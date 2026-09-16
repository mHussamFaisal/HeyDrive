<?php
require_once '../includes/config.php';

if (is_logged_in() && is_driver()) {
    redirect(APP_URL . '/driver/');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    try {
        $pdo = db_connect();
        $stmt = $pdo->prepare("SELECT * FROM td_users WHERE email=? AND role='driver' AND status='active'");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['role'] = $user['role'];
            redirect(APP_URL . '/driver/');
        } else {
            $error = 'Invalid credentials.';
        }
    } catch (Exception $e) { $error = 'Login error.'; }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Driver Login - TaxisDispatch</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-dark d-flex align-items-center" style="min-height:100vh">
<div class="container">
  <div class="row justify-content-center">
    <div class="col-md-4">
      <div class="text-center mb-4">
        <div class="display-4">🚖</div>
        <h3 class="text-white">Driver Portal</h3>
        <p class="text-muted">TaxisDispatch</p>
      </div>
      <div class="card border-0 shadow">
        <div class="card-body p-4">
          <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
          <form method="POST">
            <div class="mb-3">
              <label class="form-label">Email</label>
              <input type="email" class="form-control" name="email" required autofocus>
            </div>
            <div class="mb-3">
              <label class="form-label">Password</label>
              <input type="password" class="form-control" name="password" required>
            </div>
            <div class="d-grid">
              <button type="submit" class="btn btn-warning btn-lg">Login as Driver</button>
            </div>
          </form>
        </div>
      </div>
      <div class="text-center mt-3">
        <a href="/admin/login.php" class="text-muted small">Admin Login →</a>
      </div>
    </div>
  </div>
</div>
</body>
</html>
