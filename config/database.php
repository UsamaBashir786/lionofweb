<?php

/**
 * Database Connection
 */

// Include config file
require_once 'config.php';

try {
  // Create a PDO instance
  $conn = new PDO(
    "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
    DB_USER,
    DB_PASS,
    [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
      PDO::ATTR_EMULATE_PREPARES => false
    ]
  );
} catch (PDOException $e) {
  // Handle connection error
  die("Connection failed: " . $e->getMessage());
}
