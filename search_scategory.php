<?php
// File: search_scategory.php

header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ───── DB connection ─────
require_once 'config_db.php';

$query = $_GET['q'] ?? '';
$query = trim($query);
if (strlen($query) < 1) {
    echo json_encode([]);
    exit;
}

$stmt = $conn->prepare("SELECT SubCategoryCode, SubCategory, Description, CategoryCode FROM sub_category WHERE SubCategory LIKE CONCAT(?, '%') ORDER BY SubCategory LIMIT 10");
$stmt->bind_param("s", $query);
$stmt->execute();
$result = $stmt->get_result();

$subs = [];
while ($row = $result->fetch_assoc()) {
    $subs[] = $row;
}

echo json_encode($subs);

$stmt->close();
$conn->close();
