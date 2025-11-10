<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/helpers/jwt.php';
require_once __DIR__ . '/database.php';

try {
    $conn = DatabaseManager::getConnection();
} catch (Exception $e) {
    sendError('Database connection failed', 500);
}

$userData = JWTHandler::getUserFromRequest();
if (!$userData) {
    sendError('Unauthorized', 401);
}

$userId = $userData->id;
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $action = $_GET['action'] ?? 'list';
    
    if ($action === 'list') {
        try {
            $stmt = $conn->prepare("SELECT * FROM transactions WHERE user_id = ? ORDER BY date DESC");
            $stmt->execute([$userId]);
            $transactions = $stmt->fetchAll();
            
            sendResponse(['transactions' => $transactions]);
        } catch (Exception $e) {
            sendError('Failed to fetch transactions: ' . $e->getMessage(), 500);
        }
    }
    
    elseif ($action === 'categories') {
        try {
            $stmt = $conn->query("SELECT * FROM categories");
            $categories = $stmt->fetchAll();
            sendResponse(['categories' => $categories]);
        } catch (Exception $e) {
            sendError('Failed to fetch categories: ' . $e->getMessage(), 500);
        }
    }
}

elseif ($method === 'POST') {
    $action = $_GET['action'] ?? '';
    $input = json_decode(file_get_contents('php://input'), true);
    
    if ($action === 'add') {
        if (!isset($input['date']) || !isset($input['description']) || !isset($input['amount']) || !isset($input['type'])) {
            sendError('Missing required fields', 400);
        }
        
        try {
            $stmt = $conn->prepare("INSERT INTO transactions (user_id, date, description, amount, type, category) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $userId,
                $input['date'],
                htmlspecialchars($input['description']),
                (float)$input['amount'],
                $input['type'],
                $input['category'] ?? ''
            ]);
            
            $transactionId = $conn->lastInsertId();
            
            $stmt = $conn->prepare("SELECT * FROM transactions WHERE id = ?");
            $stmt->execute([$transactionId]);
            $transaction = $stmt->fetch();
            
            sendResponse([
                'message' => 'Transaction added successfully',
                'transaction' => $transaction
            ], 201);
        } catch (Exception $e) {
            sendError('Failed to add transaction: ' . $e->getMessage(), 500);
        }
    }
    
    elseif ($action === 'bulk') {
        if (!isset($input['transactions']) || !is_array($input['transactions'])) {
            sendError('Invalid bulk data', 400);
        }
        
        try {
            $stmt = $conn->prepare("INSERT INTO transactions (user_id, date, description, amount, type, category) VALUES (?, ?, ?, ?, ?, ?)");
            $added = 0;
            
            foreach ($input['transactions'] as $trans) {
                $stmt->execute([
                    $userId,
                    $trans['date'],
                    htmlspecialchars($trans['description']),
                    (float)$trans['amount'],
                    $trans['type'],
                    $trans['category'] ?? ''
                ]);
                $added++;
            }
            
            sendResponse([
                'message' => $added . ' transactions added successfully',
                'count' => $added
            ], 201);
        } catch (Exception $e) {
            sendError('Failed to add transactions: ' . $e->getMessage(), 500);
        }
    }
}

elseif ($method === 'PUT') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $id = (int)($_GET['id'] ?? $input['id'] ?? 0);
    if (!$id) {
        sendError('Transaction ID required', 400);
    }
    
    try {
        $stmt = $conn->prepare("SELECT * FROM transactions WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $userId]);
        $transaction = $stmt->fetch();
        
        if (!$transaction) {
            sendError('Transaction not found', 404);
        }
        
        $updates = [];
        $params = [];
        
        if (isset($input['date'])) {
            $updates[] = "date = ?";
            $params[] = $input['date'];
        }
        if (isset($input['description'])) {
            $updates[] = "description = ?";
            $params[] = htmlspecialchars($input['description']);
        }
        if (isset($input['amount'])) {
            $updates[] = "amount = ?";
            $params[] = (float)$input['amount'];
        }
        if (isset($input['type'])) {
            $updates[] = "type = ?";
            $params[] = $input['type'];
        }
        if (isset($input['category'])) {
            $updates[] = "category = ?";
            $params[] = $input['category'];
        }
        
        if (count($updates) > 0) {
            $updates[] = "updated_at = CURRENT_TIMESTAMP";
            $params[] = $id;
            $params[] = $userId;
            
            $sql = "UPDATE transactions SET " . implode(', ', $updates) . " WHERE id = ? AND user_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
        }
        
        sendResponse(['message' => 'Transaction updated successfully']);
    } catch (Exception $e) {
        sendError('Failed to update transaction: ' . $e->getMessage(), 500);
    }
}

elseif ($method === 'DELETE') {
    $id = (int)($_GET['id'] ?? 0);
    if (!$id) {
        sendError('Transaction ID required', 400);
    }
    
    try {
        $stmt = $conn->prepare("SELECT * FROM transactions WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $userId]);
        $transaction = $stmt->fetch();
        
        if (!$transaction) {
            sendError('Transaction not found', 404);
        }
        
        $stmt = $conn->prepare("DELETE FROM transactions WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $userId]);
        
        sendResponse(['message' => 'Transaction deleted successfully']);
    } catch (Exception $e) {
        sendError('Failed to delete transaction: ' . $e->getMessage(), 500);
    }
}

else {
    sendError('Method not allowed', 405);
}
