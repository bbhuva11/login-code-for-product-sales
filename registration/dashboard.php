<?php
session_start();

// Redirect back to login if session does not exist
if (empty($_SESSION['is_logged'])) {
    header('Location: login.php');
    exit;
}

$fullName = $_SESSION['full_name'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Profile</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 40px;
            display: flex;
            justify-content: center;
        }
        .dashboard-container {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 500px;
            text-align: center;
        }
        h2 {
            margin-top: 0;
            color: #333333;
        }
        .welcome-text {
            font-size: 18px;
            color: #555555;
            margin: 20px 0 30px;
        }
        .user-name {
            font-weight: bold;
            color: #007bff;
        }
        .btn-logout {
            display: inline-block;
            padding: 10px 20px;
            background-color: #dc3545;
            color: #ffffff;
            text-decoration: none;
            border-radius: 6px;
            font-size: 15px;
            transition: background-color 0.2s;
        }
        .btn-logout:hover {
            background-color: #bd2130;
        }
    </style>
</head>
<body>

<div class="dashboard-container">
    <h2>User Profile</h2>
    <p class="welcome-text">
        Welcome, <span class="user-name"><?= htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8') ?></span>!
    </p>
    <a href="logout.php" class="btn-logout">Logout</a>
</div>

</body>
</html>