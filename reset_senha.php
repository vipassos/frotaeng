<?php
// reset_senha.php
require 'db.php';

$usuario = 'admin';
$senha_nova = 'admin'; // <--- A senha será esta

// Gera um hash novo e válido usando o algoritmo padrão do seu servidor
$hash = password_hash($senha_nova, PASSWORD_DEFAULT);

try {
    // 1. Tenta atualizar se o usuário já existir
    $stmt = $pdo->prepare("UPDATE usuarios SET senha = ? WHERE usuario = ?");
    $stmt->execute([$hash, $usuario]);
    
    // Verifica se alterou alguma linha
    if ($stmt->rowCount() > 0) {
        echo "<h1>Sucesso!</h1>";
        echo "Senha do usuário <b>admin</b> redefinida para: <b>admin</b><br>";
    } else {
        // 2. Se não alterou nada, talvez o usuário não exista. Vamos criar.
        $stmtInsert = $pdo->prepare("INSERT INTO usuarios (usuario, senha) VALUES (?, ?)");
        $stmtInsert->execute([$usuario, $hash]);
        echo "<h1>Sucesso!</h1>";
        echo "Usuário <b>admin</b> criado com a senha: <b>admin</b><br>";
    }

    echo "<br>Hash gerado no banco: " . $hash;
    echo "<br><br><a href='login.php'>Clique aqui para fazer Login</a>";

} catch (PDOException $e) {
    echo "<h1>Erro</h1>";
    echo "Erro ao conectar ou gravar no banco: " . $e->getMessage();
    echo "<br>Verifique se o arquivo db.php está com a senha correta do banco.";
}
?>