<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Meta Facebook AutoPost Dashboard</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        /* Modern Premium CSS System */
        :root {
            --bg-primary: #0f172a;
            --bg-secondary: #1e293b;
            --bg-tertiary: #334155;
            --accent-color: #3b82f6;
            --accent-hover: #2563eb;
            --accent-success: #10b981;
            --accent-warning: #f59e0b;
            --accent-danger: #ef4444;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --glass-bg: rgba(30, 41, 59, 0.7);
            --glass-border: rgba(255, 255, 255, 0.08);
            --card-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.5);
            --radius-lg: 16px;
            --radius-md: 10px;
            --radius-sm: 6px;
            --font-outfit: 'Outfit', sans-serif;
            --font-inter: 'Inter', sans-serif;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            transition: all 0.25s ease;
        }

        body {
            background-color: var(--bg-primary);
            color: var(--text-main);
            font-family: var(--font-inter);
            min-height: 100vh;
            line-height: 1.5;
            background-image: 
                radial-gradient(at 0% 0%, rgba(59, 130, 246, 0.15) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(99, 102, 241, 0.15) 0px, transparent 50%);
            padding: 2rem 1rem;
        }

        .container {
            max-width: 1280px;
            margin: 0 auto;
        }

        /* Header Styling */
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2.5rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid var(--glass-border);
        }

        .header-logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .header-logo i {
            font-size: 2.5rem;
            color: var(--accent-color);
            filter: drop-shadow(0 0 10px rgba(59, 130, 246, 0.5));
        }

        .header-logo h1 {
            font-family: var(--font-outfit);
            font-size: 1.85rem;
            font-weight: 800;
            letter-spacing: -0.025em;
            background: linear-gradient(135deg, #3b82f6 0%, #818cf8 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .header-badge {
            background: rgba(59, 130, 246, 0.1);
            border: 1px solid rgba(59, 130, 246, 0.2);
            color: var(--accent-color);
            padding: 0.4rem 1rem;
            border-radius: 9999px;
            font-size: 0.85rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        /* Alerts */
        .alert {
            padding: 1rem 1.25rem;
            border-radius: var(--radius-md);
            margin-bottom: 2rem;
            font-size: 0.95rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            box-shadow: var(--card-shadow);
        }

        .alert-success {
            background-color: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.2);
            color: #34d399;
        }

        .alert-error {
            background-color: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.2);
            color: #f87171;
        }

        .alert-warning {
            background-color: rgba(245, 158, 11, 0.1);
            border: 1px solid rgba(245, 158, 11, 0.2);
            color: #fbbf24;
        }

        /* Main Dashboard Grid */
        .dashboard-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 2rem;
            margin-bottom: 2rem;
        }

        @media (min-width: 1024px) {
            .dashboard-grid {
                grid-template-columns: 450px 1fr;
            }
        }

        /* Card System */
        .card {
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            backdrop-filter: blur(16px);
            border-radius: var(--radius-lg);
            padding: 1.75rem;
            box-shadow: var(--card-shadow);
            position: relative;
            overflow: hidden;
        }

        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: transparent;
        }

        .card-accent-blue::before {
            background: linear-gradient(90deg, var(--accent-color), #818cf8);
        }

        .card-accent-emerald::before {
            background: linear-gradient(90deg, var(--accent-success), #34d399);
        }

        .card-title {
            font-family: var(--font-outfit);
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--text-main);
        }

        .card-title i {
            color: var(--accent-color);
        }

        .section-group {
            display: flex;
            flex-direction: column;
            gap: 2rem;
        }

        /* Forms */
        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-group label {
            display: block;
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--text-muted);
            margin-bottom: 0.5rem;
        }

        .input-control {
            width: 100%;
            background-color: rgba(15, 23, 42, 0.6);
            border: 1px solid var(--glass-border);
            border-radius: var(--radius-md);
            padding: 0.75rem 1rem;
            color: var(--text-main);
            font-family: var(--font-inter);
            font-size: 0.95rem;
        }

        .input-control:focus {
            outline: none;
            border-color: var(--accent-color);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.25);
        }

        textarea.input-control {
            resize: vertical;
            min-height: 120px;
        }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.75rem 1.25rem;
            font-weight: 600;
            font-size: 0.95rem;
            border-radius: var(--radius-md);
            border: none;
            cursor: pointer;
            text-decoration: none;
            width: 100%;
            text-align: center;
        }

        .btn-primary {
            background-color: var(--accent-color);
            color: white;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }

        .btn-primary:hover {
            background-color: var(--accent-hover);
            transform: translateY(-1px);
        }

        .btn-danger-outline {
            background: transparent;
            border: 1px solid rgba(239, 68, 68, 0.4);
            color: var(--accent-danger);
        }

        .btn-danger-outline:hover {
            background-color: rgba(239, 68, 68, 0.1);
        }

        /* Switches */
        .token-mode-switch {
            display: flex;
            background: rgba(15, 23, 42, 0.4);
            border-radius: var(--radius-md);
            padding: 0.25rem;
            margin-bottom: 1.5rem;
            border: 1px solid var(--glass-border);
        }

        .tab-btn {
            flex: 1;
            background: transparent;
            border: none;
            color: var(--text-muted);
            padding: 0.5rem;
            font-size: 0.85rem;
            font-weight: 600;
            border-radius: var(--radius-sm);
            cursor: pointer;
        }

        .tab-btn.active {
            background: var(--bg-tertiary);
            color: var(--text-main);
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
        }

        /* Accounts List */
        .accounts-container {
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }

        .account-item {
            background: rgba(15, 23, 42, 0.4);
            border: 1px solid var(--glass-border);
            border-radius: var(--radius-md);
            padding: 1rem;
        }

        .account-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.75rem;
        }

        .account-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .account-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--accent-color);
        }

        .account-details h4 {
            font-weight: 600;
            font-size: 0.95rem;
        }

        .account-details span {
            font-size: 0.75rem;
            color: var(--text-muted);
        }

        .pages-list {
            margin-top: 0.75rem;
            border-top: 1px dashed var(--glass-border);
            padding-top: 0.75rem;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .page-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: rgba(30, 41, 59, 0.3);
            padding: 0.5rem 0.75rem;
            border-radius: var(--radius-sm);
            font-size: 0.85rem;
        }

        .page-name {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 500;
        }

        .page-avatar {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            object-fit: cover;
            background-color: var(--bg-tertiary);
        }

        .page-status-badge {
            font-size: 0.75rem;
            padding: 0.15rem 0.5rem;
            border-radius: 4px;
            font-weight: 600;
        }

        .page-status-active {
            background-color: rgba(16, 185, 129, 0.15);
            color: #34d399;
        }

        .page-status-inactive {
            background-color: rgba(239, 68, 68, 0.15);
            color: #f87171;
        }

        .switch-toggle {
            cursor: pointer;
            background: none;
            border: none;
            color: var(--text-muted);
            font-size: 1.1rem;
        }

        .switch-toggle:hover {
            color: var(--accent-color);
        }

        .page-selector-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 0.75rem;
            margin-bottom: 1.5rem;
            max-height: 180px;
            overflow-y: auto;
            border: 1px solid var(--glass-border);
            padding: 0.75rem;
            border-radius: var(--radius-md);
            background: rgba(15, 23, 42, 0.4);
        }

        .page-selector-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background: var(--bg-secondary);
            border: 1px solid var(--glass-border);
            padding: 0.6rem 0.75rem;
            border-radius: var(--radius-md);
            cursor: pointer;
            font-size: 0.85rem;
        }

        .page-selector-item:hover {
            border-color: var(--accent-color);
        }

        .page-selector-item input {
            cursor: pointer;
            accent-color: var(--accent-color);
            width: 16px;
            height: 16px;
        }

        /* Table */
        .recent-posts-card {
            margin-top: 2rem;
        }

        .table-responsive {
            width: 100%;
            overflow-x: auto;
            border-radius: var(--radius-md);
            border: 1px solid var(--glass-border);
            background: rgba(15, 23, 42, 0.4);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            font-size: 0.9rem;
        }

        th {
            background-color: rgba(30, 41, 59, 0.8);
            color: var(--text-muted);
            font-weight: 600;
            padding: 1rem;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-bottom: 1px solid var(--glass-border);
        }

        td {
            padding: 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.04);
            vertical-align: middle;
        }

        tr:last-child td {
            border-bottom: none;
        }

        tr:hover td {
            background-color: rgba(255, 255, 255, 0.02);
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.25rem 0.6rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .badge-success {
            background-color: rgba(16, 185, 129, 0.15);
            color: #34d399;
            border: 1px solid rgba(16, 185, 129, 0.2);
        }

        .badge-danger {
            background-color: rgba(239, 68, 68, 0.15);
            color: #f87171;
            border: 1px solid rgba(239, 68, 68, 0.2);
        }

        .post-preview {
            max-width: 250px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            display: block;
        }

        .post-link {
            color: var(--accent-color);
            text-decoration: none;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
        }

        .post-link:hover {
            text-decoration: underline;
            color: #60a5fa;
        }

        .attachment-badge {
            background-color: rgba(255, 255, 255, 0.06);
            color: var(--text-muted);
            font-size: 0.75rem;
            padding: 0.15rem 0.4rem;
            border-radius: 4px;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
        }

        .empty-state {
            padding: 3rem 1.5rem;
            text-align: center;
            color: var(--text-muted);
        }

        .empty-state i {
            font-size: 2.5rem;
            color: var(--text-muted);
            opacity: 0.4;
            margin-bottom: 1rem;
        }

        .img-thumbnail {
            width: 36px;
            height: 36px;
            border-radius: 4px;
            object-fit: cover;
            background-color: #000;
            border: 1px solid var(--glass-border);
        }
    </style>
</head>
<body>

<div class="container">
    <!-- Header -->
    <header>
        <div class="header-logo">
            <i class="fab fa-facebook-square"></i>
            <div>
                <h1>Meta AutoPost</h1>
                <p style="font-size: 0.75rem; color: var(--text-muted); font-weight: 500;">Multi-User & Multi-Page Manager</p>
            </div>
        </div>
        <div class="header-badge">
            <i class="fas fa-plug"></i>
            <span>Graph API v20.0 Connected</span>
        </div>
    </header>

    <!-- Notifications -->
    @if(session('success'))
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    @if(session('warning'))
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i>
            <span>{{ session('warning') }}</span>
        </div>
    @endif

    <!-- Main Grid -->
    <div class="dashboard-grid">
        
        <!-- Left Column -->
        <div class="section-group">
            
            <!-- Connection Card -->
            <div class="card card-accent-blue">
                <h3 class="card-title"><i class="fas fa-link"></i> Connect Facebook</h3>
                
                <div class="token-mode-switch">
                    <button class="tab-btn active" onclick="switchTokenMode('oauth-mode', this)">Facebook Login (Easy)</button>
                    <button class="tab-btn" onclick="switchTokenMode('user-mode', this)">User Token Explorer</button>
                    <button class="tab-btn" onclick="switchTokenMode('single-mode', this)">Single Page Token</button>
                </div>

                <!-- Facebook OAuth Mode -->
                <div id="form-oauth-mode" style="text-align: center; padding: 1.25rem 0;">
                    <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 1.5rem; line-height: 1.5;">
                        Fast and secure connection using standard Meta authentication. Instantly import your profile and all managed Facebook Pages in a single click.
                    </p>
                    <a href="{{ route('facebook.redirect') }}" class="btn btn-primary" style="background-color: #1877f2; box-shadow: 0 4px 14px rgba(24, 119, 242, 0.4); font-weight: 700; font-size: 1rem; padding: 0.85rem 1.5rem; width: auto; display: inline-flex; border-radius: var(--radius-md); border: none; color: white; cursor: pointer; text-decoration: none;">
                        <i class="fab fa-facebook" style="font-size: 1.25rem; margin-right: 0.5rem;"></i> Connect with Facebook
                    </a>
                </div>

                <!-- User Access Token Form -->
                <form id="form-user-mode" action="{{ route('facebook.connect.user-token') }}" method="POST" style="display: none;">
                    @csrf
                    <div class="form-group">
                        <label for="user_access_token">Facebook User Access Token</label>
                        <input type="text" id="user_access_token" name="user_access_token" class="input-control" placeholder="EAAwv1PIERk4..." required>
                        <p style="font-size: 0.7rem; color: var(--text-muted); margin-top: 0.5rem;">
                            Exchange this token to automatically fetch all your managed Pages and their permanent tokens. Get one from the Graph API Explorer.
                        </p>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-sync-alt"></i> Import Facebook Account
                    </button>
                </form>

                <!-- Single Page Connection Form -->
                <form id="form-single-mode" action="{{ route('facebook.connect.single-page') }}" method="POST" style="display: none;">
                    @csrf
                    <div class="form-group">
                        <label for="page_name">Page Name</label>
                        <input type="text" id="page_name" name="page_name" class="input-control" placeholder="e.g. My Business Page">
                    </div>
                    <div class="form-group">
                        <label for="page_id">Page ID</label>
                        <input type="text" id="page_id" name="page_id" class="input-control" placeholder="e.g. 10459298379201">
                    </div>
                    <div class="form-group">
                        <label for="page_access_token">Page Access Token</label>
                        <input type="text" id="page_access_token" name="page_access_token" class="input-control" placeholder="EAAwv...">
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Connect Single Page
                    </button>
                </form>
            </div>

            <!-- Connected Accounts Card -->
            <div class="card">
                <h3 class="card-title"><i class="fas fa-users-cog"></i> Connected Accounts</h3>
                
                @if($accounts->isEmpty())
                    <div class="empty-state">
                        <i class="fas fa-user-slash"></i>
                        <p>No Facebook Accounts linked. Paste a token above to get started.</p>
                    </div>
                @else
                    <div class="accounts-container">
                        @foreach($accounts as $account)
                            <div class="account-item">
                                <div class="account-header">
                                    <div class="account-info">
                                        @if($account->avatar)
                                            <img src="{{ $account->avatar }}" alt="{{ $account->name }}" class="account-avatar">
                                        @else
                                            <div class="account-avatar" style="background-color: var(--accent-color); display: flex; align-items: center; justify-content: center; color: white; font-weight: bold;">
                                                {{ strtoupper(substr($account->name, 0, 1)) }}
                                            </div>
                                        @endif
                                        <div class="account-details">
                                            <h4>{{ $account->name }}</h4>
                                            <span>UID: {{ $account->fb_user_id }}</span>
                                        </div>
                                    </div>
                                    
                                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                                        <button type="button" class="switch-toggle" style="color: var(--accent-warning); opacity: 0.9;" title="Renew / Update Access Token" onclick="toggleRenewForm('{{ $account->id }}')">
                                            <i class="fas fa-key"></i>
                                        </button>
                                        
                                        <form action="{{ route('facebook.account.delete', $account->id) }}" method="POST" onsubmit="return confirm('Disconnect this account and all associated pages?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="switch-toggle" style="color: var(--accent-danger); opacity: 0.8;" title="Disconnect Account">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>

                                <!-- Renew Token Form (Hidden by default) -->
                                <div id="renew-form-{{ $account->id }}" class="renew-token-form" style="display: none; margin-top: 1rem; padding: 0.75rem; background: rgba(245, 158, 11, 0.05); border: 1px dashed rgba(245, 158, 11, 0.3); border-radius: var(--radius-md); margin-bottom: 0.5rem;">
                                    <form action="{{ route('facebook.account.renew', $account->id) }}" method="POST">
                                        @csrf
                                        <div class="form-group" style="margin-bottom: 0.75rem;">
                                            <label style="color: var(--accent-warning); font-size: 0.8rem; font-weight: 600;">Enter New User Access Token:</label>
                                            <input type="text" name="new_access_token" class="input-control" placeholder="EAAwv1..." required style="padding: 0.5rem 0.75rem; font-size: 0.85rem;">
                                        </div>
                                        <div style="display: flex; gap: 0.5rem;">
                                            <button type="submit" class="btn btn-primary" style="padding: 0.4rem 0.75rem; font-size: 0.8rem; width: auto; background-color: var(--accent-warning); box-shadow: none; border: none; cursor: pointer; color: white; font-weight: 600; border-radius: var(--radius-md);">
                                                <i class="fas fa-sync-alt"></i> Update Token
                                            </button>
                                            <button type="button" class="btn btn-danger-outline" style="padding: 0.4rem 0.75rem; font-size: 0.8rem; width: auto; border: 1px solid rgba(255,255,255,0.2); color: var(--text-muted); background: transparent; cursor: pointer; font-weight: 600; border-radius: var(--radius-md);" onclick="toggleRenewForm('{{ $account->id }}')">
                                                Cancel
                                            </button>
                                        </div>
                                    </form>
                                </div>

                                <!-- Pages List -->
                                <div class="pages-list">
                                    <p style="font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Imported Pages ({{ $account->pages->count() }})</p>
                                    @if($account->pages->isEmpty())
                                        <p style="font-size: 0.8rem; color: var(--text-muted);">No pages found for this account.</p>
                                    @else
                                        @foreach($account->pages as $page)
                                            <div class="page-item">
                                                <div class="page-name">
                                                    @if($page->avatar)
                                                        <img src="{{ $page->avatar }}" alt="" class="page-avatar">
                                                    @else
                                                        <div class="page-avatar" style="background-color: var(--bg-tertiary); display: flex; align-items: center; justify-content: center; color: white; font-size: 0.6rem;">FB</div>
                                                    @endif
                                                    <span>{{ $page->name }}</span>
                                                </div>
                                                <div style="display: flex; align-items: center; gap: 0.75rem;">
                                                    <span class="page-status-badge {{ $page->is_active ? 'page-status-active' : 'page-status-inactive' }}">
                                                        {{ $page->is_active ? 'Active' : 'Inactive' }}
                                                    </span>
                                                    
                                                    <form action="{{ route('facebook.page.toggle', $page->id) }}" method="POST">
                                                        @csrf
                                                        <button type="submit" class="switch-toggle" title="{{ $page->is_active ? 'Deactivate Page' : 'Activate Page' }}">
                                                            <i class="fas {{ $page->is_active ? 'fa-toggle-on' : 'fa-toggle-off' }}" style="color: {{ $page->is_active ? 'var(--accent-success)' : 'var(--text-muted)' }}; font-size: 1.25rem;"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <!-- Right Column: Autopost Form -->
        <div class="card card-accent-emerald" style="height: fit-content;">
            <h3 class="card-title"><i class="fas fa-paper-plane"></i> Publish Multi-Page Post</h3>
            
            @if($activePages->isEmpty())
                <div class="empty-state">
                    <i class="fas fa-exclamation-triangle"></i>
                    <p style="margin-top: 1rem;">You must connect an account and activate at least one Page before writing a post.</p>
                </div>
            @else
                <form action="{{ route('facebook.post') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <!-- Page Selector -->
                    <div class="form-group">
                        <label>Select Target Pages (Multi-Selection Allowed)</label>
                        <div class="page-selector-grid">
                            @foreach($activePages as $page)
                                <label class="page-selector-item">
                                    <input type="checkbox" name="pages[]" value="{{ $page->id }}" checked>
                                    @if($page->avatar)
                                        <img src="{{ $page->avatar }}" alt="" style="width: 18px; height: 18px; border-radius: 50%;">
                                    @endif
                                    <span>{{ $page->name }}</span>
                                    <span style="font-size: 0.65rem; color: var(--text-muted);">({{ $page->account->name }})</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- Post Text Content -->
                    <div class="form-group">
                        <label for="message">Post Message (Text)</label>
                        <textarea id="message" name="message" class="input-control" placeholder="Write something amazing to share..."></textarea>
                    </div>

                    <!-- Link -->
                    <div class="form-group">
                        <label for="link"><i class="fas fa-link" style="color: var(--text-muted);"></i> Attach Link (URL)</label>
                        <input type="url" id="link" name="link" class="input-control" placeholder="https://example.com/some-cool-article">
                    </div>

                    <!-- Image -->
                    <div class="form-group">
                        <label for="image"><i class="fas fa-image" style="color: var(--text-muted);"></i> Upload Photo (Image)</label>
                        <input type="file" id="image" name="image" class="input-control" accept="image/*" style="padding: 0.5rem 1rem;">
                        <p style="font-size: 0.7rem; color: var(--text-muted); margin-top: 0.4rem;">
                            Supports PNG, JPG, GIF up to 10MB.
                        </p>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-primary" style="margin-top: 1rem; padding: 0.9rem;">
                        <i class="fas fa-share-square"></i> Publish Autopost Now
                    </button>
                </form>
            @endif
        </div>
    </div>

    <!-- History Logs Section -->
    <div class="card recent-posts-card">
        <h3 class="card-title"><i class="fas fa-history"></i> Recent Post History & API Logs</h3>
        
        @if($recentPosts->isEmpty())
            <div class="empty-state">
                <i class="fas fa-folder-open"></i>
                <p>No recent posts recorded. Publish a post to populate this audit table.</p>
            </div>
        @else
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Date / Time</th>
                            <th>Target Page</th>
                            <th>Message</th>
                            <th>Attachments</th>
                            <th>Status</th>
                            <th>Actions / Links</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentPosts as $post)
                            <tr>
                                <td style="white-space: nowrap; font-size: 0.8rem; font-weight: 500;">
                                    {{ $post->posted_at ? $post->posted_at->format('Y-m-d H:i:s') : $post->created_at->format('Y-m-d H:i:s') }}
                                </td>
                                <td>
                                    <div class="page-name">
                                        @if($post->page->avatar)
                                            <img src="{{ $post->page->avatar }}" alt="" class="page-avatar">
                                        @else
                                            <div class="page-avatar" style="background-color: var(--bg-tertiary); display: flex; align-items: center; justify-content: center; color: white; font-size: 0.6rem;">FB</div>
                                        @endif
                                        <div>
                                            <div style="font-weight: 600;">{{ $post->page->name }}</div>
                                            <div style="font-size: 0.7rem; color: var(--text-muted);">Acc: {{ $post->page->account->name }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="post-preview" title="{{ $post->message }}">
                                        {{ $post->message ?: '[No Text Content]' }}
                                    </span>
                                </td>
                                <td>
                                    <div style="display: flex; gap: 0.4rem; flex-wrap: wrap;">
                                        @if($post->link)
                                            <span class="attachment-badge"><i class="fas fa-link"></i> Link</span>
                                        @endif
                                        @if($post->image_path)
                                            @if(str_starts_with($post->image_path, 'uploads/'))
                                                <img src="{{ asset($post->image_path) }}" alt="attachment" class="img-thumbnail" title="Click to view full image">
                                            @else
                                                <span class="attachment-badge"><i class="fas fa-image"></i> Photo</span>
                                            @endif
                                        @endif
                                        @if(!$post->link && !$post->image_path)
                                            <span style="color: var(--text-muted); font-size: 0.8rem;">Text-Only</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    @if($post->status === 'success')
                                        <span class="badge badge-success"><i class="fas fa-check-circle"></i> Success</span>
                                    @else
                                        <span class="badge badge-danger" title="{{ $post->error_message }}">
                                            <i class="fas fa-times-circle"></i> Failed
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if($post->status === 'success' && $post->fb_post_id)
                                        <a href="https://www.facebook.com/{{ $post->fb_post_id }}" target="_blank" class="post-link">
                                            <i class="fab fa-facebook-f"></i> View Post <i class="fas fa-external-link-alt" style="font-size: 0.7rem;"></i>
                                        </a>
                                    @else
                                        <div style="display: flex; flex-direction: column; gap: 0.25rem; align-items: flex-start;">
                                            <span style="font-size: 0.8rem; color: var(--accent-danger); font-weight: 500;" title="{{ $post->error_message }}">
                                                {{ $post->error_message ? \Illuminate\Support\Str::limit($post->error_message, 30) : 'API Error' }}
                                            </span>
                                            @if($post->page && $post->page->account)
                                                <button type="button" class="btn btn-danger-outline" style="padding: 0.15rem 0.4rem; font-size: 0.7rem; width: auto; font-weight: 600; border-radius: 4px; display: inline-flex; align-items: center; gap: 0.25rem; margin-top: 0.25rem; border-color: rgba(239, 68, 68, 0.4); color: var(--accent-danger); cursor: pointer;" onclick="toggleRenewForm('{{ $post->page->account->id }}'); document.getElementById('renew-form-{{ $post->page->account->id }}').scrollIntoView({behavior: 'smooth'});">
                                                    <i class="fas fa-key"></i> Renew Token
                                                </button>
                                            @endif
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

<script>
    function switchTokenMode(mode, element) {
        // Toggle tabs
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        element.classList.add('active');

        // Toggle forms
        document.getElementById('form-oauth-mode').style.display = (mode === 'oauth-mode') ? 'block' : 'none';
        document.getElementById('form-user-mode').style.display = (mode === 'user-mode') ? 'block' : 'none';
        document.getElementById('form-single-mode').style.display = (mode === 'single-mode') ? 'block' : 'none';
    }

    function toggleRenewForm(id) {
        const form = document.getElementById(`renew-form-${id}`);
        if (form.style.display === 'none' || form.style.display === '') {
            form.style.display = 'block';
        } else {
            form.style.display = 'none';
        }
    }
</script>
</body>
</html>
