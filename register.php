<?php
session_start();

// Koneksi ke database
$conn = new mysqli("localhost", "root", "", "dbklik");

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Proses pendaftaran ketika form dikirim
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    // Cek apakah username sudah ada di database
    $sql = "SELECT * FROM login WHERE username = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Error prepare: " . $conn->error);
    }
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<script>alert('Username sudah digunakan!');</script>";
    } else {
        // Validasi role
        if ($role == '-') {
            echo "<script>alert('Silakan pilih role!');</script>";
        } else {
            // Enkripsi password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Simpan data admin atau user baru ke database
            $sql = "INSERT INTO login (username, password, role) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                die("Error prepare: " . $conn->error);
            }
            $stmt->bind_param("sss", $username, $hashed_password, $role);

            if ($stmt->execute()) {
                echo "<script>alert('Pendaftaran berhasil!'); window.location.href='login.php';</script>";
            } else {
                echo "<script>alert('Gagal mendaftar!');</script>";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Register</title>
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
        .register {
            background-color: #242c37;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 0 25px #000;
            text-align: center;
            width: 300px;
        }
        h2 {
            color: #fff;
            font-weight: 100;
            margin-bottom: 1.5rem;
            font-size: 2.75rem;
        }
        input, select {
            width: 100%;
            padding: 0.5rem;
            margin: 1rem 0;
            border-radius: 999px;
            border: 1px solid #242c37;
            background-color: transparent;
            color: #fff;
            text-align: center;
        }
        input[type="submit"] {
            background-image: linear-gradient(160deg, #8ceabb 0%, #378f7b 100%);
            color: #fff;
            cursor: pointer;
            border: none;
            font-size: 1rem;
            padding: 0.75rem;
            transition: background-color 0.3s ease;
        }
        input[type="submit"]:hover {
            background-image: linear-gradient(160deg, #78d4a3 0%, #2d6b5f 100%);
        }
        input::placeholder {
            color: #7e8ba3;
        }
        p {
            color: #fff;
        }
        a {
            color: #8ceabb;
        }
        select option {
            background-color: #242c37;
            color: #fff;
        }
    </style>
</head>
<body>
    <div class="register">
        <h2>Register</h2>
        <form action="register.php" method="POST">
            <div class="form__field">
                <input type="text" name="username" placeholder="Username" required>
            </div>
            <div class="form__field">
                <input type="password" name="password" placeholder="Password" required>
            </div>
            <div class="form__field">
                <label for="role" style="color: white;">Pilih Role</label>
                <select name="role" required>
                    <option value="-">-</option>
                    <option value="admin">Admin</option>
                    <option value="user">User</option>
                </select>
            </div>
            <div class="form__field">
                <input type="submit" value="Register">
            </div>
        </form>
        <p>Already have an account? <a href="login.php">Login</a></p>
    </div>
</body>
</html>
