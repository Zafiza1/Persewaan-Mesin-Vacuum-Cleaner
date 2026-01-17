<?php
require_once __DIR__ . '/config/database.php';

echo "<h2>Database Structure Analysis</h2>";

// Check table structure
echo "<h3>Produk Table Structure:</h3>";
$result = $conn->query("DESCRIBE produk");
if ($result) {
    echo "<table border='1'><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr><td>{$row['Field']}</td><td>{$row['Type']}</td><td>{$row['Null']}</td><td>{$row['Key']}</td></tr>";
    }
    echo "</table>";
    
    // List all available columns
    $result->data_seek(0);
    $columns = [];
    while ($row = $result->fetch_assoc()) {
        $columns[] = $row['Field'];
    }
    echo "<h3>Available Columns: " . implode(', ', $columns) . "</h3>";
} else {
    echo "Error: " . $conn->error;
}

// Test query with minimal columns
echo "<h3>Test Query with Minimal Columns:</h3>";
$test_query = "SELECT id, nama_mesin, deskripsi, stok, gambar FROM produk LIMIT 1";
$result = $conn->query($test_query);
if ($result) {
    $row = $result->fetch_assoc();
    echo "<pre>";
    print_r($row);
    echo "</pre>";
    
    // Check for price columns
    echo "<h3>Testing Price Columns:</h3>";
    
    $price_tests = [
        "harga_sewa_per_hari" => "SELECT harga_sewa_per_hari FROM produk LIMIT 1",
        "harga_sewa" => "SELECT harga_sewa FROM produk LIMIT 1"
    ];
    
    foreach ($price_tests as $column => $query) {
        $result = $conn->query($query);
        if ($result) {
            $value = $result->fetch_row()[0];
            echo "<p><strong>$column:</strong> EXISTS (value: $value)</p>";
        } else {
            echo "<p><strong>$column:</strong> NOT FOUND</p>";
        }
    }
} else {
    echo "Error: " . $conn->error;
}

$conn->close();
?>
