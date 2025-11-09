<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/helpers/jwt.php';
require_once __DIR__ . '/simple_db.php';

$db = new SimpleDatabase();

$userData = JWTHandler::getUserFromRequest();
if (!$userData) {
    sendError('Unauthorized', 401);
}

$userId = $userData->id;
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $action = $_GET['action'] ?? 'dashboard';
    
    if ($action === 'dashboard') {
        $transactions = $db->findAll('transactions', 'user_id', $userId);
        
        $totalIncome = 0;
        $totalExpenses = 0;
        $currentMonthExpenses = 0;
        
        $currentMonth = date('Y-m');
        
        foreach ($transactions as $trans) {
            if ($trans['type'] === 'credit') {
                $totalIncome += $trans['amount'];
            } elseif ($trans['type'] === 'debit') {
                $totalExpenses += $trans['amount'];
                
                if (strpos($trans['date'], $currentMonth) === 0) {
                    $currentMonthExpenses += $trans['amount'];
                }
            }
        }
        
        $balance = $totalIncome - $totalExpenses;
        $transactionCount = count($transactions);
        
        sendResponse([
            'totalIncome' => $totalIncome,
            'totalExpenses' => $totalExpenses,
            'balance' => $balance,
            'currentMonthExpenses' => $currentMonthExpenses,
            'transactionCount' => $transactionCount
        ]);
    }
    
    elseif ($action === 'category_breakdown') {
        $transactions = $db->findAll('transactions', 'user_id', $userId);
        $categories = $db->findAll('categories');
        
        $categoryMap = [];
        foreach ($categories as $cat) {
            $categoryMap[$cat['id']] = [
                'name' => $cat['name'],
                'color' => $cat['color'],
                'amount' => 0
            ];
        }
        
        foreach ($transactions as $trans) {
            if ($trans['type'] === 'debit' && isset($trans['category_id']) && isset($categoryMap[$trans['category_id']])) {
                $categoryMap[$trans['category_id']]['amount'] += $trans['amount'];
            }
        }
        
        $breakdown = array_filter($categoryMap, function($cat) {
            return $cat['amount'] > 0;
        });
        
        sendResponse(['breakdown' => array_values($breakdown)]);
    }
    
    elseif ($action === 'monthly_trend') {
        $transactions = $db->findAll('transactions', 'user_id', $userId);
        $months = [];
        
        foreach ($transactions as $trans) {
            $month = substr($trans['date'], 0, 7);
            if (!isset($months[$month])) {
                $months[$month] = ['income' => 0, 'expenses' => 0];
            }
            
            if ($trans['type'] === 'credit') {
                $months[$month]['income'] += $trans['amount'];
            } else {
                $months[$month]['expenses'] += $trans['amount'];
            }
        }
        
        ksort($months);
        $trend = [];
        foreach ($months as $month => $data) {
            $trend[] = [
                'month' => $month,
                'income' => $data['income'],
                'expenses' => $data['expenses']
            ];
        }
        
        sendResponse(['trend' => array_slice($trend, -6)]);
    }
    
    else {
        sendResponse([
            'totalIncome' => 0,
            'totalExpenses' => 0,
            'balance' => 0,
            'transactionCount' => 0
        ]);
    }
}

else {
    sendError('Method not allowed', 405);
}
