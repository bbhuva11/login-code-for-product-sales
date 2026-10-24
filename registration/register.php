<?php
session_start();
require_once 'db.php';

// Handle POST submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $errors = [];

    // Field-specific validations
    if (empty($fullName)) {
        $errors['full_name'] = 'Full name is required.';
    }

    if (empty($email)) {
        $errors['email'] = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address.';
    }

    if (empty($username)) {
        $errors['username'] = 'Username is required.';
    } elseif (!preg_match('/^[a-zA-Z0-9_]{3,30}$/', $username)) {
        $errors['username'] = 'Username must be 3-30 characters (letters, numbers, underscores).';
    }

    if (empty($password)) {
        $errors['password'] = 'Password is required.';
    } elseif (strlen($password) < 6) {
        $errors['password'] = 'Password must be at least 6 characters long.';
    }

    // Unique checks run on Register click
    if (empty($errors['email'])) {
        $checkEmail = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
        $checkEmail->execute([':email' => $email]);
        if ($checkEmail->fetch()) {
            $errors['email'] = 'This email is already registered.';
        }
    }

    if (empty($errors['username'])) {
        $checkUser = $pdo->prepare('SELECT id FROM users WHERE username = :username LIMIT 1');
        $checkUser->execute([':username' => $username]);
        if ($checkUser->fetch()) {
            $errors['username'] = 'This username is already taken.';
        }
    }

    // Process registration or pass errors back via session
    if (empty($errors)) {
        $hashedPassword = md5($password);

        $insertStmt = $pdo->prepare('
            INSERT INTO users (full_name, email, username, password)
            VALUES (:full_name, :email, :username, :password)
        ');

        $result = $insertStmt->execute([
            ':full_name' => $fullName,
            ':email'    => $email,
            ':username' => $username,
            ':password' => $hashedPassword,
        ]);

        if ($result) {
            $_SESSION['flash_success'] = 'Registration successful! You can now log in.';
        } else {
            $_SESSION['flash_errors'] = ['general' => 'An error occurred while creating your account. Please try again.'];
        }
    } else {
        $_SESSION['flash_errors'] = $errors;
        $_SESSION['form_data'] = [
            'full_name' => $fullName,
            'email'     => $email,
            'username'  => $username
        ];
    }

    // Redirect to clear POST data and avoid duplicate submissions
    header('Location: register.php');
    exit;
}

// Retrieve flash data for displaying once, then clear immediately
$errors   = $_SESSION['flash_errors'] ?? [];
$formData = $_SESSION['form_data'] ?? [];
$success  = $_SESSION['flash_success'] ?? '';

unset($_SESSION['flash_errors'], $_SESSION['form_data'], $_SESSION['flash_success']);

$fullName = $formData['full_name'] ?? '';
$email    = $formData['email'] ?? '';
$username = $formData['username'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Registration</title>
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
        input[type="email"],
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
        input[type="email"]:focus,
        input[type="password"]:focus {
            border-color: #007bff;
        }
        input.input-error {
            border: 1.5px solid #e04444 !important;
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
        .btn-login {
            display: block;
            width: 100%;
            box-sizing: border-box;
            padding: 12px;
            background-color: #6c757d;
            color: #ffffff;
            text-align: center;
            text-decoration: none;
            border-radius: 6px;
            font-size: 16px;
            margin-top: 10px;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        .btn-login:hover {
            background-color: #5a6268;
        }
        .message-success {
            background-color: #e6ffe6;
            color: #28a745;
            border: 1px solid #28a745;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 16px;
            font-size: 14px;
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
    </style>
</head>
<body>

<div class="form-container">
    <h2>Registration</h2>

    <?php if (!empty($errors['general'])): ?>
        <div class="message-general-error">
            <?= htmlspecialchars($errors['general'], ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <div class="message-success">
            <?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <form action="register.php" method="POST" autocomplete="off" novalidate>
        <div class="form-group">
            <label for="full_name">Full Name</label>
            <input 
                type="text" 
                id="full_name" 
                name="full_name" 
                autocomplete="off"
                class="<?= isset($errors['full_name']) ? 'input-error' : '' ?>"
                value="<?= htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8') ?>"
            >
            <?php if (isset($errors['full_name'])): ?>
                <span class="error-text"><?= htmlspecialchars($errors['full_name'], ENT_QUOTES, 'UTF-8') ?></span>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="email">Email</label>
            <input 
                type="email" 
                id="email" 
                name="email" 
                autocomplete="off"
                class="<?= isset($errors['email']) ? 'input-error' : '' ?>"
                value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>"
            >
            <span id="email_error" class="error-text" style="<?= isset($errors['email']) ? '' : 'display: none;' ?>">
                <?= htmlspecialchars($errors['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>
            </span>
        </div>

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
                autocomplete="new-password"
                class="<?= isset($errors['password']) ? 'input-error' : '' ?>"
            >
            <?php if (isset($errors['password'])): ?>
                <span class="error-text"><?= htmlspecialchars($errors['password'], ENT_QUOTES, 'UTF-8') ?></span>
            <?php endif; ?>
        </div>

        <button type="submit">Register</button>
        <a href="login.php" class="btn-login">Login</a>
    </form>
</div>

<script>
    // Real-time email validation while typing
    const emailInput = document.getElementById('email');
    const emailError = document.getElementById('email_error');
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    emailInput.addEventListener('input', function () {
        const val = emailInput.value.trim();

        if (val.length > 0 && !emailRegex.test(val)) {
            emailError.textContent = 'Please enter a valid email address.';
            emailError.style.display = 'block';
            emailInput.classList.add('input-error');
        } else {
            emailError.textContent = '';
            emailError.style.display = 'none';
            emailInput.classList.remove('input-error');
        }
    });

    // Clear browser history duplicates
    if (window.history.replaceState) {
        window.history.replaceState(null, null, window.location.href);
    }
</script>

</body>
</html>