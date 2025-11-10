<?php
header('Content-Type: text/plain');

echo "Environment Variables Check:\n\n";

$vars = ['DATABASE_URL', 'PGHOST', 'PGDATABASE', 'PGUSER', 'PGPASSWORD', 'PGPORT'];

foreach ($vars as $var) {
    $value = getenv($var);
    if ($value) {
        if ($var === 'PGPASSWORD') {
            echo "$var: ***hidden***\n";
        } else if ($var === 'DATABASE_URL') {
            // Show only the first 30 chars
            echo "$var: " . substr($value, 0, 30) . "...\n";
        } else {
            echo "$var: $value\n";
        }
    } else {
        echo "$var: NOT SET\n";
    }
}
