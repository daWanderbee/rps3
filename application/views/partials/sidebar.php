<?php
// expects: $currentPage
function active($page, $currentPage)
{
    return $page === $currentPage ? 'active' : '';
}
$userRole = $this->session->userdata('emp_cat') ?? 3;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Navbar</title>

    <link rel="stylesheet" href="<?= base_url('css/style.css') ?>">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Material Symbols -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />

    <style>
        :root {
            --sidebar-hover: #8B1F28;
            --primary-color: #FCE7C2;

            --bg: #fae7c1;
            --primary: #a3243c;
            --secondary: #1ea2dc;
            --green: #5e6337;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-color: #f8f9fa;
        }

        /* Sidebar Styles */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: 200px;
            background: var(--primary);
            z-index: 1000;
            transition: transform 0.3s ease;
            display: flex;
            flex-direction: column;
            border-right: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-header {
            padding: 16px;
            text-align: center;
            background: var(--bg);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-header img {
            height: 30px;
            width: auto;
            margin: auto;
        }

        .sidebar-nav {
            flex: 1;
            padding: 20px 0;
            overflow-y: auto;
        }

        .nav-item {
            margin: 4px 12px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 12px 16px;
            color: var(--bg);
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.2s ease;
            font-size: 14px;
            font-weight: 500;
        }

        .nav-link:hover {
            background: var(--sidebar-hover);
            color: #fff;
        }

        .nav-link.active {
            background: var(--primary-color);
            color: #AB2732;
        }

        .nav-link .material-symbols-outlined {
            font-size: 22px;
            margin-right: 12px;
        }


        .top-navbar {
            background: var(--bg);
            position: sticky;
            top: 0;
            z-index: 999;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .btn-notifications {
            margin: 8px;
            width: 56px;
            height: 56px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
            color: #0A0A0A;
            text-decoration: none;
        }

        .btn-icon {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
            background: var(--bg);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-icon:hover {
            background: var(--bg);
        }

        .main-content {
            padding: 32px;
        }

        /* Mobile Menu Toggle */
        .mobile-menu-toggle {
            display: none;
            width: 28px;
            height: 28px;
            border: 1px solid #0A0A0A;
            background: var(--bg);
            border-radius: 4px;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }

        /* Mobile Styles */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                width: 180px;
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .main-wrapper {
                margin-left: 0;
            }

            .mobile-menu-toggle {
                display: flex;
            }

            .top-navbar {
                padding: 16px;
            }

            .main-content {
                padding: 20px;
            }

            .btn-notifications {
                margin: 0px;
                width: 16px;
                height: 16px;
            }

        }

        /* Overlay for mobile */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }

        .sidebar-overlay.show {
            display: block;
        }
    </style>
</head>

<body>

    <!-- Sidebar Overlay (Mobile) -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <a href="/">
                <img src="/uploads/rps/logo/full_logo.svg" alt="Logo">
            </a>
        </div>

        <nav class="sidebar-nav">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a href="/rps/" class="nav-link <?= active('dashboard', $currentPage ?? '') ?>">
                        <span class="material-symbols-outlined">home</span>
                        Home
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/rps/tasks" class="nav-link <?= active('tasks', $currentPage ?? '') ?>">
                        <span class="material-symbols-outlined">school</span>
                        Tasks
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/rps/redeem" class="nav-link <?= active('redeem', $currentPage ?? '') ?>">
                        <span class="material-symbols-outlined">card_giftcard</span>
                        Redeem
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/rps/leaderboard" class="nav-link <?= active('leaderboard', $currentPage ?? '') ?>">
                        <span class="material-symbols-outlined">leaderboard</span>
                        Leaderboard
                    </a>
                </li>
                <?php if ($userRole == 1 || $userRole == 2) : ?>
                    <li class="nav-item">
                        <a href="/rps/reward" class="nav-link <?= active('reward', $currentPage ?? '') ?>">
                            <span class="material-symbols-outlined">volunteer_activism</span>
                            Reward
                        </a>
                    </li>
                <?php endif; ?>
                <li class="nav-item">
                    <a href="/rps/transactions" class="nav-link <?= active('transactions', $currentPage ?? '') ?>">
                        <span class="material-symbols-outlined">receipt_long</span>
                        Transactions
                    </a>
                </li>
                <?php if ($userRole == 1 || $userRole == 2) : ?>
                    <li class="nav-item">
                        <a href="/rps/management" class="nav-link <?= active('management', $currentPage ?? '') ?>">
                            <span class="material-symbols-outlined">manage_accounts</span>
                            Management
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="/rps/admin" class="nav-link <?= active('admin', $currentPage ?? '') ?>">
                            <span class="material-symbols-outlined">admin_panel_settings</span>
                            Admin
                        </a>
                    </li>
                <?php endif; ?>
                <li class="nav-item">
                    <a href="/rps/support" class="nav-link <?= active('support', $currentPage ?? '') ?>">
                        <span class="material-symbols-outlined">help</span>
                        Support
                    </a>
                </li>
                <li class="nav-item mt-3">
                    <a href="/login/logout" class="nav-link">
                        <span class="material-symbols-outlined">logout</span>
                        Logout
                    </a>
            </ul>
        </nav>
    </aside>

    <!-- Main Content Wrapper -->
    <div class="main-wrapper">

        <!-- Top Navbar -->
        <header class="top-navbar">
            <div class="d-flex align-items-center gap-3">
                <button class="mobile-menu-toggle" id="menuToggle">
                    <span class="material-symbols-outlined">menu</span>
                </button>
            </div>
            <a href="/rps/notifications" class="btn-notifications">
                <span class="material-symbols-outlined">notifications</span>
            </a>
        </header>

    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Mobile menu toggle
        const menuToggle = document.getElementById('menuToggle');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');

        menuToggle.addEventListener('click', () => {
            sidebar.classList.toggle('show');
            overlay.classList.toggle('show');
        });

        overlay.addEventListener('click', () => {
            sidebar.classList.remove('show');
            overlay.classList.remove('show');
        });
    </script>

</body>

</html>