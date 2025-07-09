<?php

declare(strict_types=1);

namespace App\Config;

use PDO;
use PDOException;

/**
 * Database Connection Manager
 * 
 * Provides static methods for database operations with connection pooling.
 * Simplified design for the e-commerce application with MySQL support.
 * 
 * @package App\Config
 */
class Database
{
    private static ?PDO $connection = null;
    
    // InfinityFree Production Database Configuration
    private static string $host = 'sql309.infinityfree.com';
    private static string $database = 'if0_39425959_scandiweb_test';
    private static string $username = 'if0_39425959';
    private static string $password = 'HgSNp9Ng4RgazJ'; 
    private static int $port = 3306; // Standard MySQL port

    /**
     * Get database connection instance
     * Creates connection on first call, reuses on subsequent calls
     * 
     * @return PDO Database connection instance
     * @throws PDOException If connection fails
     */
    public static function getConnection(): PDO
    {
        if (self::$connection === null) {
            self::connect();
        }
        
        return self::$connection;
    }

    /**
     * Establish database connection with PDO
     * Configures PDO with proper error handling and fetch modes
     * 
     * @return void
     * @throws PDOException If connection cannot be established
     */
    private static function connect(): void
    {
        try {
            $dsn = "mysql:host=" . self::$host . ";port=" . self::$port . ";dbname=" . self::$database . ";charset=utf8mb4";
            
            self::$connection = new PDO($dsn, self::$username, self::$password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            throw new PDOException('Database connection failed: ' . $e->getMessage());
        }
    }

    /**
     * Execute SELECT query and return all matching rows
     * 
     * @param string $sql SQL query string with parameter placeholders
     * @param array $params Array of parameters to bind to query
     * @return array Array of associative arrays representing database rows
     * @throws PDOException If query execution fails
     */
    public static function fetchAll(string $sql, array $params = []): array
    {
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Execute SELECT query and return single matching row
     * 
     * @param string $sql SQL query string with parameter placeholders
     * @param array $params Array of parameters to bind to query
     * @return array|false Associative array representing database row, or false if no match
     * @throws PDOException If query execution fails
     */
    public static function fetchOne(string $sql, array $params = []): array|false
    {
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }

    /**
     * Execute non-SELECT query (INSERT, UPDATE, DELETE)
     * 
     * @param string $sql SQL query string with parameter placeholders
     * @param array $params Array of parameters to bind to query
     * @return bool True on successful execution, false otherwise
     * @throws PDOException If query execution fails
     */
    public static function execute(string $sql, array $params = []): bool
    {
        $stmt = self::getConnection()->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Get the ID of the last inserted row
     * 
     * @return string Last insert ID as string
     */
    public static function getLastInsertId(): string
    {
        return self::getConnection()->lastInsertId();
    }

    /**
     * Begin a database transaction
     * 
     * @return bool True on success, false on failure
     */
    public static function beginTransaction(): bool
    {
        return self::getConnection()->beginTransaction();
    }

    /**
     * Commit the current transaction
     * 
     * @return bool True on success, false on failure
     */
    public static function commit(): bool
    {
        return self::getConnection()->commit();
    }

    /**
     * Rollback the current transaction
     * 
     * @return bool True on success, false on failure
     */
    public static function rollback(): bool
    {
        return self::getConnection()->rollback();
    }
} 