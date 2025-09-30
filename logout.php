<?php
// logout.php
// Clear all session data and redirect to homepage

session_start();
session_unset();
session_destroy();

header('Location: homepage.php');
exit;
?>