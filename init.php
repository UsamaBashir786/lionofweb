<?php
// Prevent output before redirect
ob_start();

// Include functions and check if page exists
require_once 'includes/functions.php';
checkPageExists();
