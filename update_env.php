<?php
$envContent = file_get_contents('.env');

// Replace SQLite with MySQL configuration
$envContent = str_replace('DB_CONNECTION=sqlite', 'DB_CONNECTION=mysql', $envContent);
$envContent = str_replace('# DB_HOST=127.0.0.1', 'DB_HOST=127.0.0.1', $envContent);
$envContent = str_replace('# DB_PORT=3306', 'DB_PORT=3306', $envContent);
$envContent = str_replace('# DB_DATABASE=laravel', 'DB_DATABASE=quiz_system_laravel', $envContent);
$envContent = str_replace('# DB_USERNAME=root', 'DB_USERNAME=root', $envContent);
$envContent = str_replace('# DB_PASSWORD=', 'DB_PASSWORD=', $envContent);

file_put_contents('.env', $envContent);
echo "Environment file updated successfully!\n";
?> 