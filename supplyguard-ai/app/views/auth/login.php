<?php $pageTitle = $data['pageTitle'] ?? 'Login'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="<?= URL_ROOT ?>assets/css/style.css" rel="stylesheet">
</head>
<body>
<div class="login-page">
    <div class="login-card">
        <div class="login-brand">
            <div class="login-brand-icon">
                <i class="fas fa-shield-halved"></i>
            </div>
            <h1><?= APP_NAME ?></h1>
            <p><?= APP_TAGLINE ?></p>
        </div>

        <?php if (Session::hasFlash('error')): ?>
            <div class="alert-custom error">
                <i class="fas fa-exclamation-circle"></i>
                <?= Validator::escape(Session::getFlash('error')) ?>
            </div>
        <?php endif; ?>

        <?php if (Session::hasFlash('success')): ?>
            <div class="alert-custom success">
                <i class="fas fa-check-circle"></i>
                <?= Validator::escape(Session::getFlash('success')) ?>
            </div>
        <?php endif; ?>

        <form action="<?= URL_ROOT ?>auth/do-login" method="POST">
            <?= Auth::csrfField() ?>
            
            <div class="mb-3">
                <label for="email" class="form-label">Email Address</label>
                <input type="email" class="form-control" id="email" name="email" placeholder="admin@supplyguard.ai" required autofocus>
            </div>

            <div class="mb-4">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" placeholder="••••••••" required>
            </div>

            <button type="submit" class="login-btn" id="loginBtn">
                <i class="fas fa-right-to-bracket me-2"></i>Sign In
            </button>
        </form>

        <div style="margin-top: 24px; padding-top: 20px; border-top: 1px solid var(--border-color); text-align: center;">
            <p style="font-size: 11px; color: var(--text-muted); margin-bottom: 8px;">Demo Credentials</p>
            <div style="display: flex; gap: 12px; justify-content: center; flex-wrap: wrap;">
                <div style="padding: 8px 12px; background: var(--bg-input); border-radius: 6px; font-size: 11px;">
                    <strong style="color: var(--text-secondary);">Admin:</strong>
                    <span style="color: var(--text-muted);">admin@supplyguard.ai / admin123</span>
                </div>
                <div style="padding: 8px 12px; background: var(--bg-input); border-radius: 6px; font-size: 11px;">
                    <strong style="color: var(--text-secondary);">Ops:</strong>
                    <span style="color: var(--text-muted);">ops@supplyguard.ai / ops123</span>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
