<?php
// FILE INI SENGAJA RENTAN UNTUK DEMO
$conn = new mysqli("localhost", "username_db", "password_db", "shop_db");

$id = $_GET['id']; // TIDAK ADA SANITIZE = RENTAN

$query = "SELECT * FROM users WHERE id = '$id'";
$result = $conn->query($query);

while($row = $result->fetch_assoc()) {
    echo "User: " . $row['username'] . "<br>";
    echo "Password: " . $row['password'] . "<br>";
    echo "Email: " . $row['email'] . "<br>";
}
?>