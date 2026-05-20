# Meta Facebook Auto-Post Package for Laravel

A highly reusable, isolated, and premium multi-user and multi-page Facebook Meta posting package for Laravel. 

This package is designed as a standalone, self-contained library so you can drop it into any Laravel project without risking routing, class, or dependency conflicts with your existing application code.

---

## ✨ Features

- **Multi-User Structure**: Multiple users can register and connect their own distinct Facebook accounts.
- **Multi-Page Management**: Under each Facebook account, manage multiple pages with simple, instant toggles to activate/deactivate pages for autoposting.
- **Double Connection Flows**:
  1. **User Token Flow (Auto-import)**: Paste a Facebook User Access Token to automatically fetch user profiles, exchange it for long-lived tokens, and query/import all managed Pages along with their permanent Page Access Tokens.
  2. **Page Token Flow (Manual input)**: Manually connect a single page with its direct Page Access Token, ID, and Name (perfect for local sandbox testing).
- **Multi-Page Simultaneous Autoposting**: Write a single post, check multiple target pages, and publish to all selected pages at once (text, links, and local image attachments).
- **Artisan CLI Posting Command**: Includes a highly interactive terminal command `facebook:post-multi` with choice menus, progress bars, and audit logging.
- **Glassmorphic Premium Blade Dashboard**: Includes an out-of-the-box, fully responsive Blade dashboard styled with custom Vanilla CSS.
- **Post Audit Logs**: Logs all success and failure payloads including direct links to published posts on Facebook.

---

## 🚀 Installation & Integration Guide

### Step 1: Register Package Repository in `composer.json`
To install this package, open your main Laravel application's `composer.json` and add the GitHub repository to the `repositories` block:

```json
"repositories": [
    {
        "type": "vcs",
        "url": "https://github.com/R124LEfendi/facebook-autopost-package.git"
    }
],
```

---

### Step 2: Install Package via Composer
Since the package is hosted directly on GitHub and may not have a tagged release yet, Composer will treat it as a development version (`dev-main`). Because Laravel's default `composer.json` restricts packages to `stable` stability, you must explicitly require the `dev-main` branch:

```bash
composer require r124lefendi/facebook-autopost:dev-main
```

> [!TIP]
> **Production Tagging:** Once you are ready to publish stable versions, you can create a release tag on GitHub (e.g. `v1.0.0`). After doing so, anyone can install the package normally using:
> `composer require r124lefendi/facebook-autopost`

*Note: Since Laravel supports package auto-discovery, it will automatically register the `R124LEfendi\FacebookAutopost\FacebookAutopostServiceProvider` service provider.*

### Step 3: Run Database Migrations
Run your application migrations to create the required `facebook_accounts`, `facebook_pages`, and `facebook_posts` tables:

```bash
php artisan migrate
```

---

## 🛠️ Configuration

Add your Facebook App ID and App Secret credentials inside `config/services.php`:

```php
'facebook' => [
    'app_id' => env('META_APP_ID'),
    'app_secret' => env('META_SECRET_KEY'),
],
```

And define them in your `.env` file:
```env
META_APP_ID="your-app-id"
META_SECRET_KEY="your-app-secret"
```

### 🔑 Setting up Facebook Login (OAuth 2.0)
To enable the seamless **Connect with Facebook** button (OAuth 2.0 flow) in the dashboard:
1. Go to your **[Meta Developer Portal](https://developers.facebook.com/)** and select your App.
2. In the left menu, click **Add Product** and select **Facebook Login for Business** (or standard Facebook Login).
3. Under the **Facebook Login Settings**, add your application's Callback URI to the **Valid OAuth Redirect URIs** input:
   ```text
   https://your-domain.com/facebook/callback
   ```
   *(Note: Facebook requires secure `https://` URLs for redirection in production, but supports http for `localhost`/`127.0.0.1` environments)*

---

## 💻 CLI Command Usage

The package registers an Artisan console command `facebook:post-multi` for CLI-based autoposting:

### **Interactive Selection Mode (Recommended)**
```bash
php artisan facebook:post-multi --message="Hello from the terminal!"
```
*It will automatically display an interactive selection checklist for all your active pages!*

### **Broadcast to All Active Pages**
```bash
php artisan facebook:post-multi --message="Broadcast content" --pages="all"
```

### **Post with Attachments (Link & Image File)**
```bash
php artisan facebook:post-multi --message="Check out R124LEfendi!" --link="https://github.com/R124LEfendi" --pages="all"
```

---

## 🌐 Web Dashboard Usage
Go to:
🔗 `http://your-domain.local/facebook`

1. Paste a **Facebook User Access Token** in the dashboard form.
2. Click **Import Facebook Account** to automatically register the account and import all its pages.
3. Check the boxes of the target pages, type your message or link, select an optional photo, and hit **Publish Autopost Now**!

---

## 🎨 Customizing & Publishing the UI

If you want to customize the look and feel, change the colors, modify layout elements, or translate texts, you can publish the package's Blade views directly into your Laravel application's resources directory:

```bash
php artisan vendor:publish --tag=facebook-autopost-views
```

This will copy the dashboard template to:
`resources/views/vendor/facebook-autopost/dashboard.blade.php`

Once published, Laravel will automatically prioritize and load your custom file at `resources/views/vendor/facebook-autopost/dashboard.blade.php` instead of the default package layout, giving you 100% freedom to modify or style the view to match your application's design system!

