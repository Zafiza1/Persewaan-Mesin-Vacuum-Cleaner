<?php
require_once 'config/database.php';

$query = "UPDATE produk SET gambar = CONCAT('products/', gambar)";
if ($conn->query($query) === TRUE) {
    echo "Product images updated successfully. All image paths now include 'products/' prefix.";
} else {
    echo "Error updating images: " . $conn->error;
}
$conn->close();
?>
