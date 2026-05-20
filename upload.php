<?php
// Cek apakah ini request untuk list file
if(isset($_GET['action']) && $_GET['action'] == 'list') {
    $target_dir = "uploads/";
    $files = [];
    
    if (is_dir($target_dir)) {
        $scannedFiles = scandir($target_dir);
        foreach ($scannedFiles as $file) {
            if ($file != '.' && $file != '..') {
                $filePath = $target_dir . $file;
                $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
                
                if (in_array($ext, $allowedExtensions)) {
                    $files[] = [
                        'name' => $file,
                        'size' => filesize($filePath),
                        'date' => date('Y-m-d H:i:s', filemtime($filePath))
                    ];
                }
            }
        }
    }
    
    if (count($files) == 0) {
        echo '<div class="no-files">Belum ada gambar yang diupload</div>';
    } else {
        foreach ($files as $file) {
            echo '<div class="file-item">
                    <div class="file-info">
                        <img src="uploads/' . htmlspecialchars($file['name']) . '" class="preview-img">
                        <span class="file-name">' . htmlspecialchars($file['name']) . '</span>
                        <span class="file-size">(' . formatFileSize($file['size']) . ')</span>
                        <div style="font-size: 11px; color: #999;">Tanggal: ' . $file['date'] . '</div>
                    </div>
                    <div>
                        <button class="btn-download" onclick="downloadFile(\'' . htmlspecialchars($file['name']) . '\')">Download</button>
                        <button class="btn-delete" onclick="deleteFile(\'' . htmlspecialchars($file['name']) . '\')">Hapus</button>
                    </div>
                  </div>';
        }
    }
    exit;
}

// Cek apakah ini request untuk download file
if(isset($_GET['action']) && $_GET['action'] == 'download') {
    $file = isset($_GET['file']) ? basename($_GET['file']) : '';
    $uploadDir = 'uploads/';
    $filePath = $uploadDir . $file;
    
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    
    if ($file && in_array($ext, $allowedExtensions) && file_exists($filePath)) {
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $file . '"');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit;
    } else {
        echo "File tidak ditemukan.";
        exit;
    }
}

function formatFileSize($bytes) {
    if ($bytes === 0) return '0 Bytes';
    $k = 1024;
    $sizes = ['Bytes', 'KB', 'MB', 'GB'];
    $i = floor(log($bytes) / log($k));
    return round($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
}

// Proses upload file
$target_dir = "uploads/";

if (!file_exists($target_dir)) {
    mkdir($target_dir, 0777, true);
}

$target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
$uploadOk = 1;
$fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

if(isset($_POST["submit"])) {
    $check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
    if($check === false) {
        $message = "Error: File harus berupa gambar!";
        $uploadOk = 0;
        $status = "error";
    } else {
        $uploadOk = 1;
    }
    
    if ($uploadOk && file_exists($target_file)) {
        $message = "Error: File dengan nama tersebut sudah ada.";
        $uploadOk = 0;
        $status = "error";
    }
    
    if ($uploadOk && $_FILES["fileToUpload"]["size"] > 500000) {
        $message = "Error: Ukuran gambar terlalu besar! Maksimal 500KB.";
        $uploadOk = 0;
        $status = "error";
    }
    
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
    if($uploadOk && !in_array($fileType, $allowedExtensions)) {
        $message = "Error: Hanya file gambar yang diperbolehkan! Format: JPG, JPEG, PNG, GIF";
        $uploadOk = 0;
        $status = "error";
    }
    
    if ($uploadOk) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $_FILES["fileToUpload"]["tmp_name"]);
        finfo_close($finfo);
        
        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if(!in_array($mimeType, $allowedMimeTypes)) {
            $message = "Error: File bukan gambar yang valid!";
            $uploadOk = 0;
            $status = "error";
        }
    }
    
    if ($uploadOk == 0) {
        $result = ['status' => $status, 'message' => $message];
    } else {
        if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
            $result = [
                'status' => 'success', 
                'message' => "Berhasil! Gambar '" . htmlspecialchars(basename($_FILES["fileToUpload"]["name"])) . "' telah diunggah."
            ];
        } else {
            $result = [
                'status' => 'error', 
                'message' => "Error: Gagal mengunggah gambar."
            ];
        }
    }
} else {
    $result = [
        'status' => 'error',
        'message' => "Error: Tidak ada file yang dipilih."
    ];
}

echo "<script>
    window.parent.postMessage({
        type: 'upload',
        status: '{$result['status']}',
        message: '{$result['message']}'
    }, '*');
    window.location.href = 'index.html';
</script>";
?>