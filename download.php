<?php
// Script para fazer download de APKs hospedados no servidor

if (!isset($_GET['file'])) {
    die('Arquivo não especificado');
}

$file = basename($_GET['file']); // Segurança: apenas nome do arquivo
$filePath = __DIR__ . '/uploads/' . $file;

// Verificar se arquivo existe
if (!file_exists($filePath)) {
    die('Arquivo não encontrado');
}

// Verificar se é APK
if (pathinfo($filePath, PATHINFO_EXTENSION) !== 'apk') {
    die('Tipo de arquivo inválido');
}

// Headers para download
header('Content-Type: application/vnd.android.package-archive');
header('Content-Disposition: attachment; filename="' . $file . '"');
header('Content-Length: ' . filesize($filePath));
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');

// Enviar arquivo
readfile($filePath);
exit;
?>