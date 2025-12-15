<?php
include 'config.php';

// Inisialisasi variabel
$name = $email = $password = $bio = "";
$name_err = $email_err = $password_err = $avatar_err = "";

// Proses saat form disubmit
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Validasi nama
    if (empty(trim($_POST["name"]))) {
        $name_err = "Nama wajib diisi.";
    } else {
        $name = trim($_POST["name"]);
    }

    // Validasi email
    if (empty(trim($_POST["email"]))) {
        $email_err = "Email wajib diisi.";
    } else {
        // cek email sudah dipakai belum
        $email = trim($_POST["email"]);
        $sql = "SELECT id FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $email_err = "Email sudah terdaftar.";
        }
        $stmt->close();
    }

    // Validasi password
    if (empty(trim($_POST["password"]))) {
        $password_err = "Password wajib diisi.";
    } elseif (strlen(trim($_POST["password"])) < 6) {
        $password_err = "Password minimal 6 karakter.";
    } else {
        $password = password_hash(trim($_POST["password"]), PASSWORD_DEFAULT);
    }

    // Bio opsional
    $bio = trim($_POST["bio"]);

    // Upload avatar (opsional)
    $avatar_path = NULL;
    if (isset($_FILES["avatar"]) && $_FILES["avatar"]["error"] == 0) {
        $allowed_ext = ['jpg','jpeg','png','gif'];
        $file_name = $_FILES["avatar"]["name"];
        $file_tmp = $_FILES["avatar"]["tmp_name"];
        $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        if (in_array($ext, $allowed_ext)) {
            $new_name = uniqid() . "." . $ext;
            if (!move_uploaded_file($file_tmp, "uploads/" . $new_name)) {
                $avatar_err = "Gagal upload avatar.";
            } else {
                $avatar_path = "uploads/" . $new_name;
            }
        } else {
            $avatar_err = "Format avatar harus jpg, jpeg, png, atau gif.";
        }
    }

    // Jika tidak ada error, insert ke database
    if (empty($name_err) && empty($email_err) && empty($password_err) && empty($avatar_err)) {
        $sql = "INSERT INTO users (name, email, password, avatar, bio) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssss", $name, $email, $password, $avatar_path, $bio);
        if ($stmt->execute()) {
            echo "<script>alert('Registrasi berhasil! Silakan login.'); window.location='login.php';</script>";
        } else {
            echo "Terjadi kesalahan: " . $conn->error;
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Register - ThinkQuest</title>
    <style>
        body { font-family: Arial, sans-serif; background: #fff; display:flex; justify-content:center; align-items:center; height:100vh; }
        .container { background:#fff; padding:30px; border-radius:10px; box-shadow:0 0 10px rgba(0,0,0,0.1); width:400px; }
        h2 { text-align:center; margin-bottom:20px; color:#333; }
        input[type=text], input[type=email], input[type=password], textarea { width: 95%; padding:10px; margin:5px 0 15px 0; border:1px solid #ccc; border-radius:5px; }
        input[type=file] { margin-bottom:15px; }
        input[type=submit] { width:100%; padding:10px; background:#28a745; color:#fff; border:none; border-radius:5px; cursor:pointer; font-size:16px; }
        input[type=submit]:hover { background:#218838; }
        .error { color:red; font-size:14px; margin-top:-10px; margin-bottom:10px; }
        a { text-decoration:none; color:#007bff; }
        a:hover { text-decoration:underline; }
        .login-link {
            text-align: center;
            color: #28a745;
        }

        .login-link a {
            color: #28a745;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Register ThinkQuest</h2>
    <form action="" method="post" enctype="multipart/form-data">
        <label>Nama</label>
        <input type="text" name="name" value="<?= htmlspecialchars($name) ?>">
        <div class="error"><?= $name_err ?></div>

        <label>Email</label>
        <input type="email" name="email" value="<?= htmlspecialchars($email) ?>">
        <div class="error"><?= $email_err ?></div>

        <label>Password (password harus 6 karakter)</label>
        <input type="password" name="password">
        <div class="error"><?= $password_err ?></div>

        <label>Bio (opsional)</label>
        <textarea name="bio"><?= htmlspecialchars($bio) ?></textarea>

        <label>Avatar (Photo Profile)</label>
        <input type="file" name="avatar">
        <div class="error"><?= $avatar_err ?></div>

        <input type="submit" value="Register">
    </form>
    <p class="login-link">Sudah punya akun? <a href="login.php">Login di sini</a></p>
</div>
</body>
</html>
