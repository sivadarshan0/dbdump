<?php
// File: entry_item.php

error_reporting(E_ALL);
ini_set('display_errors', 1);

// ───── DB connection ─────
require_once 'config_db.php';

$msg = '';
$itemCode = null;

// Fetch categories
$categories = $conn->query("SELECT CategoryCode, Category FROM category ORDER BY Category") ?: [];

// Fetch sub-categories grouped by category
$subCategoryMap = [];
$subCats = $conn->query("SELECT SubCategoryCode, SubCategory, CategoryCode FROM sub_category ORDER BY SubCategory") ?: [];
while($row = $subCats->fetch_assoc()) {
    $subCategoryMap[$row['CategoryCode']][] = $row;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $item        = $_POST['Item'] ?? '';
    $desc        = $_POST['Description'] ?? '';
    $catCode     = $_POST['CategoryCode'] ?? null;
    $subCatCode  = $_POST['SubCategoryCode'] ?? null;

    // Check if item exists
    $stmt = $conn->prepare("SELECT ItemCode FROM item WHERE Item = ?");
    $stmt->bind_param("s", $item);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // Update
        $stmt->bind_result($itemCode);
        $stmt->fetch();
        $stmt->close();

        $update = $conn->prepare("UPDATE item SET Description=?, CategoryCode=?, SubCategoryCode=? WHERE Item=?");
        $update->bind_param("ssss", $desc, $catCode, $subCatCode, $item);
        if ($update->execute()) {
            $msg = "✅ Item updated successfully (Code: <b>$itemCode</b>)";
        } else {
            $msg = "❌ Error updating: " . $conn->error;
        }
        $update->close();
    } else {
        // Insert
        $stmt->close();
        $insert = $conn->prepare("INSERT INTO item (Item, Description, CategoryCode, SubCategoryCode) VALUES (?, ?, ?, ?)");
        $insert->bind_param("ssss", $item, $desc, $catCode, $subCatCode);
        if ($insert->execute()) {
            $itemCode = $conn->insert_id;
            $msg = "✅ Item added. Code: <b>$itemCode</b>";
        } else {
            $msg = "❌ Error inserting: " . $conn->error;
        }
        $insert->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Item Entry</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="style.css">
<style>
.autocomplete-list {
    border: 1px solid #ccc;
    background: white;
    list-style: none;
    margin: 0;
    padding: 0;
    max-height: 150px;
    overflow-y: auto;
    position: absolute;
    width: 200px;
    z-index: 1000;
}
.autocomplete-list li {
    padding: 8px;
    cursor: pointer;
}
.autocomplete-list li:hover {
    background-color: #f0f0f0;
}
</style>
</head>
<body>
<div class="container">
    <h1>Item Entry</h1>

    <?php if ($msg): ?>
        <p class="msg <?= str_starts_with($msg, '✅') ? 'ok' : 'err' ?>"><?= $msg ?></p>
    <?php endif; ?>

    <form id="itemForm" method="POST" autocomplete="off">
        <label for="CategoryCode">Category*</label>
        <select name="CategoryCode" id="CategoryCode" required>
            <option value="">-- Select Category --</option>
            <?php if ($categories && $categories instanceof mysqli_result): ?>
                <?php while($row = $categories->fetch_assoc()): ?>
                    <option value="<?= $row['CategoryCode'] ?>"><?= htmlspecialchars($row['Category']) ?></option>
                <?php endwhile; ?>
            <?php endif; ?>
        </select>
        <a href="entry_category.php" class="back-link">➕ Add Category</a>

        <label for="SubCategoryCode">Sub-Category*</label>
        <select name="SubCategoryCode" id="SubCategoryCode" required>
            <option value="">-- Select Sub-Category --</option>
        </select>
        <a href="entry_scategory.php" class="back-link">➕ Add Sub-Category</a>

        <label for="Item">Item*</label>
        <div class="autocomplete-wrapper">
            <input type="text" name="Item" id="Item" required>
            <ul id="suggestions" class="autocomplete-list"></ul>
        </div>

        <label for="Description">Description</label>
        <textarea name="Description" id="Description" rows="3"></textarea>

        <input type="submit" value="Save Item">
    </form>

    <?php if ($itemCode): ?>
        <p class="msg ok">🏦 Your Item Code: <strong><?= $itemCode ?></strong></p>
    <?php endif; ?>

    <p><a href="index.php" class="back-link">&larr; Back to Menu</a></p>
</div>

<script>
const subCategoryMap = <?= json_encode($subCategoryMap) ?>;
const catSelect = document.getElementById('CategoryCode');
const subCatSelect = document.getElementById('SubCategoryCode');
const itemInput = document.getElementById('Item');
const suggestionsList = document.getElementById('suggestions');

// Handle category change
catSelect.addEventListener('change', function() {
    const selectedCat = this.value;
    subCatSelect.innerHTML = '<option value="">-- Select Sub-Category --</option>';
    if (subCategoryMap[selectedCat]) {
        subCategoryMap[selectedCat].forEach(sc => {
            const opt = document.createElement('option');
            opt.value = sc.SubCategoryCode;
            opt.textContent = sc.SubCategory;
            subCatSelect.appendChild(opt);
        });
    }
});

// Form validation
document.getElementById('itemForm').addEventListener('submit', function(e) {
    const cat = catSelect.value;
    const sub = subCatSelect.value;
    const item = itemInput.value.trim();
    if (!cat || !sub || item.length < 3) {
        alert('Please fill all fields correctly.');
        e.preventDefault();
    }
});

// Live search + auto-fill
itemInput.addEventListener('input', async () => {
    const query = itemInput.value.trim();
    suggestionsList.innerHTML = '';

    if (query.length < 1) return;

    const res = await fetch(`search_item.php?q=${encodeURIComponent(query)}`);
    if (!res.ok) return;

    const items = await res.json();
    items.forEach(data => {
        const li = document.createElement('li');
        li.textContent = data.Item;
        li.addEventListener('click', () => {
            itemInput.value = data.Item;
            document.getElementById('Description').value = data.Description;
            catSelect.value = data.CategoryCode;
            catSelect.dispatchEvent(new Event('change'));
            setTimeout(() => {
                subCatSelect.value = data.SubCategoryCode;
            }, 100);
            suggestionsList.innerHTML = '';
        });
        suggestionsList.appendChild(li);
    });
});

// Hide dropdown when clicking outside
document.addEventListener('click', (e) => {
    if (!suggestionsList.contains(e.target) && e.target !== itemInput) {
        suggestionsList.innerHTML = '';
    }
});
</script>
</body>
</html>
