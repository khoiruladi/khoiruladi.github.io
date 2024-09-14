<?php
session_start();

// Cek apakah pengguna sudah login dan memiliki role user
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit;
}

$username = $_SESSION['username'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <style>
        body {
            background-color: #354152;
            color: #7e8ba3;
            font-family: Helvetica Neue, sans-serif;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .dashboard-container {
            background-color: #242c37;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 0 25px #000;
            text-align: center;
            width: 300px;
        }
        h1 {
            color: #fff;
            font-weight: 100;
            margin-bottom: 1.5rem;
            font-size: 2.75rem;
        }
        label {
            color: #fff;
            display: block;
            margin-bottom: 0.5rem;
        }
        input {
            width: calc(100% - 1rem);
            padding: 0.5rem;
            margin: 1rem 0;
            border-radius: 5px;
            border: 1px solid #8ceabb;
            background-color: #2b2e34;
            color: #fff;
            text-align: center;
        }
        button {
            background-image: linear-gradient(160deg, #8ceabb 0%, #378f7b 100%);
            color: #fff;
            cursor: pointer;
            padding: 0.5rem;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
        }
        p {
            color: #fff;
        }
        #message {
            margin-top: 1rem;
            color: #ff6b6b; /* Warna merah untuk pesan error */
        }

        .logout-button {
            background-color: #e74c3c;
            border: none;
            color: #fff;
            cursor: pointer;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            font-size: 1rem;
            text-decoration: none;
            display: inline-block;
            transition: background-color 0.3s ease;
            text-align: center;
            margin-top: 1rem; /* Tambahkan jarak atas jika diperlukan */
        }
        .logout-button:hover {
            background-color: #c0392b;
        }
        .logout-button:active {
            background-color: #e74c3c;
            transform: scale(0.98);
        }
    </style>
</head>
<body>

<script src="https://code.jquery.com/jquery-3.7.1.slim.min.js" integrity="sha256-kmHvs0B+OpCW5GVHUNjv9rOmY0IvSIRcf7zGUDTDQM8=" crossorigin="anonymous"></script>
    <div class="dashboard-container">
        <h1>Selamat Datang, <?php echo htmlspecialchars($username); ?>!</h1>
        <form id="roomForm">
            <label for="roomCode">Masukkan Kode Room:</label>
            <input type="text" id="roomCode" name="roomCode" required>
            <br>
            <button type="submit">Masuk</button>
        </form>
        <p id="message"></p>
        <p><a href="logout.php" class="logout-button">Logout</a></p>
    </div>

    <script>
        // Fungsi yang menangani submit form
        document.getElementById('roomForm').addEventListener('submit', function(event) {
            event.preventDefault(); // Mencegah form submit standar

            const roomCode = document.getElementById('roomCode').value;

            // Kirim kode room ke server menggunakan AJAX
            const xhr = new XMLHttpRequest();
            xhr.open("POST", "validate_room.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onload = function() {
                const response = JSON.parse(this.responseText);
                if (response.success) {
                    document.getElementById('message').style.color = '#8ceabb'; // Ubah pesan jadi hijau jika sukses
                    document.getElementById('message').innerText = 'Berhasil menginputkan kode';

                    // Redirect ke halaman user_activity
                    window.location.href = `user_activity.php?username=<?php echo htmlspecialchars($username); ?>&roomCode=${roomCode}`;
                } else {
                    document.getElementById('message').style.color = '#ff6b6b'; // Warna merah jika gagal
                    document.getElementById('message').innerText = response.message;
                }
            };
            xhr.send(`roomCode=${roomCode}&username=<?php echo htmlspecialchars($username); ?>`); // Kirim kode room dan username
        });
    </script>
</body>
</html>
