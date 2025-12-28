<?php
require 'auth.php';
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $marca = $_POST['marca'];
    $modelo = $_POST['modelo'];
    $placa = strtoupper($_POST['placa']);
    $ano = $_POST['ano'];
    $km = $_POST['km_atual'];

    try {
        $stmt = $pdo->prepare("INSERT INTO veiculos (marca, modelo, placa, ano, km_atual) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$marca, $modelo, $placa, $ano, $km]);
        header("Location: index.php?msg=Veiculo Cadastrado");
    } catch (PDOException $e) {
        die("Erro: " . $e->getMessage());
    }
}