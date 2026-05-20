<?php
if (isset($_GET['file'])) {
    // Ambil hanya nama filenya saja, cegah manipulasi folder menggunakan basename()
    $file = basename($_GET['file']); 
    $filepath = "uploads/" . $file;

    // Pastikan file benar-benar ada di folder uploads dan bukan folder lain
    if (file_exists($filepath) && is_file($filepath)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $file . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filepath));
        
        // Bersihkan buffer output server sebelum membaca file
        flush(); 
        readfile($filepath);
        exit;
    } else {
        die("Maaf, file tidak ditemukan atau akses ditolak.");
    }
}
?>