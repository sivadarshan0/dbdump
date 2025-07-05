<?php
// File: entry_grn.php

error_reporting(E_ALL);
ini_set('display_errors', 1);

// ───── DB connection ─────
require_once 'config_db.php';

$msg = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $grnDate   = $_POST['GRNDate'];
    $itemCode  = $_POST['ItemCode'];
    $uom       = $_POST['UOM'];
    $quantity  = $_POST['Quantity'];
    $costPrice = $_POST['CostPrice'];
    $remarks   = $_POST['Remarks'];

    $conn->begin_transaction();

    try {
        // Lock grn_master to avoid duplicate GRNCodes
        $conn->query("LOCK TABLES grn_master WRITE");

        $result = $conn->query("SELECT GRNCode FROM grn_master ORDER BY GRNCode DESC LIMIT 1");
        $lastCode = $result->fetch_assoc()['GRNCode'] ?? null;

        if ($lastCode) {
            $num = (int)substr($lastCode, 3);
            $newGRNCode = 'GRN' . str_pad($num + 1, 5, '0', STR_PAD_LEFT);
        } else {
            $newGRNCode = 'GRN00001';
        }

        // Insert into grn_master
        $stmt = $conn->prepare("INSERT INTO grn_master (GRNCode, GRNDate) VALUES (?, ?)");
        $stmt->bind_param("ss", $newGRNCode, $grnDate);
        $stmt->execute();
        $stmt->close();

        $conn->query("UNLOCK TABLES");

        // Insert into grn_detail
        $stmt2 = $conn->prepare("INSERT INTO grn_detail (GRNCode, ItemCode, UOM, Quantity, CostPrice, Remarks)
                                 VALUES (?, ?, ?, ?, ?, ?)");
        $stmt2->bind_param("sssids", $newGRNCode, $itemCode, $uom, $quantity, $costPrice, $remarks);
        $stmt2->execute();
        $stmt2->close();

        $conn->commit();
        $msg = "✅ GRN $newGRNCode saved successfully.";

    } catch (Exception $e) {
        $conn->rollback();
        $conn->query("UNLOCK TABLES");
        $msg = "❌ Error saving GRN: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>GRN Entry</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="style.css">
</head>
<body>
<a href="index.php" class="back-link">← Back to Main Menu</a>
<div class="container">
  <h1>GRN Entry</h1>

  <?php if ($msg): ?>
    <p class="msg <?= str_starts_with($msg, '✅') ? 'ok' : 'err' ?>"><?= $msg ?></p>
  <?php endif; ?>

  <form method="POST" autocomplete="off">
    <label>GRN Date*</label>
    <input type="date" name="GRNDate" required>

    <label>Item*</label>
    <div class="autocomplete-wrapper">
      <input type="hidden" name="ItemCode" id="ItemCode" required>
      <input type="text" id="ItemName" placeholder="Search Item..." autocomplete="off" required>
      <ul id="itemSuggestions" class="autocomplete-list"></ul>
    </div>

    <label>UOM*</label>
    <select name="UOM" required>
      <option value="">Select</option>
      <option value="No">No</option>
      <option value="Set">Set</option>
      <option value="Pair">Pair</option>
    </select>

    <label>Quantity*</label>
    <input type="number" name="Quantity" min="1" step="1" required>

    <label>Cost Price*</label>
    <input type="number" name="CostPrice" min="0.01" step="0.01" required>

    <label>Remarks</label>
    <textarea name="Remarks" rows="2"></textarea>

    <input type="submit" value="Save GRN">
  </form>
</div>

<script>
const itemNameInput = document.getElementById('ItemName');
const itemCodeInput = document.getElementById('ItemCode');
const suggestionBox = document.getElementById('itemSuggestions');

async function fetchItems(q) {
  if (!q || q.length < 2) {
    suggestionBox.innerHTML = '';
    return;
  }

  try {
    const res = await fetch(`search_item.php?q=${encodeURIComponent(q)}`);
    if (!res.ok) return;
    const items = await res.json();

    suggestionBox.innerHTML = '';
    items.forEach(item => {
      const li = document.createElement('li');
      li.textContent = `${item.Item} (${item.ItemCode})`;
      li.addEventListener('click', () => {
        itemNameInput.value = item.Item;
        itemCodeInput.value = item.ItemCode;
        suggestionBox.innerHTML = '';
      });
      suggestionBox.appendChild(li);
    });
  } catch (e) {
    console.warn('Fetch error:', e);
  }
}

let debounceTimer;
itemNameInput.addEventListener('input', () => {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(() => {
    fetchItems(itemNameInput.value.trim());
  }, 300);
});

document.addEventListener('click', (e) => {
  if (!suggestionBox.contains(e.target) && e.target !== itemNameInput) {
    suggestionBox.innerHTML = '';
  }
});
</script>
</body>
</html>
