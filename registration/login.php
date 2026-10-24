<?php
session_start();
require_once 'db.php';

// If user is already logged in, redirect straight to dashboard
if (!empty($_SESSION['is_logged'])) {
    header('Location: dashboard.php');
    exit;
}

// Handle POST submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $errors = [];

    // Field validations
    if (empty($username)) {
        $errors['username'] = 'Username is required.';
    }

    if (empty($password)) {
        $errors['password'] = 'Password is required.';
    }

    if (empty($errors)) {
        // MD5 encryption to match register.php
        $hashedPassword = md5($password);

        // Fetch user matching credentials
        $stmt = $pdo->prepare('SELECT id, full_name, email, username FROM users WHERE username = :username AND password = :password LIMIT 1');
        $stmt->execute([
            ':username' => $username,
            ':password' => $hashedPassword,
        ]);
        $user = $stmt->fetch();

        if ($user) {
            // Update last_login_at timestamp
            $updateStmt = $pdo->prepare('UPDATE users SET last_login_at = NOW() WHERE id = :id');
            $updateStmt->execute([':id' => $user['id']]);

            // Save user data in session
            $_SESSION['user_id']    = $user['id'];
            $_SESSION['full_name']  = $user['full_name'];
            $_SESSION['username']   = $user['username'];
            $_SESSION['email']      = $user['email'];
            $_SESSION['is_logged']  = true;

            // Redirect to dashboard page
            header('Location: dashboard.php');
            exit;
        } else {
            $errors['general'] = 'Invalid username or password.';
        }
    }

    // Save errors and state to session
    $_SESSION['flash_errors'] = $errors;
    $_SESSION['form_data']    = ['username' => $username];

    // PRG pattern to eliminate "Confirm Form Resubmission" alert
    header('Location: login.php');
    exit;
}

// Retrieve flash data for displaying once, then clear immediately
$errors   = $_SESSION['flash_errors'] ?? [];
$formData = $_SESSION['form_data'] ?? [];
$success  = $_SESSION['flash_danger'] ?? '';

unset($_SESSION['flash_errors'], $_SESSION['form_data'], $_SESSION['flash_danger']);

$username = $formData['username'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 40px;
            display: flex;
            justify-content: center;
        }
        .form-container {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }
        h2 {
            margin-top: 0;
            margin-bottom: 20px;
            text-align: center;
            color: #333333;
        }
        .form-group {
            margin-bottom: 16px;
        }
        label {
            display: block;
            margin-bottom: 6px;
            font-weight: bold;
            color: #333333;
        }
        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #cccccc;
            border-radius: 6px;
            box-sizing: border-box;
            font-size: 14px;
            outline: none;
            transition: border-color 0.2s;
        }
        input[type="text"]:focus,
        input[type="password"]:focus {
            border-color: #007bff;
        }
        input.input-error {
            border: 1.5px solid #e04444;
        }
        .error-text {
            color: #e04444;
            font-size: 13px;
            margin-top: 5px;
            display: block;
        }
        button[type="submit"] {
            width: 100%;
            padding: 12px;
            background-color: #007bff;
            border: none;
            border-radius: 6px;
            color: #ffffff;
            font-size: 16px;
            cursor: pointer;
            margin-top: 8px;
        }
        button[type="submit"]:hover {
            background-color: #0056b3;
        }
        .message-success {
            background-color: #e6ffe6;
            color: #28a745;
            border: 1px solid #28a745;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 16px;
            font-size: 14px;
            opacity: 1;
            transition: opacity 0.5s ease;
        }
        .message-general-error {
            background-color: #ffe6e6;
            color: #d9534f;
            border: 1px solid #d9534f;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 16px;
            font-size: 14px;
        }
        .link-text {
            text-align: center;
            margin-top: 16px;
            font-size: 14px;
            color: #666666;
        }
        .link-text a {
            color: #007bff;
            text-decoration: none;
        }
        .link-text a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="form-container">
    <h2>Login</h2>

    <?php if (!empty($success)): ?>
        <div id="flash-message" class="message-general-error">
            <?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($errors['general'])): ?>
        <div class="message-general-error">
            <?= htmlspecialchars($errors['general'], ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <form action="login.php" method="POST" autocomplete="off" novalidate>
        <div class="form-group">
            <label for="username">Username</label>
            <input 
                type="text" 
                id="username" 
                name="username" 
                autocomplete="off"
                class="<?= isset($errors['username']) ? 'input-error' : '' ?>"
                value="<?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8') ?>"
            >
            <?php if (isset($errors['username'])): ?>
                <span class="error-text"><?= htmlspecialchars($errors['username'], ENT_QUOTES, 'UTF-8') ?></span>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input 
                type="password" 
                id="password" 
                name="password" 
                autocomplete="current-password"
                class="<?= isset($errors['password']) ? 'input-error' : '' ?>"
            >
            <?php if (isset($errors['password'])): ?>
                <span class="error-text"><?= htmlspecialchars($errors['password'], ENT_QUOTES, 'UTF-8') ?></span>
            <?php endif; ?>
        </div>

        <button type="submit">Login</button>
    </form>

    <div class="link-text">
        Don't have an account? <a href="register.php">Register</a>
    </div>
</div>

<script>
    // Fade out and remove message after 3 seconds
    const flashMsg = document.getElementById('flash-message');
    if (flashMsg) {
        setTimeout(() => {
            flashMsg.style.opacity = '0';
            setTimeout(() => {
                flashMsg.remove();
            }, 500);
        }, 3000);
    }

    if (window.history.replaceState) {
        window.history.replaceState(null, null, window.location.href);
    }
</script>

</body>
</html>