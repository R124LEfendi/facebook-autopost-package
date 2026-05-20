<?php

namespace R124LEfendi\FacebookAutopost\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Exception;
use R124LEfendi\FacebookAutopost\Models\FacebookAccount;
use R124LEfendi\FacebookAutopost\Models\FacebookPage;
use R124LEfendi\FacebookAutopost\Models\FacebookPost;
use R124LEfendi\FacebookAutopost\Services\FacebookService;

class FacebookController extends Controller
{
    protected FacebookService $fbService;

    public function __construct(FacebookService $fbService)
    {
        $this->fbService = $fbService;
    }

    /**
     * Display the Facebook dashboard.
     */
    public function index()
    {
        $accounts = FacebookAccount::with('pages')->get();
        $activePages = FacebookPage::where('is_active', true)->get();
        $recentPosts = FacebookPost::with('page.account')->latest()->take(30)->get();

        return view('facebook-autopost::dashboard', compact('accounts', 'activePages', 'recentPosts'));
    }

    /**
     * Connect a Facebook account using a User Access Token.
     */
    public function connectUserToken(Request $request)
    {
        $request->validate([
            'user_access_token' => 'required|string',
        ]);

        $token = $request->input('user_access_token');

        try {
            // Exchange for a long-lived user access token
            $longLivedToken = $this->fbService->exchangeToLongLivedToken($token);

            // Fetch user profile details
            $profile = $this->fbService->getUserProfile($longLivedToken);

            // Save Facebook Account
            $account = FacebookAccount::updateOrCreate(
                ['fb_user_id' => $profile['id']],
                [
                    'user_id' => auth()->id() ?: null,
                    'name' => $profile['name'],
                    'email' => $profile['email'],
                    'access_token' => $longLivedToken,
                    'avatar' => $profile['avatar'],
                ]
            );

            // Fetch and save User Pages
            $pages = $this->fbService->getUserPages($longLivedToken);
            $importedCount = 0;

            foreach ($pages as $p) {
                FacebookPage::updateOrCreate(
                    ['page_id' => $p['page_id']],
                    [
                        'facebook_account_id' => $account->id,
                        'name' => $p['name'],
                        'access_token' => $p['access_token'],
                        'category' => $p['category'],
                        'avatar' => $p['avatar'],
                        'is_active' => true,
                    ]
                );
                $importedCount++;
            }

            return redirect()->route('facebook.dashboard')->with('success', "Connected account '{$account->name}' and imported {$importedCount} pages successfully!");

        } catch (Exception $e) {
            return redirect()->route('facebook.dashboard')->with('error', "Connection failed: " . $e->getMessage());
        }
    }

    /**
     * Connect a single Facebook Page manually using Page Token and Page ID.
     */
    public function connectSinglePage(Request $request)
    {
        $request->validate([
            'page_name' => 'required|string|max:255',
            'page_id' => 'required|string|max:255',
            'page_access_token' => 'required|string',
        ]);

        try {
            // Create a virtual "Manual Connections" account to group manually entered pages
            $account = FacebookAccount::updateOrCreate(
                ['fb_user_id' => 'manual_connections'],
                [
                    'user_id' => auth()->id() ?: null,
                    'name' => 'Manual Connections',
                    'access_token' => 'manual',
                    'avatar' => 'https://www.facebook.com/images/assets_files/yis/gray_app_icon.png',
                ]
            );

            // Create/Update the specific page
            $page = FacebookPage::updateOrCreate(
                ['page_id' => $request->input('page_id')],
                [
                    'facebook_account_id' => $account->id,
                    'name' => $request->input('page_name'),
                    'access_token' => $request->input('page_access_token'),
                    'category' => 'Manual Input',
                    'avatar' => null,
                    'is_active' => true,
                ]
            );

            return redirect()->route('facebook.dashboard')->with('success', "Page '{$page->name}' connected successfully via manual token!");

        } catch (Exception $e) {
            return redirect()->route('facebook.dashboard')->with('error', "Manual connection failed: " . $e->getMessage());
        }
    }

    /**
     * Toggle active/inactive status of a page.
     */
    public function togglePage(FacebookPage $page)
    {
        $page->is_active = !$page->is_active;
        $page->save();

        $status = $page->is_active ? 'activated' : 'deactivated';
        return redirect()->route('facebook.dashboard')->with('success', "Page '{$page->name}' has been {$status}!");
    }

    /**
     * Disconnect/delete a Facebook Account and all its pages.
     */
    public function deleteAccount(FacebookAccount $account)
    {
        $name = $account->name;
        $account->delete();

        return redirect()->route('facebook.dashboard')->with('success', "Disconnected Facebook Account '{$name}' successfully.");
    }

    /**
     * Handle multi-page posting.
     */
    public function post(Request $request)
    {
        $request->validate([
            'message' => 'nullable|string',
            'link' => 'nullable|url',
            'image' => 'nullable|image|max:10240', // Max 10MB
            'pages' => 'required|array|min:1',
            'pages.*' => 'exists:facebook_pages,id',
        ]);

        $message = $request->input('message');
        $link = $request->input('link');
        $pages = $request->input('pages');

        // Handle image upload if present
        $imagePath = null;
        $dbImagePath = null;

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $fileName = time() . '_' . preg_replace('/[^A-Za-z0-9._-]/', '', $file->getClientOriginalName());
            
            $uploadDir = public_path('uploads/facebook');
            if (!File::exists($uploadDir)) {
                File::makeDirectory($uploadDir, 0755, true);
            }

            $file->move($uploadDir, $fileName);
            $imagePath = $uploadDir . '/' . $fileName;
            $dbImagePath = 'uploads/facebook/' . $fileName; // Relative path to store in DB
        }

        $successCount = 0;
        $failCount = 0;
        $errors = [];

        foreach ($pages as $pageDbId) {
            $page = FacebookPage::find($pageDbId);
            if (!$page || !$page->is_active) {
                continue;
            }

            // Post to page using the service
            $result = $this->fbService->postToPage(
                $page->access_token,
                $page->page_id,
                $message,
                $link,
                $imagePath
            );

            if ($result['success']) {
                $successCount++;
                FacebookPost::create([
                    'user_id' => auth()->id() ?: null,
                    'facebook_page_id' => $page->id,
                    'message' => $message,
                    'link' => $link,
                    'image_path' => $dbImagePath,
                    'status' => 'success',
                    'fb_post_id' => $result['fb_post_id'],
                    'posted_at' => now(),
                ]);
            } else {
                $failCount++;
                $errors[] = "{$page->name}: {$result['error']}";
                FacebookPost::create([
                    'user_id' => auth()->id() ?: null,
                    'facebook_page_id' => $page->id,
                    'message' => $message,
                    'link' => $link,
                    'image_path' => $dbImagePath,
                    'status' => 'failed',
                    'error_message' => $result['error'],
                    'posted_at' => now(),
                ]);
            }
        }

        if ($failCount > 0) {
            $errMsg = "Posted to {$successCount} pages, but failed on {$failCount} pages. Errors: " . implode(' | ', $errors);
            return redirect()->route('facebook.dashboard')->with('warning', $errMsg);
        }

        return redirect()->route('facebook.dashboard')->with('success', "Successfully posted to all {$successCount} selected pages!");
    }

    /**
     * Renew/update the access token of a Facebook Account and its pages.
     */
    public function renewToken(Request $request, FacebookAccount $account)
    {
        $request->validate([
            'new_access_token' => 'required|string',
        ]);

        $token = $request->input('new_access_token');

        try {
            // Exchange for a long-lived user access token
            $longLivedToken = $this->fbService->exchangeToLongLivedToken($token);

            // Fetch user profile details to ensure the token belongs to the SAME Facebook user ID!
            $profile = $this->fbService->getUserProfile($longLivedToken);

            if ($profile['id'] !== $account->fb_user_id) {
                return redirect()->route('facebook.dashboard')->with('error', "Token mismatch: The new token belongs to '{$profile['name']}' (ID: {$profile['id']}), but this account is for '{$account->name}' (ID: {$account->fb_user_id}).");
            }

            // Update Facebook Account token and details
            $account->update([
                'access_token' => $longLivedToken,
                'name' => $profile['name'],
                'email' => $profile['email'] ?? $account->email,
                'avatar' => $profile['avatar'] ?? $account->avatar,
            ]);

            // Fetch and update pages for this account
            $pages = $this->fbService->getUserPages($longLivedToken);
            $updatedCount = 0;

            foreach ($pages as $p) {
                $existingPage = FacebookPage::where('facebook_account_id', $account->id)
                    ->where('page_id', $p['page_id'])
                    ->first();

                if ($existingPage) {
                    $existingPage->update([
                        'access_token' => $p['access_token'],
                        'name' => $p['name'],
                        'category' => $p['category'],
                        'avatar' => $p['avatar'] ?? $existingPage->avatar,
                    ]);
                    $updatedCount++;
                }
            }

            return redirect()->route('facebook.dashboard')->with('success', "Access token for '{$account->name}' and {$updatedCount} pages successfully updated!");

        } catch (Exception $e) {
            return redirect()->route('facebook.dashboard')->with('error', "Failed to renew token: " . $e->getMessage());
        }
    }

    /**
     * Redirect the user to the Facebook authentication page.
     */
    public function redirectToFacebook()
    {
        $appId = config('services.facebook.app_id');
        $redirectUri = route('facebook.callback');
        
        if (!$appId) {
            return redirect()->route('facebook.dashboard')->with('error', 'Facebook App ID is not configured. Please check your services.php configuration.');
        }

        // Build OAuth URL
        $permissions = ['public_profile', 'email', 'pages_read_engagement', 'pages_manage_posts'];
        $query = http_build_query([
            'client_id' => $appId,
            'redirect_uri' => $redirectUri,
            'scope' => implode(',', $permissions),
            'response_type' => 'code',
            'state' => csrf_token(), // CSRF Protection
        ]);

        return redirect("https://www.facebook.com/v20.0/dialog/oauth?{$query}");
    }

    /**
     * Handle the callback from Facebook authentication.
     */
    public function handleCallback(Request $request)
    {
        if ($request->has('error')) {
            return redirect()->route('facebook.dashboard')->with('error', 'Facebook Login cancelled or failed: ' . $request->input('error_description', 'Unknown error'));
        }

        $code = $request->input('code');
        
        if (!$code) {
            return redirect()->route('facebook.dashboard')->with('error', 'No authorization code returned from Facebook.');
        }

        try {
            $appId = config('services.facebook.app_id');
            $appSecret = config('services.facebook.app_secret');
            $redirectUri = route('facebook.callback');

            // 1. Exchange authorization code for a short-lived User Access Token
            $response = \Illuminate\Support\Facades\Http::get("https://graph.facebook.com/v20.0/oauth/access_token", [
                'client_id' => $appId,
                'client_secret' => $appSecret,
                'redirect_uri' => $redirectUri,
                'code' => $code,
            ]);

            if ($response->failed()) {
                throw new Exception("Failed to exchange code: " . json_encode($response->json()));
            }

            $shortLivedToken = $response->json()['access_token'] ?? null;

            if (!$shortLivedToken) {
                throw new Exception("No access token returned in code exchange.");
            }

            // 2. Exchange short-lived token for a long-lived User Access Token (60 days)
            $longLivedToken = $this->fbService->exchangeToLongLivedToken($shortLivedToken);

            // 3. Fetch user profile details
            $profile = $this->fbService->getUserProfile($longLivedToken);

            // 4. Save/Update Facebook Account
            $account = FacebookAccount::updateOrCreate(
                ['fb_user_id' => $profile['id']],
                [
                    'user_id' => auth()->id() ?: null,
                    'name' => $profile['name'],
                    'email' => $profile['email'] ?? null,
                    'access_token' => $longLivedToken,
                    'avatar' => $profile['avatar'] ?? null,
                ]
            );

            // 5. Fetch and save User Pages
            $pages = $this->fbService->getUserPages($longLivedToken);
            $importedCount = 0;

            foreach ($pages as $p) {
                FacebookPage::updateOrCreate(
                    ['page_id' => $p['page_id']],
                    [
                        'facebook_account_id' => $account->id,
                        'name' => $p['name'],
                        'access_token' => $p['access_token'],
                        'category' => $p['category'],
                        'avatar' => $p['avatar'],
                        'is_active' => true,
                    ]
                );
                $importedCount++;
            }

            return redirect()->route('facebook.dashboard')->with('success', "Connected account '{$account->name}' via Facebook Login and imported {$importedCount} pages successfully!");

        } catch (Exception $e) {
            \Illuminate\Support\Facades\Log::error("Facebook OAuth Callback Error: " . $e->getMessage());
            return redirect()->route('facebook.dashboard')->with('error', "Facebook authentication failed: " . $e->getMessage());
        }
    }
}
