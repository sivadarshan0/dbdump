<?php
// File: search_item.php

header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ───── DB connection ─────
require_once 'config_db.php';

$q = trim($_GET['q'] ?? '');
if (strlen($q) < 1) {
    echo json_encode([]);
    exit;
}

$stmt = $conn->prepare("SELECT Item, ItemCode, Description, CategoryCode, SubCategoryCode FROM item WHERE Item LIKE CONCAT(?, '%') ORDER BY Item LIMIT 10");
$stmt->bind_param("s", $q);
$stmt->execute();
$result = $stmt->get_result();

$suggestions = [];
while ($row = $result->fetch_assoc()) {
    $suggestions[] = $row;
}

echo json_encode($suggestions);

$stmt->close();
$conn->close();
?>
