<?php
session_start();
require_once 'config.php';

// Nova senha
$nova_senha = '1020304050';
$email = 'medina@gmail.com';

// Gerar hash correto
$hash = password_hash($nova_senha, PASSWORD_DEFAULT);

echo "<h1>Resetar Senha do Admin</h1>";
echo "<p>Gerando novo hash para a senha: <strong>$nova_senha</strong></p>";
echo "<p>Hash gerado: <code>$hash</code></p>";
echo "<hr>";

try {
    $conn = getConnection();
    
    // Atualizar senha no banco
    $query = "UPDATE users SET password = :password WHERE email = :email";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':password', $hash);
    $stmt->bindParam(':email', $email);
    
    if ($stmt->execute()) {
        echo "<p style='color: green; font-size: 20px;'><strong>? Senha atualizada com sucesso!</strong></p>";
        echo "<p>Agora você pode fazer login com:</p>";
        echo "<ul>";
        echo "<li><strong>Email:</strong> $email</li>";
        echo "<li><strong>Senha:</strong> $nova_senha</li>";
        echo "</ul>";
        echo "<hr>";
        echo "<p><a href='auth.php' style='background: blue; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Ir para Login</a></p>";
    } else {
        echo "<p style='color: red;'>Erro ao atualizar senha</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Erro: " . $e->getMessage() . "</p>";
}
<?