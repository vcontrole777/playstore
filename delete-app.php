<?php
session_start();
require_once 'config.php';

// Verificar se está logado
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: auth.php');
    exit;
}

// Verificar se o ID foi fornecido
if (!isset($_GET['id'])) {
    header('Location: admin.php');
    exit;
}

$id = $_GET['id'];

try {
    $conn = getConnection();
    $query = "DELETE FROM apps WHERE id = :id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    
    header('Location: admin.php?deleted=1');
} catch (Exception $e) {
    header('Location: admin.php?error=1');
}
exit;
?>