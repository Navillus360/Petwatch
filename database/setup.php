<?php
require_once __DIR__ . '/../app/models/Database.php';
use App\Models\Database;
$db = Database::connect();
$schema = file_get_contents(__DIR__ . 'database/schema.sql');
$db->exec($schema);
echo "Database schema created successfully!";