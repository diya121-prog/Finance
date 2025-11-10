<?php

class DatabaseManager {
    private static $conn = null;
    
    public static function getConnection() {
        if (self::$conn === null) {
            try {
                $dbPath = __DIR__ . '/../data/finance_analyzer.db';
                $dataDir = dirname($dbPath);
                
                if (!file_exists($dataDir)) {
                    mkdir($dataDir, 0777, true);
                }
                
                self::$conn = new PDO('sqlite:' . $dbPath);
                self::$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                
                self::initializeTables();
                
            } catch(PDOException $e) {
                error_log("Database connection error: " . $e->getMessage());
                throw $e;
            }
        }
        return self::$conn;
    }
    
    private static function initializeTables() {
        $sql = "
        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            email VARCHAR(255) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            full_name VARCHAR(255) NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS categories (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name VARCHAR(100) NOT NULL,
            type VARCHAR(20) NOT NULL,
            color VARCHAR(50) NOT NULL,
            icon VARCHAR(50) NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS transactions (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            description TEXT NOT NULL,
            amount REAL NOT NULL,
            type VARCHAR(20) NOT NULL,
            category VARCHAR(100) NOT NULL,
            date DATE NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        );

        CREATE INDEX IF NOT EXISTS idx_transactions_user_id ON transactions(user_id);
        CREATE INDEX IF NOT EXISTS idx_transactions_date ON transactions(date);
        CREATE INDEX IF NOT EXISTS idx_transactions_category ON transactions(category);
        ";
        
        self::$conn->exec($sql);
    }
    
    public static function migrateFromJSON() {
        try {
            $conn = self::getConnection();
            $dataDir = __DIR__ . '/../data/';
            
            $categoriesFile = $dataDir . 'categories.json';
            if (file_exists($categoriesFile)) {
                $categories = json_decode(file_get_contents($categoriesFile), true);
                if ($categories && count($categories) > 0) {
                    $stmt = $conn->prepare("INSERT OR IGNORE INTO categories (id, name, type, color, icon) VALUES (?, ?, ?, ?, ?)");
                    foreach ($categories as $category) {
                        $stmt->execute([
                            $category['id'],
                            $category['name'],
                            $category['type'],
                            $category['color'],
                            $category['icon']
                        ]);
                    }
                    echo "Migrated " . count($categories) . " categories\n";
                }
            }
            
            $usersFile = $dataDir . 'users.json';
            if (file_exists($usersFile)) {
                $users = json_decode(file_get_contents($usersFile), true);
                if ($users && count($users) > 0) {
                    $stmt = $conn->prepare("INSERT OR IGNORE INTO users (id, email, password, full_name, created_at) VALUES (?, ?, ?, ?, ?)");
                    foreach ($users as $user) {
                        $stmt->execute([
                            $user['id'],
                            $user['email'],
                            $user['password'],
                            $user['full_name'],
                            $user['created_at'] ?? date('Y-m-d H:i:s')
                        ]);
                    }
                    echo "Migrated " . count($users) . " users\n";
                }
            }
            
            $transactionsFile = $dataDir . 'transactions.json';
            if (file_exists($transactionsFile)) {
                $transactions = json_decode(file_get_contents($transactionsFile), true);
                if ($transactions && count($transactions) > 0) {
                    $stmt = $conn->prepare("INSERT OR IGNORE INTO transactions (id, user_id, description, amount, type, category, date, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                    foreach ($transactions as $transaction) {
                        $stmt->execute([
                            $transaction['id'],
                            $transaction['user_id'],
                            $transaction['description'],
                            $transaction['amount'],
                            $transaction['type'],
                            $transaction['category'],
                            $transaction['date'],
                            $transaction['created_at'] ?? date('Y-m-d H:i:s')
                        ]);
                    }
                    echo "Migrated " . count($transactions) . " transactions\n";
                }
            }
            
            echo "Migration completed successfully!\n";
            return true;
            
        } catch(PDOException $e) {
            error_log("Migration error: " . $e->getMessage());
            echo "Migration error: " . $e->getMessage() . "\n";
            return false;
        }
    }
}
