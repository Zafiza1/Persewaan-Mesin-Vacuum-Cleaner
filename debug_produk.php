<?php
require_once __DIR__ . '/config/database.php';

echo "<h2>Debug Produk Table</h2>";

// Check table structure
echo "<h3>Table Structure:</h3>";
$result = $conn->query("DESCRIBE produk");
if ($result) {
    echo "<table border='1'><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr><td>{$row['Field']}</td><td>{$row['Type']}</td><td>{$row['Null']}</td><td>{$row['Key']}</td></tr>";
    }
    echo "</table>";
} else {
    echo "Error: " . $conn->error;
}

// Check actual data
echo "<h3>Sample Data:</h3>";
$result = $conn->query("SELECT * FROM produk LIMIT 2");
if ($result) {
    echo "<table border='1'>";
    // Header
    echo "<tr>";
    foreach ($result->fetch_fields() as $field) {
        echo "<th>{$field->name}</th>";
    }
    echo "</tr>";
    // Data
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        foreach ($row as $key => $value) {
            echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "Error: " . $conn->error;
}

// Test specific query
echo "<h3>Test Query:</h3>";
$result = $conn->query("SELECT id, nama_mesin, harga_sewa_per_hari, stok FROM produk LIMIT 1");
if ($result) {
    $row = $result->fetch_assoc();
    echo "<pre>";
    print_r($row);
    echo "</pre>";
} else {
    echo "Error: " . $conn->error;
}

$conn->close();
?>
