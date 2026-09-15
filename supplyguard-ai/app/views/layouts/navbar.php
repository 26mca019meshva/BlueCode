<!-- Top Navbar -->
<nav class="top-navbar">
    <div class="navbar-left">
        <button class="menu-toggle navbar-btn" onclick="toggleSidebar()" id="menuToggle">
            <i class="fas fa-bars"></i>
        </button>
        <h1><?= isset($pageTitle) ? Validator::escape($pageTitle) : 'Dashboard' ?></h1>
    </div>
    <div class="navbar-right">
        <div class="ai-workflow" style="padding: 6px 16px; margin: 0; border: none; background: rgba(79, 70, 229, 0.1); border-radius: 20px; gap: 4px; font-size: 11px;">
            <span style="color: var(--primary-light); font-weight: 600;">
                <i class="fas fa-bolt"></i> DETECT → ANALYZE → PREDICT → RECOMMEND → ACT
            </span>
        </div>
        <button class="navbar-btn" title="Notifications" id="notificationBtn">
            <i class="fas fa-bell"></i>
            <span class="notification-dot"></span>
        </button>
        <div class="user-menu dropdown">
            <div data-bs-toggle="dropdown" aria-expanded="false">
                <div style="display:flex;align-items:center;gap:10px;">
                    <div class="user-avatar"><?= strtoupper(substr(Auth::getUserName() ?? 'U', 0, 1)) ?></div>
                    <div class="user-info">
                        <div class="name"><?= Validator::escape(Auth::getUserName() ?? 'User') ?></div>
                        <div class="role"><?= Auth::isAdmin() ? 'Admin' : 'Ops Manager' ?></div>
                    </div>
                </div>
            </div>
            <ul class="dropdown-menu dropdown-menu-end" style="background:var(--bg-card);border-color:var(--border-color);">
                <li><a class="dropdown-item" href="<?= URL_ROOT ?>profile" style="color:var(--text-secondary);"><i class="fas fa-user-edit me-2"></i>Edit Profile</a></li>
                <li><hr class="dropdown-divider" style="border-color:var(--border-color);"></li>
                <li><a class="dropdown-item" href="<?= URL_ROOT ?>logout" style="color:var(--text-secondary);"><i class="fas fa-right-from-bracket me-2"></i>Logout</a></li>
            </ul>
        </div>
    </div>
</nav>

<!-- Main Content Start -->
<main class="main-content">
<div class="content-area">

<?php 
// Display flash messages
if (Session::hasFlash('success')): ?>
    <div class="alert-custom success">
        <i class="fas fa-check-circle"></i>
        <?= Validator::escape(Session::getFlash('success')) ?>
    </div>
<?php endif; ?>

<?php if (Session::hasFlash('error')): ?>
    <div class="alert-custom error">
        <i class="fas fa-exclamation-circle"></i>
        <?= Validator::escape(Session::getFlash('error')) ?>
    </div>
<?php endif; ?>

<?php if (Session::hasFlash('warning')): ?>
    <div class="alert-custom warning">
        <i class="fas fa-exclamation-triangle"></i>
        <?= Validator::escape(Session::getFlash('warning')) ?>
    </div>
<?php endif; ?>
