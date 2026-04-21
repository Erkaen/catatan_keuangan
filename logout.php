<?php
require_once 'config/app.php';
require_once 'includes/auth.php';
logoutUser();
header('Location: login.php');
exit;
