#!/usr/bin/env php

<?php
// Script to create a db user with all privileges.

$db_host = 'localhost';
$db_admin_user = 'mysql';

$db_name = file_get_contents('/run/secrets/db_name');

if (!$db_name) {
    throw new Exception("DB name secret not found");
}

$db_user = file_get_contents('/run/secrets/db_user');

if (!$db_user) {
    throw new Exception("DB user secret not found");
}

$db_password = file_get_contents('/run/secrets/db_password');

if (!$db_password) {
    throw new Exception("DB password secret not found");
}

$db_user_host = getenv('DB_USER_HOST');

if (!$db_user_host) {
    throw new Exception("DB user host environment variable not set");
}

try {
    // Connect to MariaDB with admin credentials
    $mysqli = new mysqli($db_host, $db_admin_user);

    if ($mysqli->connect_error) {
        throw new Exception("Connection failed: " . $mysqli->connect_error);
    }

    // Create user if not exists
    $create_user_query = "CREATE USER IF NOT EXISTS '" . $mysqli->real_escape_string($db_user) . "'@'" . $mysqli->real_escape_string($db_user_host) . "' IDENTIFIED BY '" . $mysqli->real_escape_string($db_password) . "'";

    if (!$mysqli->query($create_user_query)) {
        throw new Exception("Error creating user: " . $mysqli->error);
    }
    echo "User created successfully.\n";

    // Grant all privileges on the database
    $grant_query = "GRANT ALL PRIVILEGES ON " . $mysqli->real_escape_string($db_name) . ".* TO '" . $mysqli->real_escape_string($db_user) . "'@'" . $mysqli->real_escape_string($db_user_host) . "' WITH GRANT OPTION";

    if (!$mysqli->query($grant_query)) {
        throw new Exception("Error granting privileges: " . $mysqli->error);
    }
    echo "Privileges granted successfully.\n";

    // Flush privileges
    if (!$mysqli->query("FLUSH PRIVILEGES")) {
        throw new Exception("Error flushing privileges: " . $mysqli->error);
    }
    echo "Privileges flushed successfully.\n";

    $mysqli->close();
    echo "User setup completed successfully.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
