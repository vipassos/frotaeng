<?php
require 'auth.php';
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $senha_atual = $_POST['senha_atual'] ?? '';
    $nova_senha = $_POST['nova_senha'] ?? '';
    $confirma_senha = $_POST['confirma_senha'] ?? '';
    $usuario = $_SESSION['usuario'];

    if (empty($senha_atual) || empty($nova_senha) || empty($confirma_senha)) {
        header("Location: index.php?msg=Preencha todos os campos!&erro=1");
        exit;
    }

    if ($nova_senha !== $confirma_senha) {
        header("Location: index.php?msg=Senhas não conferem!&erro=1");
        exit;
    }

    try {
        // Busca a senha atual no banco
        $stmt = $pdo->prepare("SELECT senha FROM usuarios WHERE usuario = ?");
        $stmt->execute([$usuario]);
        $user = $stmt->fetch();

        if ($user && password_verify($senha_atual, $user['senha'])) {
            $hash = password_hash($nova_senha, PASSWORD_DEFAULT);
            $stmtUpdate = $pdo->prepare("UPDATE usuarios SET senha = ? WHERE usuario = ?");
            $stmtUpdate->execute([$hash, $usuario]);
            header("Location: index.php?msg=Senha alterada com sucesso!");
        } else {
            header("Location: index.php?msg=Senha atual incorreta!&erro=1");
        }
    } catch (PDOException $e) {
        // Em produção, não mostraria o erro detalhado, mas para debug sim
        header("Location: index.php?msg=Erro no banco de dados!&erro=1");
    }
} else {
    header("Location: index.php");
    exit;
}
?>