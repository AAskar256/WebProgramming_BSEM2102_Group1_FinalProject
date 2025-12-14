<?php
// logout.php - Handle user logout
require_once 'config.php';

// Destroy all session data
session_destroy();

// Redirect to login page with success message
header('Location: index.php?success=You have been logged out successfully');
exit();
?>