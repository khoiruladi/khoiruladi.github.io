<?php
include 'db_connection.php'; // Koneksi ke database

// Query untuk mengambil data dari tabel history
$query = "SELECT username, timestamp FROM history ORDER BY timestamp ASC";
$result = $conn->query($query);

$signals = [];

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $signals[] = [
            'username' => $row['username'],
            'timestamp' => $row['timestamp']
        ];
    }
}

echo json_encode(['signals' => $signals]);

$conn->close();
?>
