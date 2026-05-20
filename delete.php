<?php
$file = isset($_POST['file']) ? basename($_POST['file']) : '';
$uploadDir = 'uploads/';
$filePath = $uploadDir . $file;

$allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
$ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));

if ($file && in_array($ext, $allowedExtensions) && file_exists($filePath)) {
    if (unlink($filePath)) {
        echo json_encode(['status' => 'success', 'message' => "File '{$file}' berhasil dihapus."]);
    } else {
        echo json_encode(['status' => 'error', 'message' => "Gagal menghapus file '{$file}'."]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => "File tidak ditemukan."]);
}
?>