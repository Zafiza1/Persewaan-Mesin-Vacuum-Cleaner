<?php
require_once __DIR__ . '/config/database.php';

echo "<h2>Database Structure Check</h2>";

// Check produk table structure
echo "<h3>Produk Table Structure:</h3>";
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

// Check sample data
echo "<h3>Sample Produk Data:</h3>";
$result = $conn->query("SELECT * FROM produk LIMIT 3");
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

$conn->close();
?>
