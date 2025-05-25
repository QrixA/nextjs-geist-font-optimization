<?php
require_once __DIR__ . '/functions.php';
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['lang'] ?? 'en'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SakuraCloudID - <?php echo __('page_title'); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #FF4B91;
            --secondary-color: #FF8DC7;
            --text-color: #1a1a1a;
            --bg-color: #f5f5f5;
            --card-bg: #ffffff;
        }

        /* Modern CSS Reset */
        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            line-height: 1.6;
            color: var(--text-color);
            background-color: var(--bg-color);
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
        }
        
        /* Navigation */
        .nav {
            background: var(--card-bg);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 1rem 0;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .nav-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .nav-logo {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .nav-menu {
            display: flex;
            gap: 2rem;
            list-style: none;
        }
        
        .nav-link {
            color: var(--text-color);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
            padding: 0.5rem 1rem;
            border-radius: 4px;
        }
        
        .nav-link:hover {
            color: var(--primary-color);
            background-color: rgba(255, 75, 145, 0.1);
        }
        
        /* Messages */
        .message {
            padding: 1rem;
            margin: 1rem 0;
            border-radius: 8px;
            border: 1px solid transparent;
        }
        
        .message-success {
            background-color: #d1fae5;
            border-color: #34d399;
            color: #065f46;
        }
        
        .message-error {
            background-color: #fee2e2;
            border-color: #f87171;
            color: #991b1b;
        }
        
        /* Forms */
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--text-color);
        }
        
        .form-input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s, box-shadow 0.3s;
        }
        
        .form-input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(255, 75, 145, 0.1);
        }
        
        .btn {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 500;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: var(--secondary-color);
            transform: translateY(-1px);
        }
        
        /* Cards */
        .card {
            background: var(--card-bg);
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            border: 1px solid #e5e7eb;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 12px rgba(0,0,0,0.1);
        }

        /* Grid Layout */
        .grid {
            display: grid;
            gap: 1.5rem;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        }

        /* Stats Card */
        .stats-card {
            background: var(--card-bg);
            border-radius: 12px;
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .stats-title {
            font-size: 0.875rem;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .stats-value {
            font-size: 1.875rem;
            font-weight: 600;
            color: var(--text-color);
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .nav-menu {
                display: none;
            }
            
            .grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <nav class="nav">
        <div class="nav-container container">
            <a href="/" class="nav-logo">
                🌸 SakuraCloudID
            </a>
            <?php if (is_logged_in()): ?>
            <ul class="nav-menu">
                <li><a href="/dashboard.php" class="nav-link"><?php echo __('dashboard'); ?></a></li>
                <li><a href="/catalog.php" class="nav-link"><?php echo __('catalog'); ?></a></li>
                <li><a href="/services.php" class="nav-link"><?php echo __('services'); ?></a></li>
                <li><a href="/tickets.php" class="nav-link"><?php echo __('tickets'); ?></a></li>
                <?php if (is_admin()): ?>
                <li><a href="/admin/dashboard.php" class="nav-link"><?php echo __('admin'); ?></a></li>
                <?php endif; ?>
                <li><a href="/logout.php" class="nav-link"><?php echo __('logout'); ?></a></li>
            </ul>
            <?php endif; ?>
        </div>
    </nav>
    
    <div class="container" style="padding-top: 2rem; padding-bottom: 2rem;">
        <?php if (isset($_SESSION['message'])): ?>
            <div class="message message-<?php echo $_SESSION['message_type'] ?? 'success'; ?>">
                <?php 
                echo $_SESSION['message'];
                unset($_SESSION['message']);
                unset($_SESSION['message_type']);
                ?>
            </div>
        <?php endif; ?>
