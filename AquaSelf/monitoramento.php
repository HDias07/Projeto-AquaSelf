<?php
require_once __DIR__ . "/conexao.php";

$mensagem = "";
$tipoMensagem = "";
$modoEdicao = false;
$idEdicao = 0;

$dadosMonitoramento = [
    "local_coleta" => "",
    "temperatura_agua" => "",
    "salinidade" => "",
    "ph_agua" => "",
    "nivel_poluicao" => "",
    "data_coleta" => "",
];

if ($mensagemConexao === "" && isset($_GET["editar"])) {
    $idEdicao = (int) $_GET["editar"];

    if ($idEdicao > 0) {
        $modoEdicao = true;
        $sqlEdicao = "SELECT id, local_coleta, temperatura_agua, salinidade, ph_agua, nivel_poluicao, data_coleta
            FROM monitoramento_ambiental
            WHERE id = ?";
        $comandoEdicao = $conexao->prepare($sqlEdicao);

        if ($comandoEdicao) {
            $comandoEdicao->bind_param("i", $idEdicao);
            $comandoEdicao->execute();
            $resultadoEdicao = $comandoEdicao->get_result();
            $registroEdicao = $resultadoEdicao ? $resultadoEdicao->fetch_assoc() : null;

            if ($registroEdicao) {
                $dadosMonitoramento = [
                    "local_coleta" => $registroEdicao["local_coleta"],
                    "temperatura_agua" => $registroEdicao["temperatura_agua"],
                    "salinidade" => $registroEdicao["salinidade"],
                    "ph_agua" => $registroEdicao["ph_agua"],
                    "nivel_poluicao" => $registroEdicao["nivel_poluicao"],
                    "data_coleta" => $registroEdicao["data_coleta"],
                ];
            } else {
                $modoEdicao = false;
                $idEdicao = 0;
                $mensagem = "O registro ambiental selecionado para edicao nao foi encontrado.";
                $tipoMensagem = "erro";
            }

            $comandoEdicao->close();
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $idEdicao = (int) ($_POST["id"] ?? 0);
    $modoEdicao = $idEdicao > 0;

    foreach ($dadosMonitoramento as $campo => $valor) {
        $dadosMonitoramento[$campo] = trim($_POST[$campo] ?? "");
    }

    if (in_array("", $dadosMonitoramento, true)) {
        $mensagem = "Preencha todos os campos do monitoramento ambiental.";
        $tipoMensagem = "erro";
    } elseif ($mensagemConexao !== "") {
        $mensagem = $mensagemConexao;
        $tipoMensagem = "erro";
    } else {
        if ($modoEdicao) {
            $sql = "UPDATE monitoramento_ambiental
                SET local_coleta = ?, temperatura_agua = ?, salinidade = ?, ph_agua = ?, nivel_poluicao = ?, data_coleta = ?
                WHERE id = ?";
        } else {
            $sql = "INSERT INTO monitoramento_ambiental
                (local_coleta, temperatura_agua, salinidade, ph_agua, nivel_poluicao, data_coleta)
                VALUES (?, ?, ?, ?, ?, ?)";
        }

        $comando = $conexao->prepare($sql);

        if ($comando) {
            if ($modoEdicao) {
                $comando->bind_param(
                    "sddsssi",
                    $dadosMonitoramento["local_coleta"],
                    $dadosMonitoramento["temperatura_agua"],
                    $dadosMonitoramento["salinidade"],
                    $dadosMonitoramento["ph_agua"],
                    $dadosMonitoramento["nivel_poluicao"],
                    $dadosMonitoramento["data_coleta"],
                    $idEdicao
                );
            } else {
                $comando->bind_param(
                    "sddsss",
                    $dadosMonitoramento["local_coleta"],
                    $dadosMonitoramento["temperatura_agua"],
                    $dadosMonitoramento["salinidade"],
                    $dadosMonitoramento["ph_agua"],
                    $dadosMonitoramento["nivel_poluicao"],
                    $dadosMonitoramento["data_coleta"]
                );
            }

            if ($comando->execute()) {
                $mensagem = $modoEdicao
                    ? "Registro ambiental atualizado com sucesso."
                    : "Registro ambiental salvo com sucesso.";
                $tipoMensagem = "sucesso";
                $modoEdicao = false;
                $idEdicao = 0;

                foreach ($dadosMonitoramento as $campo => $valor) {
                    $dadosMonitoramento[$campo] = "";
                }
            } else {
                $mensagem = $modoEdicao
                    ? "Nao foi possivel atualizar o registro ambiental."
                    : "Nao foi possivel salvar o registro ambiental.";
                $tipoMensagem = "erro";
            }

            $comando->close();
        } else {
            $mensagem = "Nao foi possivel preparar o comando SQL do monitoramento.";
            $tipoMensagem = "erro";
        }
    }
}

$registros = [];

if ($mensagemConexao === "") {
    $resultado = $conexao->query("SELECT id, local_coleta, temperatura_agua, salinidade, ph_agua, nivel_poluicao, data_coleta
        FROM monitoramento_ambiental
        ORDER BY data_coleta DESC, id DESC");

    if ($resultado instanceof mysqli_result) {
        while ($linha = $resultado->fetch_assoc()) {
            $registros[] = $linha;
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
    <title>AquaSelf | Monitoramento Ambiental</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header class="subpagina topo-monitoramento">
        <nav class="menu">
            <a class="logo" href="index.php">AquaSelf</a>
            <div class="menu-links">
                <a href="index.php">Inicio</a>
                <a href="animais.php">Animais marinhos</a>
                <a href="monitoramento.php">Monitoramento</a>
            </div>
        </nav>

        <section class="subpagina-intro">
            <span class="etiqueta">Controle ambiental marinho</span>
            <h1>Monitoramento do ambiente marinho</h1>
            <p>
                Registre as condicoes ambientais das areas monitoradas e acompanhe os indicadores
                mais recentes de qualidade da agua.
            </p>
        </section>
    </header>

    <main class="container pagina-dupla">
        <section class="card">
            <h2><?php echo $modoEdicao ? "Editar registro ambiental" : "Novo registro ambiental"; ?></h2>
            <p>
                <?php echo $modoEdicao
                    ? "Atualize os dados da coleta selecionada e salve as alteracoes."
                    : "Informe os dados coletados no mar para acompanhar a qualidade do ambiente."; ?>
            </p>

            <?php if ($mensagem !== ""): ?>
                <p class="feedback <?php echo htmlspecialchars($tipoMensagem); ?>">
                    <?php echo htmlspecialchars($mensagem); ?>
                </p>
            <?php endif; ?>

            <form action="monitoramento.php" method="POST" class="formulario">
                <input type="hidden" name="id" value="<?php echo $idEdicao; ?>">

                <label for="local_coleta">Local da coleta</label>
                <input type="text" id="local_coleta" name="local_coleta" value="<?php echo htmlspecialchars($dadosMonitoramento["local_coleta"]); ?>" required>

                <label for="temperatura_agua">Temperatura da agua (C)</label>
                <input type="number" step="0.1" id="temperatura_agua" name="temperatura_agua" value="<?php echo htmlspecialchars($dadosMonitoramento["temperatura_agua"]); ?>" required>

                <label for="salinidade">Salinidade</label>
                <input type="number" step="0.1" id="salinidade" name="salinidade" value="<?php echo htmlspecialchars($dadosMonitoramento["salinidade"]); ?>" required>

                <label for="ph_agua">pH da agua</label>
                <input type="number" step="0.1" id="ph_agua" name="ph_agua" value="<?php echo htmlspecialchars($dadosMonitoramento["ph_agua"]); ?>" required>

                <label for="nivel_poluicao">Nivel de poluicao</label>
                <select id="nivel_poluicao" name="nivel_poluicao" required>
                    <option value="">Selecione</option>
                    <option value="Baixo" <?php echo $dadosMonitoramento["nivel_poluicao"] === "Baixo" ? "selected" : ""; ?>>Baixo</option>
                    <option value="Moderado" <?php echo $dadosMonitoramento["nivel_poluicao"] === "Moderado" ? "selected" : ""; ?>>Moderado</option>
                    <option value="Alto" <?php echo $dadosMonitoramento["nivel_poluicao"] === "Alto" ? "selected" : ""; ?>>Alto</option>
                </select>

                <label for="data_coleta">Data da coleta</label>
                <input type="date" id="data_coleta" name="data_coleta" value="<?php echo htmlspecialchars($dadosMonitoramento["data_coleta"]); ?>" required>

                <div class="acoes-formulario">
                    <button type="submit" class="botao botao-principal botao-formulario">
                        <?php echo $modoEdicao ? "Atualizar monitoramento" : "Salvar monitoramento"; ?>
                    </button>
                    <?php if ($modoEdicao): ?>
                        <a href="monitoramento.php" class="botao botao-secundario botao-formulario">Cancelar edicao</a>
                    <?php endif; ?>
                </div>
            </form>
        </section>

        <section class="card">
            <h2>Historico ambiental</h2>
            <p>Use esta tabela para comparar os dados coletados nas diferentes regioes.</p>

            <?php if ($mensagemConexao !== ""): ?>
                <p class="feedback erro"><?php echo htmlspecialchars($mensagemConexao); ?></p>
            <?php elseif (count($registros) === 0): ?>
                <p class="vazio">Nenhum registro ambiental foi salvo ainda.</p>
            <?php else: ?>
                <div class="tabela-responsiva">
                    <table>
                        <thead>
                            <tr>
                                <th>Local</th>
                                <th>Temperatura</th>
                                <th>Salinidade</th>
                                <th>pH</th>
                                <th>Poluicao</th>
                                <th>Data</th>
                                <th>Acoes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($registros as $registro): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($registro["local_coleta"]); ?></td>
                                    <td><?php echo htmlspecialchars($registro["temperatura_agua"]); ?> C</td>
                                    <td><?php echo htmlspecialchars($registro["salinidade"]); ?></td>
                                    <td><?php echo htmlspecialchars($registro["ph_agua"]); ?></td>
                                    <td><?php echo htmlspecialchars($registro["nivel_poluicao"]); ?></td>
                                    <td><?php echo htmlspecialchars($registro["data_coleta"]); ?></td>
                                    <td>
                                        <a class="botao-tabela" href="monitoramento.php?editar=<?php echo (int) $registro["id"]; ?>">Editar</a>
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
