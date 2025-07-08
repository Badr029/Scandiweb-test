<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/src/Scripts/Import.php';

use App\Config\Database;

/**
 * Database Setup Script
 * 
 * Sets up the database schema and imports data from data.json
 * Provides proper error handling and feedback
 */
class DatabaseSetup
{
    private const SCHEMA_FILE = __DIR__ . '/database_schema.sql';
    
    public static function run(): void
    {
        echo "🚀 Starting database setup...\n";
        
        try {
            self::createSchema();
            self::importData();
            echo "✅ Database setup completed successfully!\n";
        } catch (Exception $e) {
            echo "❌ Setup failed: " . $e->getMessage() . "\n";
            exit(1);
        }
    }
    
    /**
     * Create database schema from SQL file
     */
    private static function createSchema(): void
    {
        echo "📋 Creating database schema...\n";
        
        if (!file_exists(self::SCHEMA_FILE)) {
            throw new RuntimeException("Schema file not found: " . self::SCHEMA_FILE);
        }
        
        $sql = file_get_contents(self::SCHEMA_FILE);
        if ($sql === false) {
            throw new RuntimeException("Failed to read schema file");
        }
        
        // Split SQL into individual statements
        $statements = array_filter(
            array_map('trim', explode(';', $sql)),
            fn($stmt) => !empty($stmt) && !str_starts_with($stmt, '--')
        );
        
        foreach ($statements as $statement) {
            if (!Database::execute($statement)) {
                throw new RuntimeException("Failed to execute SQL statement: " . substr($statement, 0, 100) . "...");
            }
        }
        
        echo "✅ Database schema created successfully!\n";
    }
    
    /**
     * Import data using the DatabaseImporter class
     */
    private static function importData(): void
    {
        echo "📦 Importing data from data.json...\n";
        
        $importer = new DatabaseImporter();
        
        if (!$importer->importAll()) {
            throw new RuntimeException("Data import failed");
        }
        
        $stats = $importer->getImportStats();
        echo "✅ Data imported successfully!\n";
        echo "📊 Import statistics:\n";
        foreach ($stats as $key => $value) {
            echo "   • {$key}: {$value}\n";
        }
    }
}

// Run setup if called directly
if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    DatabaseSetup::run();
} 