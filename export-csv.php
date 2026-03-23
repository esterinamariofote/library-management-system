<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: dashboard.php");
    exit;
}

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="borrowed_books_' . date('Y-m-d') . '.csv"');

$output = fopen('php://output', 'w');
fputcsv($output, ['Title', 'Author', 'Borrowed By', 'Borrow Date', 'Due Date', 'Days Overdue', 'Fine (UGX)']);

$borrowed = $pdo->query("
    SELECT b.title, b.author, u.username, br.borrow_date, br.due_date,
           DATEDIFF(CURDATE(), br.due_date) AS days_overdue,
           GREATEST(0, DATEDIFF(CURDATE(), br.due_date)) * 500 AS fine_ugx
    FROM borrowings br 
    JOIN books b ON br.book_id = b.id 
    JOIN users u ON br.user_id = u.id 
    WHERE br.status = 'borrowed'
")->fetchAll();

foreach ($borrowed as $row) {
    fputcsv($output, [
        $row['title'],
        $row['author'],
        $row['username'],
        $row['borrow_date'],
        $row['due_date'],
        $row['days_overdue'],
        $row['fine_ugx']
    ]);
}

fclose($output);
exit;
?>
