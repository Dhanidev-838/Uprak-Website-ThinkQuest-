<?php
session_start();
include 'config.php';

// Cek login
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Cek apakah ada id pertanyaan
if(!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$question_id = intval($_GET['id']);

// Ambil data pertanyaan untuk cek kepemilikan dan ambil nama file gambar
$sql = "SELECT * FROM questions WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $question_id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows == 0) {
    echo "Pertanyaan tidak ditemukan atau Anda tidak memiliki hak untuk menghapus.";
    exit;
}

$question = $result->fetch_assoc();
$stmt->close();

// Hapus gambar pertanyaan jika ada
if($question['image'] && file_exists($question['image'])) {
    unlink($question['image']);
}

// Hapus pertanyaan dari database (jawaban otomatis ikut terhapus karena ON DELETE CASCADE)
$sql_delete = "DELETE FROM questions WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($sql_delete);
$stmt->bind_param("ii", $question_id, $_SESSION['user_id']);
if($stmt->execute()) {
    $stmt->close();
    header("Location: index.php");
    exit;
} else {
    echo "Terjadi kesalahan: ".$conn->error;
}
?>
