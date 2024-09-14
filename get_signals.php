<?php
session_start();
// Koneksi ke database
include 'db_connection.php';

// Ambil kode room dari session
$room_code = $_SESSION['room_code'];

// Langkah 1: Ambil data user yang telah bergabung ke room dari tabel `room_users`
$joined_users = [];
$stmt = $conn->prepare("
    SELECT login.username, room_users.joined_at 
    FROM room_users 
    JOIN login ON room_users.id_user = login.id
    JOIN rooms ON room_users.id_room = rooms.id
    WHERE rooms.room_code = ?
");
$stmt->bind_param("s", $room_code);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $joined_users[] = $row;
}
$stmt->close();

// Langkah 2: Ambil data user yang menekan tombol tercepat dari tabel `user_activity`
$signals = [];
$stmt = $conn->prepare("SELECT username, timestamp FROM history Join login on login.id=history.id_user Join rooms on rooms.id = history.id_room Where rooms.room_code = '$room_code' ORDER BY timestamp ASC");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $signals[] = $row;
}
$stmt->close();

// Langkah 3: Return hasil dalam bentuk JSON
echo json_encode([
    'joined_users' => $joined_users,
    'signals' => $signals
]);
?>
