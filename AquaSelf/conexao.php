<?php
$servidor = "localhost";
$usuario = "root";
$senha = "";
$banco = "aquaself";

$conexao = null;

if (!class_exists("mysqli")) {
    $mensagemConexao = "A extensao mysqli nao esta ativa neste PHP. Use o PHP do XAMPP ou habilite a extensao mysqli no php.ini.";
} else {
    $conexao = @new mysqli($servidor, $usuario, $senha, $banco);

    if ($conexao->connect_error) {
        $mensagemConexao = "Nao foi possivel conectar ao banco de dados. Verifique se o MySQL esta ligado e se o banco 'aquaself' foi criado.";
    } else {
        $conexao->set_charset("utf8mb4");
        $mensagemConexao = "";
    }
}
?>
