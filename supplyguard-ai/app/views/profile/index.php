<?php require APP_ROOT . '/views/layouts/header.php'; ?>
<?php require APP_ROOT . '/views/layouts/sidebar.php'; ?>
<?php require APP_ROOT . '/views/layouts/navbar.php'; ?>

<div class="page-header">
    <div>
        <h2><i class="fas fa-user-edit me-2" style="color:var(--primary-light);"></i> <?= Validator::escape($data['pageTitle']) ?></h2>
        <div class="breadcrumb">Manage your personal settings</div>
    </div>
</div>

<div class="row">
    <div class="col-md-8 col-lg-6 mx-auto">
        <div class="card" style="box-shadow: var(--shadow-glow);">
            <div class="card-header">
                <h5>Update Details</h5>
            </div>
            <div class="card-body">
                <form action="<?= URL_ROOT ?>profile/update" method="POST">
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">Full Name</label>
                        <input type="text" name="name" class="form-control <?= (!empty($data['name_err'])) ? 'is-invalid' : ''; ?>" value="<?= Validator::escape($data['name']) ?>">
                        <span class="invalid-feedback"><?= $data['name_err'] ?></span>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" name="email" class="form-control <?= (!empty($data['email_err'])) ? 'is-invalid' : ''; ?>" value="<?= Validator::escape($data['email']) ?>">
                        <span class="invalid-feedback"><?= $data['email_err'] ?></span>
                    </div>

                    <hr style="border-color:var(--border-color); margin: 24px 0;">
                    <h6 style="color:var(--text-secondary); margin-bottom: 16px;">Change Password (Optional)</h6>

                    <div class="mb-3">
                        <label for="password" class="form-label">New Password</label>
                        <input type="password" name="password" class="form-control <?= (!empty($data['password_err'])) ? 'is-invalid' : ''; ?>" value="<?= Validator::escape($data['password']) ?>">
                        <span class="invalid-feedback"><?= $data['password_err'] ?></span>
                        <div class="form-text" style="color:var(--text-muted); font-size:11px;">Leave blank if you don't want to change it.</div>
                    </div>

                    <div class="mb-4">
                        <label for="confirm_password" class="form-label">Confirm New Password</label>
                        <input type="password" name="confirm_password" class="form-control <?= (!empty($data['confirm_password_err'])) ? 'is-invalid' : ''; ?>" value="<?= Validator::escape($data['confirm_password']) ?>">
                        <span class="invalid-feedback"><?= $data['confirm_password_err'] ?></span>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="<?= URL_ROOT ?>dashboard" class="btn-outline-custom">Cancel</a>
                        <button type="submit" class="btn-primary-custom">
                            <i class="fas fa-save"></i> Save Changes
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

<?php require APP_ROOT . '/views/layouts/footer.php'; ?>
