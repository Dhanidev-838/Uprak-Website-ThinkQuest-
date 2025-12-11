<?php
session_start();
include 'config.php';

// Cek login
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Cek apakah ada id jawaban
if(!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$answer_id = intval($_GET['id']);

// Ambil jawaban untuk cek kepemilikan dan ambil nama file gambar serta question_id
$sql = "SELECT * FROM answers WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $answer_id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows == 0) {
    echo "Jawaban tidak ditemukan atau Anda tidak memiliki hak untuk menghapus.";
    exit;
}

$answer = $result->fetch_assoc();
$question_id = $answer['question_id'];
$old_image = $answer['image'];
$stmt->close();

// Hapus file gambar jawaban jika ada
if($old_image && file_exists($old_image)) {
    unlink($old_image);
}

// Hapus jawaban dari database
$sql_delete = "DELETE FROM answers WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($sql_delete);
$stmt->bind_param("ii", $answer_id, $_SESSION['user_id']);
if($stmt->execute()) {
    $stmt->close();
    header("Location: question.php?id=".$question_id);
    exit;
} else {
    echo "Terjadi kesalahan: ".$conn->error;
}
?>
