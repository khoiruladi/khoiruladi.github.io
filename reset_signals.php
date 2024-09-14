<?php
include 'db_connection.php';

// Hapus semua data dari tabel user_activity
$stmt = $conn->prepare("DELETE FROM user_activity");
$stmt->execute();
$stmt->close();

// Mengatur ulang nomor urut pada kolom `No`
$reset_query = "SET @num := 0; UPDATE user_activity SET No = (@num := @num + 1) ORDER BY timestamp ASC";
$conn->query($reset_query);

// Reset auto-increment ke nilai awal (1)
$reset_auto_increment = "ALTER TABLE user_activity AUTO_INCREMENT = 1";
$conn->query($reset_auto_increment);

// Tutup koneksi
$conn->close();
?>
