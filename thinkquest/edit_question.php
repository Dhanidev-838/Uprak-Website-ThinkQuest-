<?php
session_start();
include 'config.php';

// Cek login
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Cek apakah ada id pertanyaan di URL
if(!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$question_id = intval($_GET['id']);

// Ambil data pertanyaan
$sql = "SELECT * FROM questions WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $question_id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows == 0) {
    echo "Pertanyaan tidak ditemukan atau Anda tidak memiliki hak untuk mengedit.";
    exit;
}

$question = $result->fetch_assoc();
$stmt->close();

$title = $question['title'];
$body = $question['body'];
$old_image = $question['image'];
$title_err = $body_err = $image_err = "";

// Proses form submit
if($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title']);
    $body = trim($_POST['body']);

    if(empty($title)) $title_err = "Judul wajib diisi.";
    if(empty($body)) $body_err = "Isi pertanyaan wajib diisi.";

    // Upload gambar baru (opsional)
    $image_path = $old_image;
    if(isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
        $allowed_ext = ['jpg','jpeg','png','gif'];
        $file_name = $_FILES["image"]["name"];
        $file_tmp = $_FILES["image"]["tmp_name"];
        $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        if(in_array($ext, $allowed_ext)) {
            $new_name = "uploads/".uniqid().".".$ext;
            if(move_uploaded_file($file_tmp, $new_name)) {
                // hapus gambar lama jika ada
                if($old_image && file_exists($old_image)) unlink($old_image);
                $image_path = $new_name;
            } else {
                $image_err = "Gagal upload gambar.";
            }
        } else {
            $image_err = "Format gambar harus jpg, jpeg, png, atau gif.";
        }
    }

    // Update ke database jika tidak ada error
    if(empty($title_err) && empty($body_err) && empty($image_err)) {
        $sql = "UPDATE questions SET title = ?, body = ?, image = ? WHERE id = ? AND user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssii", $title, $body, $image_path, $question_id, $_SESSION['user_id']);
        if($stmt->execute()) {
            echo "<script>alert('Pertanyaan berhasil diupdate!'); window.location='question.php?id=".$question_id."';</script>";
            exit;
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
    <title>Edit Question - ThinkQuest</title>
    <style>
        body { font-family: Arial, sans-serif; background:#f0f2f5; display:flex; justify-content:center; align-items:flex-start; padding-top:50px; }
        .container { background:#fff; padding:30px; border-radius:10px; box-shadow:0 0 10px rgba(0,0,0,0.1); width:500px; }
        h2 { text-align:center; margin-bottom:20px; color:#333; }
        input[type=text], textarea { width:100%; padding:10px; margin:5px 0 15px 0; border:1px solid #ccc; border-radius:5px; }
        input[type=file] { margin-bottom:15px; }
        input[type=submit] { width:100%; padding:10px; background:#007bff; color:#fff; border:none; border-radius:5px; cursor:pointer; font-size:16px; }
        input[type=submit]:hover { background:#0056b3; }
        .error { color:red; font-size:14px; margin-top:-10px; margin-bottom:10px; }
        a { text-decoration:none; color:#007bff; }
        a:hover { text-decoration:underline; }
        img { max-width:100%; margin:10px 0; border-radius:5px; }
    </style>
</head>
<body>
<div class="container">
    <h2>Edit Pertanyaan</h2>
    <form action="" method="post" enctype="multipart/form-data">
        <label>Judul</label>
        <input type="text" name="title" value="<?= htmlspecialchars($title) ?>">
        <div class="error"><?= $title_err ?></div>

        <label>Isi Pertanyaan</label>
        <textarea name="body" rows="6"><?= htmlspecialchars($body) ?></textarea>
        <div class="error"><?= $body_err ?></div>

        <label>Gambar (opsional)</label>
        <?php if($old_image): ?>
            <img src="<?= htmlspecialchars($old_image) ?>" alt="old image">
        <?php endif; ?>
        <input type="file" name="image">
        <div class="error"><?= $image_err ?></div>

        <input type="submit" value="Update Question">
    </form>
    <p style="text-align:center;"><a href="question.php?id=<?= $question_id ?>">Kembali ke Pertanyaan</a></p>
</div>
</body>
</html>
