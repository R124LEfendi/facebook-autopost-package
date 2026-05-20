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

### Step 1: Place the Package
Make sure the `facebook-autopost-package` folder is placed in the root of your Laravel application (e.g. `facebook-autopost-package/`).

### Step 2: Register Package Repository in `composer.json`
To load this local package elegantly without manual path hacks, open your main application's `composer.json` and add a local path repository configuration:

```json
"repositories": [
    {
        "type": "path",
        "url": "./facebook-autopost-package"
    }
],
```

Then, require the package using standard Composer:
```bash
composer require tokalink/facebook-autopost
```

*Note: Since Laravel supports package auto-discovery, it will automatically register the `Tokalink\FacebookAutopost\FacebookAutopostServiceProvider` service provider.*

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
php artisan facebook:post-multi --message="Check out Tokalink!" --link="https://tokalink.co" --image="public/uploads/promo.jpg" --pages="all"
```

---

## 🌐 Web Dashboard Usage
Go to:
🔗 `http://your-domain.local/facebook`

1. Paste a **Facebook User Access Token** in the dashboard form.
2. Click **Import Facebook Account** to automatically register the account and import all its pages.
3. Check the boxes of the target pages, type your message or link, select an optional photo, and hit **Publish Autopost Now**!
