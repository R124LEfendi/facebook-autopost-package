<?php

namespace R124LEfendi\FacebookAutopost\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class FacebookService
{
    protected string $apiVersion = 'v20.0';
    protected string $baseUrl = 'https://graph.facebook.com';

    /**
     * Get App ID from configuration.
     */
    protected function getAppId(): ?string
    {
        return config('services.facebook.app_id');
    }

    /**
     * Get App Secret from configuration.
     */
    protected function getAppSecret(): ?string
    {
        return config('services.facebook.app_secret');
    }

    /**
     * Exchange short-lived User Access Token for a long-lived one (valid for 60 days).
     */
    public function exchangeToLongLivedToken(string $shortLivedToken): string
    {
        $appId = $this->getAppId();
        $appSecret = $this->getAppSecret();

        if (!$appId || !$appSecret) {
            Log::warning("Facebook App ID or Secret is not configured. Returning original token.");
            return $shortLivedToken;
        }

        try {
            $response = Http::get("{$this->baseUrl}/{$this->apiVersion}/oauth/access_token", [
                'grant_type' => 'fb_exchange_token',
                'client_id' => $appId,
                'client_secret' => $appSecret,
                'fb_exchange_token' => $shortLivedToken,
            ]);

            if ($response->failed()) {
                throw new Exception("Token exchange failed: " . json_encode($response->json()));
            }

            return $response->json()['access_token'] ?? $shortLivedToken;
        } catch (Exception $e) {
            Log::error("FacebookService - exchangeToLongLivedToken error: " . $e->getMessage());
            return $shortLivedToken;
        }
    }

    /**
     * Get Facebook user profile details using a User Access Token.
     */
    public function getUserProfile(string $userAccessToken): array
    {
        try {
            $response = Http::get("{$this->baseUrl}/{$this->apiVersion}/me", [
                'fields' => 'id,name,email,picture.type(large)',
                'access_token' => $userAccessToken,
            ]);

            if ($response->failed()) {
                throw new Exception("Failed to fetch user profile: " . json_encode($response->json()));
            }

            $data = $response->json();
            return [
                'id' => $data['id'],
                'name' => $data['name'],
                'email' => $data['email'] ?? null,
                'avatar' => $data['picture']['data']['url'] ?? null,
            ];
        } catch (Exception $e) {
            Log::error("FacebookService - getUserProfile error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get all pages managed by the user, along with permanent page access tokens.
     */
    public function getUserPages(string $userAccessToken): array
    {
        try {
            $response = Http::get("{$this->baseUrl}/{$this->apiVersion}/me/accounts", [
                'fields' => 'id,name,access_token,category,picture.type(large)',
                'access_token' => $userAccessToken,
                'limit' => 200,
            ]);

            if ($response->failed()) {
                throw new Exception("Failed to fetch user pages: " . json_encode($response->json()));
            }

            $pages = [];
            $data = $response->json()['data'] ?? [];

            foreach ($data as $page) {
                $pages[] = [
                    'page_id' => $page['id'],
                    'name' => $page['name'],
                    'access_token' => $page['access_token'], // Permanent page token
                    'category' => $page['category'] ?? null,
                    'avatar' => $page['picture']['data']['url'] ?? null,
                ];
            }

            return $pages;
        } catch (Exception $e) {
            Log::error("FacebookService - getUserPages error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Post text, link, and/or image to a Facebook Page.
     */
    public function postToPage(string $pageAccessToken, string $pageId, ?string $message = null, ?string $link = null, ?string $imagePath = null): array
    {
        try {
            // Case 1: Post has an image (Local Path)
            if ($imagePath && file_exists($imagePath)) {
                $url = "{$this->baseUrl}/{$this->apiVersion}/{$pageId}/photos";
                
                $request = Http::timeout(180)->attach(
                    'source',
                    file_get_contents($imagePath),
                    basename($imagePath)
                );

                $params = [
                    'access_token' => $pageAccessToken,
                ];

                if ($message) {
                    $params['caption'] = $message;
                }

                $response = $request->post($url, $params);
            } 
            // Case 2: Post has a remote image URL
            elseif ($imagePath && (str_starts_with($imagePath, 'http://') || str_starts_with($imagePath, 'https://'))) {
                $url = "{$this->baseUrl}/{$this->apiVersion}/{$pageId}/photos";
                $params = [
                    'url' => $imagePath,
                    'access_token' => $pageAccessToken,
                ];

                if ($message) {
                    $params['caption'] = $message;
                }

                $response = Http::timeout(180)->post($url, $params);
            }
            // Case 3: Text and/or Link post
            else {
                $url = "{$this->baseUrl}/{$this->apiVersion}/{$pageId}/feed";
                $params = [
                    'access_token' => $pageAccessToken,
                ];

                if ($message) {
                    $params['message'] = $message;
                }

                if ($link) {
                    $params['link'] = $link;
                }

                $response = Http::timeout(60)->post($url, $params);
            }

            $result = $response->json();

            if ($response->failed()) {
                $errorMsg = $result['error']['message'] ?? 'Unknown API Error';
                Log::error("FacebookService - postToPage failed for Page {$pageId}: " . json_encode($result));
                return [
                    'success' => false,
                    'error' => $errorMsg,
                ];
            }

            $fbPostId = $result['post_id'] ?? $result['id'] ?? null;

            return [
                'success' => true,
                'fb_post_id' => $fbPostId,
            ];

        } catch (Exception $e) {
            Log::error("FacebookService - postToPage exception: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}
