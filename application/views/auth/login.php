<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login &mdash; Bulesi Trang</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        body {
            background: #f0f2f5;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            width: 100%;
            max-width: 420px;
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 24px rgba(0,0,0,.10);
        }
        .login-card .card-header {
            background: #1a1a2e;
            color: #fff;
            border-radius: 12px 12px 0 0;
            text-align: center;
            padding: 28px 24px 20px;
        }
        .login-card .card-header img {
            width: 80px;
            height: 80px;
            object-fit: contain;
            margin-bottom: 10px;
            filter: drop-shadow(0 2px 6px rgba(0,0,0,.4));
        }
        .login-card .card-header h4 {
            margin: 0;
            font-weight: 700;
            letter-spacing: .5px;
            font-size: 1.25rem;
        }
        .login-card .card-body {
            padding: 32px 32px 24px;
        }
        .btn-login {
            background: #1a1a2e;
            color: #fff;
            border: none;
            padding: 10px;
            font-size: 1rem;
            border-radius: 8px;
        }
        .btn-login:hover {
            background: #16213e;
            color: #fff;
        }
        .form-control:focus {
            border-color: #1a1a2e;
            box-shadow: 0 0 0 .2rem rgba(26,26,46,.15);
        }
    </style>
</head>
<body>

<div class="card login-card">
    <div class="card-header">
        <img src="<?= base_url('assets/img/logo.png') ?>" alt="Bulesi Trang Logo">
        <h4>Bulesi Trang</h4>
        <small class="text-white-50">Sign in to your account</small>
    </div>
    <div class="card-body">

        <?php if ($this->session->flashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show py-2" role="alert">
                <?= htmlspecialchars($this->session->flashdata('error')) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php echo form_open('auth/do_login'); ?>

            <div class="mb-3">
                <label for="login" class="form-label fw-semibold">Email or Username</label>
                <input
                    type="text"
                    id="login"
                    name="login"
                    class="form-control <?= form_error('login') ? 'is-invalid' : '' ?>"
                    value="<?= set_value('login') ?>"
                    placeholder="Enter your email or username"
                    autofocus
                >
                <?php if (form_error('login')): ?>
                    <div class="invalid-feedback"><?= form_error('login') ?></div>
                <?php endif; ?>
            </div>

            <div class="mb-4">
                <label for="password" class="form-label fw-semibold">Password</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    class="form-control <?= form_error('password') ? 'is-invalid' : '' ?>"
                    placeholder="Enter your password"
                >
                <?php if (form_error('password')): ?>
                    <div class="invalid-feedback"><?= form_error('password') ?></div>
                <?php endif; ?>
            </div>

            <div class="d-grid">
                <button type="submit" class="btn btn-login">Sign In</button>
            </div>

        <?php echo form_close(); ?>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
