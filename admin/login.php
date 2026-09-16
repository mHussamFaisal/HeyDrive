<?php
require_once '../includes/config.php';

if (is_logged_in() && is_admin()) {
    redirect(APP_URL . '/admin/');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    try {
        $pdo = db_connect();
        $stmt = $pdo->prepare("SELECT * FROM td_users WHERE email = ? AND role IN ('admin') AND status = 'active'");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            redirect(APP_URL . '/admin/');
        } else {
            $error = 'Invalid email or password.';
        }
    } catch (Exception $e) {
        $error = 'Login error. Please try again.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admin Login - TaxisDispatch</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-dark d-flex align-items-center" style="min-height:100vh">
<div class="container">
  <div class="row justify-content-center">
    <div class="col-md-4">
      <div class="text-center mb-4">
        <div class="display-4 text-warning">🚖</div>
        <h3 class="text-white">TaxisDispatch</h3>
        <p class="text-muted">Admin Panel Login</p>
      </div>
      <div class="card shadow border-0">
        <div class="card-body p-4">
          <?php if ($error): ?>
          <div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i><?= $error ?></div>
          <?php endif; ?>
          <form method="POST">
            <div class="mb-3">
              <label class="form-label">Email Address</label>
              <input type="email" class="form-control" name="email" required autofocus 
                     value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>
            <div class="mb-3">
              <label class="form-label">Password</label>
              <input type="password" class="form-control" name="password" required>
            </div>
            <div class="d-grid">
              <button type="submit" class="btn btn-warning btn-lg">
                <i class="fas fa-sign-in-alt me-2"></i>Login
              </button>
            </div>
          </form>
          <div class="text-center mt-3">
            <small class="text-muted">Default: admin@taxisdispatch.com / Admin2024!</small>
          </div>
        </div>
      </div>
      <div class="text-center mt-3">
        <a href="/driver/login.php" class="text-muted small">Driver Login →</a>
      </div>
    </div>
  </div>
</div>
</body>
</html>
