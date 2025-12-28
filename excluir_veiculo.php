<?php
require 'auth.php';
require 'db.php';
$id = $_GET['id'];
$pdo->prepare("DELETE FROM veiculos WHERE id = ?")->execute([$id]);
header("Location: index.php?msg=Veiculo Excluido");
?>