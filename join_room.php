<?php
include 'db_connection.php'; // Koneksi ke database

if (isset($_POST['roomCode']) && isset($_POST['username'])) {
    $roomCode = $_POST['roomCode'];
    $username = $_POST['username'];

    // Cek apakah kode room ada di database
    $stmt = $conn->prepare("SELECT room_code FROM rooms WHERE room_code = ?");
    $stmt->bind_param("s", $roomCode);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // Jika kode room valid, tambahkan user ke room
        $stmt = $conn->prepare("INSERT INTO room_users (room_code, id_user) VALUES (?, ?)");
        $stmt->bind_param("ss", $roomCode, $_SESSION['id_user']);
        $stmt->execute();
        $stmt->close();
        
        echo json_encode(['success' => true, 'message' => 'Berhasil bergabung ke room']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Kode room tidak valid']);
    }
    $stmt->close();
}
?>
