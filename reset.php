<?php
session_start();
include 'db_connection.php'; // Koneksi ke database

header('Content-Type: application/json');

// Cek apakah form untuk reset tabel user_activity sudah dikirim
    // Ambil id room berdasarkan room_code dari session
    $stmt = $conn->prepare("SELECT id FROM rooms WHERE room_code = ?");
    $stmt->bind_param("s", $_SESSION['room_code']);
    $stmt->execute();
    
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Jika room ditemukan, simpan informasinya
        $room = $result->fetch_assoc();
    }
    $stmt->close();
    var_dump($room['id']);

    // Update status 'is_deleted' untuk user dengan id_room terkait
    $stmt = $conn->prepare("DELETE FROM history WHERE id_room = ? ");
    $stmt->bind_param("s", $room['id']);
    $status = null;
    if ($stmt->execute()){
        $status = true;
    }
    $stmt->close();

    // Mengatur ulang nomor urut pada kolom `No` untuk data yang tidak dihapus
    $reset_query = "SET @num := 0; 
                    UPDATE history SET No = (@num := @num + 1) 
                    WHERE is_deleted = 0 
                    ORDER BY timestamp ASC";
    $conn->query($reset_query);

echo json_encode(['succes' => $status]);
?>
