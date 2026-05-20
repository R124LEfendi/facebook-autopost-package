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
}
