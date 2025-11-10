<?php
require_once __DIR__ . '/config.php';

header('Content-Type: text/plain');

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    echo "Connected to database successfully!\n\n";
    
    // Create users table
    $conn->exec("
        CREATE TABLE IF NOT EXISTS users (
            id SERIAL PRIMARY KEY,
            email VARCHAR(255) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            full_name VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    echo "✓ Users table created\n";
    
    // Create categories table
    $conn->exec("
        CREATE TABLE IF NOT EXISTS categories (
            id SERIAL PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            keywords TEXT,
            color VARCHAR(7) DEFAULT '#6366f1',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    echo "✓ Categories table created\n";
    
    // Create transactions table
    $conn->exec("
        CREATE TABLE IF NOT EXISTS transactions (
            id SERIAL PRIMARY KEY,
            user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
            date DATE NOT NULL,
            description VARCHAR(500) NOT NULL,
            amount DECIMAL(12, 2) NOT NULL,
            type VARCHAR(10) NOT NULL CHECK (type IN ('credit', 'debit')),
            category VARCHAR(100),
            notes TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    echo "✓ Transactions table created\n";
    
    // Create indexes
    $conn->exec("CREATE INDEX IF NOT EXISTS idx_transactions_user_date ON transactions(user_id, date DESC)");
    $conn->exec("CREATE INDEX IF NOT EXISTS idx_transactions_category ON transactions(category)");
    echo "✓ Indexes created\n";
    
    // Insert default categories
    $categories = [
        ['Food & Dining', 'swiggy,zomato,restaurant,cafe,food,dining,pizza,burger', '#ef4444'],
        ['Transport', 'uber,ola,taxi,metro,bus,fuel,petrol,gas,parking', '#f59e0b'],
        ['Entertainment', 'netflix,spotify,prime,hotstar,disney,youtube,movie,cinema', '#8b5cf6'],
        ['Shopping', 'amazon,flipkart,myntra,shopping,mall,store,retail', '#ec4899'],
        ['Bills & Utilities', 'electricity,water,gas,internet,broadband,wifi,telephone,mobile', '#14b8a6'],
        ['Healthcare', 'hospital,clinic,pharmacy,doctor,medical,health,medicine', '#06b6d4'],
        ['Education', 'school,college,university,course,udemy,coursera,book,education', '#3b82f6'],
        ['Groceries', 'grocery,supermarket,bigbasket,grofers,blinkit,vegetable', '#10b981'],
        ['Travel', 'flight,hotel,booking,airbnb,makemytrip,travel,vacation,trip', '#f97316'],
        ['Income', 'salary,income,payment,refund,cashback,credit,deposit', '#22c55e'],
        ['Other', 'miscellaneous,other,general', '#64748b']
    ];
    
    $stmt = $conn->prepare("INSERT INTO categories (name, keywords, color) VALUES (?, ?, ?) ON CONFLICT DO NOTHING");
    foreach ($categories as $cat) {
        $stmt->execute($cat);
    }
    echo "✓ Categories populated\n";
    
    // Check how many categories were inserted
    $count = $conn->query("SELECT COUNT(*) FROM categories")->fetchColumn();
    echo "✓ Total categories in database: $count\n\n";
    
    echo "✅ Database initialization completed successfully!\n\n";
    echo "You can now delete this file (api/setup.php) for security.\n";
    
} catch(PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    http_response_code(500);
}
