<?php
session_start();
include 'config.php';

// Redirect ke login jika user belum login
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Inisialisasi variabel
$title = $body = "";
$title_err = $body_err = $image_err = "";

// Proses form submit
if($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validasi title
    if(empty(trim($_POST["title"]))) {
        $title_err = "Judul wajib diisi.";
    } else {
        $title = trim($_POST["title"]);
    }

    // Validasi body
    if(empty(trim($_POST["body"]))) {
        $body_err = "Isi pertanyaan wajib diisi.";
    } else {
        $body = trim($_POST["body"]);
    }

    // Upload gambar (opsional)
    $image_path = NULL;
    if(isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
        $allowed_ext = ['jpg','jpeg','png','gif'];
        $file_name = $_FILES["image"]["name"];
        $file_tmp = $_FILES["image"]["tmp_name"];
        $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        if(in_array($ext, $allowed_ext)) {
            $new_name = "uploads/".uniqid().".".$ext;
            if(!move_uploaded_file($file_tmp, $new_name)) {
                $image_err = "Gagal upload gambar.";
            } else {
                $image_path = $new_name;
            }
        } else {
            $image_err = "Format gambar harus jpg, jpeg, png, atau gif.";
        }
    }

    // Insert ke database jika tidak ada error
    if(empty($title_err) && empty($body_err) && empty($image_err)) {
        $sql = "INSERT INTO questions (user_id, title, body, image) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isss", $_SESSION['user_id'], $title, $body, $image_path);
        if($stmt->execute()) {
            echo "<script>alert('Pertanyaan berhasil dibuat!'); window.location='index.php';</script>";
        } else {
            echo "Terjadi kesalahan: ".$conn->error;
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Ask Question - ThinkQuest</title>
    <style>
        body { font-family: Arial, sans-serif; background:#f0f2f5; display:flex; justify-content:center; align-items:flex-start; padding-top:50px; }
        .container { background:#fff; padding:30px; border-radius:10px; box-shadow:0 0 10px rgba(0,0,0,0.1); width:500px; }
        h2 { text-align:center; margin-bottom:20px; color:#333; }
        input[type=text], textarea { width:96%; padding:10px; margin:5px 0 15px 0; border:1px solid #ccc; border-radius:5px; }
        input[type=file] { margin-bottom:15px; }
        input[type=submit] { width: 100%; padding:10px; background:#28a745; color:#fff; border:none; border-radius:5px; cursor:pointer; font-size:16px; }
        input[type=submit]:hover { background:#218838; }
        .error { color:red; font-size:14px; margin-top:-10px; margin-bottom:10px; }
        a { text-decoration:none; color:#007bff; }
        a:hover { text-decoration:underline; }
    </style>
</head>
<body>
<div class="container">
    <h2>Ask a Question</h2>
    <form action="" method="post" enctype="multipart/form-data">
        <label>Judul</label>
        <input type="text" name="title" value="<?= htmlspecialchars($title) ?>">
        <div class="error"><?= $title_err ?></div>

        <label>Isi Pertanyaan</label>
        <textarea name="body" rows="6"><?= htmlspecialchars($body) ?></textarea>
        <div class="error"><?= $body_err ?></div>

        <label>Gambar (opsional)</label>
        <input type="file" name="image">
        <div class="error"><?= $image_err ?></div>

        <input type="submit" value="Submit Question">
    </form>
    <p style="text-align:center;"><a href="index.php">Kembali ke Home</a></p>
</div>
</body>
</html>
