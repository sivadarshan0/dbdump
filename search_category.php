<?php
// File: search_category.php

header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ───── DB connection ─────
require_once 'config_db.php';

$query = $_GET['q'] ?? '';
if (strlen($query) < 1) {
    echo json_encode([]);
    exit;
}

$stmt = $conn->prepare("SELECT CategoryCode, Category, Description FROM category WHERE Category LIKE CONCAT(?, '%') ORDER BY Category LIMIT 10");
$stmt->bind_param("s", $query);
$stmt->execute();
$result = $stmt->get_result();

$categories = [];
while ($row = $result->fetch_assoc()) {
    $categories[] = $row;
}

echo json_encode($categories);

$stmt->close();
$conn->close();
