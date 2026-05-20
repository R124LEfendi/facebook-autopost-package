<?php

namespace Tokalink\FacebookAutopost\Console\Commands;

use Illuminate\Console\Command;
use Tokalink\FacebookAutopost\Models\FacebookPage;
use Tokalink\FacebookAutopost\Models\FacebookPost;
use Tokalink\FacebookAutopost\Services\FacebookService;

class FacebookPostCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'facebook:post-multi
                            {--message= : The text content of the post}
                            {--link= : An optional URL link to include in the post}
                            {--image= : Path to an image file (local path or remote URL) to attach}
                            {--pages= : Comma-separated list of Facebook Page IDs, or "all" to post to all active pages}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Post content to multiple Facebook pages simultaneously';

    protected FacebookService $fbService;

    public function __construct(FacebookService $fbService)
    {
        parent::__construct();
        $this->fbService = $fbService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $message = $this->option('message');
        $link = $this->option('link');
        $image = $this->option('image');
        $pagesOption = $this->option('pages');

        if (!$message && !$link && !$image) {
            $this->error("Error: You must provide at least a --message, --link, or --image option.");
            return 1;
        }

        // Get active pages from database
        $activePages = FacebookPage::where('is_active', true)->get();

        if ($activePages->isEmpty()) {
            $this->error("No active Facebook pages found in the database. Connect an account first!");
            return 1;
        }

        $selectedPages = collect();

        // Parse page options
        if ($pagesOption === 'all') {
            $selectedPages = $activePages;
        } elseif ($pagesOption) {
            $pageIds = explode(',', $pagesOption);
            $selectedPages = $activePages->filter(function ($page) use ($pageIds) {
                return in_array($page->page_id, $pageIds);
            });

            if ($selectedPages->isEmpty()) {
                $this->error("None of the specified Page IDs match the active pages in the database.");
                return 1;
            }
        } else {
            // Interactive Mode
            $this->info("No target pages specified. Entering interactive selection mode...");
            
            $choices = [];
            foreach ($activePages as $page) {
                $choices[$page->id] = "{$page->name} (ID: {$page->page_id})";
            }

            $selectedChoices = $this->choice(
                'Select which pages to publish to (multiple answers allowed, comma-separated)',
                $choices,
                null,
                null,
                true // Multi-select
            );

            // Fetch the selected pages
            $selectedNames = array_map(function ($choice) {
                return explode(' (ID:', $choice)[0];
            }, $selectedChoices);

            $selectedPages = $activePages->filter(function ($page) use ($selectedNames) {
                return in_array($page->name, $selectedNames);
            });
        }

        $this->info("Preparing to post to " . $selectedPages->count() . " page(s)...");
        $this->line(" - Message: " . ($message ?: '[None]'));
        $this->line(" - Link: " . ($link ?: '[None]'));
        $this->line(" - Image: " . ($image ?: '[None]'));
        $this->newLine();

        $bar = $this->output->createProgressBar($selectedPages->count());
        $bar->start();

        $successCount = 0;
        $failCount = 0;
        $results = [];

        foreach ($selectedPages as $page) {
            $result = $this->fbService->postToPage(
                $page->access_token,
                $page->page_id,
                $message,
                $link,
                $image
            );

            if ($result['success']) {
                $successCount++;
                $results[] = [
                    'page' => $page->name,
                    'status' => 'Success ✅',
                    'id' => $result['fb_post_id'],
                ];

                FacebookPost::create([
                    'user_id' => null,
                    'facebook_page_id' => $page->id,
                    'message' => $message,
                    'link' => $link,
                    'image_path' => $image,
                    'status' => 'success',
                    'fb_post_id' => $result['fb_post_id'],
                    'posted_at' => now(),
                ]);
            } else {
                $failCount++;
                $results[] = [
                    'page' => $page->name,
                    'status' => 'Failed ❌',
                    'id' => 'Error: ' . $result['error'],
                ];

                FacebookPost::create([
                    'user_id' => null,
                    'facebook_page_id' => $page->id,
                    'message' => $message,
                    'link' => $link,
                    'image_path' => $image,
                    'status' => 'failed',
                    'error_message' => $result['error'],
                    'posted_at' => now(),
                ]);
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        // Display results table
        $this->table(['Page Name', 'Status', 'Facebook Post ID / Error'], $results);

        $this->newLine();
        if ($failCount === 0) {
            $this->info("Completed successfully! Posted to all {$successCount} pages.");
        } else {
            $this->warn("Completed with errors. Posted successfully to {$successCount} pages, failed on {$failCount} pages.");
        }

        return $failCount === 0 ? 0 : 1;
    }
}
