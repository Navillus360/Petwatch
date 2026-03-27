<?php
/**
 * Class database
 * This class is designed to be the middle man between the datasets and the database.
 * Allowing datasets to execute SQL queries which can modify or retrieve data from the database.
 */
namespace App\models;
use PDO;
use PDOException;
class Database
{
    private static ?PDO $dbConnection = null;

    /**
     * @return PDO
     * Attempts to connect to the SQLite database and returns its connection upon success
     * Otherwise, prints an error message or creates a new file
     */
    public static function connect(): PDO
    {
        if (self::$dbConnection === null) {
            $rootPath = dirname(__DIR__, 2);
            $dbRelativePath = $_ENV['DB_PATH'] ?? 'database/database.sqlite';
            $dbFullPath = $rootPath . '/' . $dbRelativePath;
            if (!file_exists($dbFullPath)) touch($dbFullPath);
            try {
                self::$dbConnection = new PDO("sqlite:{$dbFullPath}");
                self::$dbConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$dbConnection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                die("Database connection failed: " . $e->getMessage());
            }
        }
        return self::$dbConnection;
    }
}