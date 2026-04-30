<?php
require_once __DIR__ . "/conexao.php";

$mensagem = "";
$tipoMensagem = "";
$modoEdicao = false;
$idEdicao = 0;

$dadosAnimal = [
    "nome" => "",
    "especie" => "",
    "identificador" => "",
    "localizacao" => "",
    "estado_saude" => "",
    "data_registro" => "",
];

if ($mensagemConexao === "" && isset($_GET["editar"])) {
    $idEdicao = (int) $_GET["editar"];

    if ($idEdicao > 0) {
        $modoEdicao = true;
        $sqlEdicao = "SELECT id, nome, especie, identificador, localizacao, estado_saude, data_registro
            FROM animais_marinhos
            WHERE id = ?";
        $comandoEdicao = $conexao->prepare($sqlEdicao);

        if ($comandoEdicao) {
            $comandoEdicao->bind_param("i", $idEdicao);
            $comandoEdicao->execute();
            $resultadoEdicao = $comandoEdicao->get_result();
            $animalEdicao = $resultadoEdicao ? $resultadoEdicao->fetch_assoc() : null;

            if ($animalEdicao) {
                $dadosAnimal = [
                    "nome" => $animalEdicao["nome"],
                    "especie" => $animalEdicao["especie"],
                    "identificador" => $animalEdicao["identificador"],
                    "localizacao" => $animalEdicao["localizacao"],
                    "estado_saude" => $animalEdicao["estado_saude"],
                    "data_registro" => $animalEdicao["data_registro"],
                ];
            } else {
                $modoEdicao = false;
                $idEdicao = 0;
                $mensagem = "O animal selecionado para edicao nao foi encontrado.";
                $tipoMensagem = "erro";
            }

            $comandoEdicao->close();
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $acao = $_POST["acao"] ?? "salvar";
    $idEdicao = (int) ($_POST["id"] ?? 0);
    $modoEdicao = $idEdicao > 0;

    if ($mensagemConexao !== "") {
        $mensagem = $mensagemConexao;
        $tipoMensagem = "erro";
    } elseif ($acao === "excluir") {
        $idExcluir = (int) ($_POST["id_excluir"] ?? 0);

        if ($idExcluir <= 0) {
            $mensagem = "Nao foi possivel identificar o animal que seria excluido.";
            $tipoMensagem = "erro";
        } else {
            $sqlExcluir = "DELETE FROM animais_marinhos WHERE id = ?";
            $comandoExcluir = $conexao->prepare($sqlExcluir);

            if ($comandoExcluir) {
                $comandoExcluir->bind_param("i", $idExcluir);

                if ($comandoExcluir->execute()) {
                    $mensagem = "Animal marinho excluido com sucesso.";
                    $tipoMensagem = "sucesso";

                    if ($modoEdicao && $idEdicao === $idExcluir) {
                        $modoEdicao = false;
                        $idEdicao = 0;

                        foreach ($dadosAnimal as $campo => $valor) {
                            $dadosAnimal[$campo] = "";
                        }
                    }
                } else {
                    $mensagem = "Nao foi possivel excluir o animal selecionado.";
                    $tipoMensagem = "erro";
                }

                $comandoExcluir->close();
            } else {
                $mensagem = "Nao foi possivel preparar a exclusao do animal.";
                $tipoMensagem = "erro";
            }
        }
    } else {
        foreach ($dadosAnimal as $campo => $valor) {
            $dadosAnimal[$campo] = trim($_POST[$campo] ?? "");
        }

        if (in_array("", $dadosAnimal, true)) {
            $mensagem = "Preencha todos os campos antes de salvar o animal.";
            $tipoMensagem = "erro";
            goto fimProcessamentoAnimal;
        }

        if ($modoEdicao) {
            $sql = "UPDATE animais_marinhos
                    SET nome = ?, especie = ?, identificador = ?, localizacao = ?, estado_saude = ?, data_registro = ?
                    WHERE id = ?";
        } else {
            $sql = "INSERT INTO animais_marinhos (nome, especie, identificador, localizacao, estado_saude, data_registro)
                    VALUES (?, ?, ?, ?, ?, ?)";
        }

        $comando = $conexao->prepare($sql);

        if ($comando) {
            if ($modoEdicao) {
                $comando->bind_param(
                    "ssssssi",
                    $dadosAnimal["nome"],
                    $dadosAnimal["especie"],
                    $dadosAnimal["identificador"],
                    $dadosAnimal["localizacao"],
                    $dadosAnimal["estado_saude"],
                    $dadosAnimal["data_registro"],
                    $idEdicao
                );
            } else {
                $comando->bind_param(
                    "ssssss",
                    $dadosAnimal["nome"],
                    $dadosAnimal["especie"],
                    $dadosAnimal["identificador"],
                    $dadosAnimal["localizacao"],
                    $dadosAnimal["estado_saude"],
                    $dadosAnimal["data_registro"]
                );
            }

            if ($comando->execute()) {
                $mensagem = $modoEdicao
                    ? "Animal marinho atualizado com sucesso."
                    : "Animal marinho cadastrado com sucesso.";
                $tipoMensagem = "sucesso";
                $modoEdicao = false;
                $idEdicao = 0;

                foreach ($dadosAnimal as $campo => $valor) {
                    $dadosAnimal[$campo] = "";
                }
            } else {
                $mensagem = $modoEdicao
                    ? "Nao foi possivel atualizar o animal. Tente novamente."
                    : "Nao foi possivel salvar o animal. Tente novamente.";
                $tipoMensagem = "erro";
            }

            $comando->close();
        } else {
            $mensagem = "Nao foi possivel preparar o comando SQL para cadastrar o animal.";
            $tipoMensagem = "erro";
        }
    }
}
fimProcessamentoAnimal:

$animais = [];

if ($mensagemConexao === "") {
    $resultado = $conexao->query("SELECT id, nome, especie, identificador, localizacao, estado_saude, data_registro
        FROM animais_marinhos
        ORDER BY data_registro DESC, id DESC");

    if ($resultado instanceof mysqli_result) {
        while ($linha = $resultado->fetch_assoc()) {
            $animais[] = $linha;
        }

        $resultado->free();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AquaSelf | Animais Marinhos</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header class="subpagina topo-animais">
        <nav class="menu">
            <a class="logo" href="index.php">AquaSelf</a>
            <div class="menu-links">
                <a href="index.php">Inicio</a>
                <a href="animais.php">Animais marinhos</a>
                <a href="monitoramento.php">Monitoramento</a>
            </div>
        </nav>

        <section class="subpagina-intro">
            <span class="etiqueta">Base de rastreamento biologico</span>
            <h1>Rastreamento de animais marinhos</h1>
            <p>
                Registre os animais monitorados pelo AquaSelf e acompanhe informacoes essenciais
                para observacao, pesquisa e preservacao marinha.
            </p>
        </section>
    </header>

    <main class="container pagina-dupla">
        <section class="card">
            <h2><?php echo $modoEdicao ? "Editar animal monitorado" : "Novo animal monitorado"; ?></h2>
            <p>
                <?php echo $modoEdicao
                    ? "Atualize os dados do animal selecionado e salve as alteracoes."
                    : "Preencha todos os campos para adicionar um novo registro ao sistema."; ?>
            </p>

            <?php if ($mensagem !== ""): ?>
                <p class="feedback <?php echo htmlspecialchars($tipoMensagem); ?>">
                    <?php echo htmlspecialchars($mensagem); ?>
                </p>
            <?php endif; ?>

            <form action="animais.php" method="POST" class="formulario">
                <input type="hidden" name="id" value="<?php echo $idEdicao; ?>">

                <label for="nome">Nome do animal</label>
                <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($dadosAnimal["nome"]); ?>" required>

                <label for="especie">Especie</label>
                <input type="text" id="especie" name="especie" value="<?php echo htmlspecialchars($dadosAnimal["especie"]); ?>" required>

                <label for="identificador">Identificador</label>
                <input type="text" id="identificador" name="identificador" value="<?php echo htmlspecialchars($dadosAnimal["identificador"]); ?>" required>

                <label for="localizacao">Localizacao atual</label>
                <input type="text" id="localizacao" name="localizacao" value="<?php echo htmlspecialchars($dadosAnimal["localizacao"]); ?>" required>

                <label for="estado_saude">Estado de saude</label>
                <input type="text" id="estado_saude" name="estado_saude" value="<?php echo htmlspecialchars($dadosAnimal["estado_saude"]); ?>" required>

                <label for="data_registro">Data do registro</label>
                <input type="date" id="data_registro" name="data_registro" value="<?php echo htmlspecialchars($dadosAnimal["data_registro"]); ?>" required>

                <div class="acoes-formulario">
                    <button type="submit" class="botao botao-principal botao-formulario">
                        <?php echo $modoEdicao ? "Atualizar animal" : "Salvar animal"; ?>
                    </button>
                    <?php if ($modoEdicao): ?>
                        <a href="animais.php" class="botao botao-secundario botao-formulario">Cancelar edicao</a>
                    <?php endif; ?>
                </div>
            </form>
        </section>

        <section class="card">
            <h2>Animais cadastrados</h2>
            <p>Os registros mais recentes aparecem primeiro para facilitar o acompanhamento.</p>

            <?php if ($mensagemConexao !== ""): ?>
                <p class="feedback erro"><?php echo htmlspecialchars($mensagemConexao); ?></p>
            <?php elseif (count($animais) === 0): ?>
                <p class="vazio">Nenhum animal foi cadastrado ainda.</p>
            <?php else: ?>
                <div class="tabela-responsiva">
                    <table>
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Especie</th>
                                <th>Identificador</th>
                                <th>Localizacao</th>
                                <th>Saude</th>
                                <th>Data</th>
                                <th>Acoes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($animais as $animal): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($animal["nome"]); ?></td>
                                    <td><?php echo htmlspecialchars($animal["especie"]); ?></td>
                                    <td><?php echo htmlspecialchars($animal["identificador"]); ?></td>
                                    <td><?php echo htmlspecialchars($animal["localizacao"]); ?></td>
                                    <td><?php echo htmlspecialchars($animal["estado_saude"]); ?></td>
                                    <td><?php echo htmlspecialchars($animal["data_registro"]); ?></td>
                                    <td>
                                        <div class="acoes-tabela">
                                            <a class="botao-tabela" href="animais.php?editar=<?php echo (int) $animal["id"]; ?>">Editar</a>
                                            <form action="animais.php" method="POST" class="formulario-excluir" onsubmit="return confirm('Tem certeza que deseja excluir este animal?');">
                                                <input type="hidden" name="acao" value="excluir">
                                                <input type="hidden" name="id_excluir" value="<?php echo (int) $animal["id"]; ?>">
                                                <button type="submit" class="botao-tabela botao-perigo">Excluir</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>
