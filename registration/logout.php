<?php
session_start();

// Unset user authentication data
unset(
    $_SESSION['user_id'],
    $_SESSION['full_name'],
    $_SESSION['username'],
    $_SESSION['email'],
    $_SESSION['is_logged']
);

// Set the logout success flash message
$_SESSION['flash_danger'] = 'You have been successfully logged out.';

header('Location: login.php');
exit;