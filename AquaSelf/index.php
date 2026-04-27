<?php
require_once __DIR__ . "/conexao.php";

$statusConexao = $mensagemConexao === ""
    ? "Conexao com o MySQL pronta para uso."
    : $mensagemConexao;

$classeStatus = $mensagemConexao === "" ? "status-ok" : "status-erro";
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
    <header class="hero">
        <nav class="menu">
            <a class="logo" href="index.php">AquaSelf</a>
            <div class="menu-links">
                <a href="index.php">Inicio</a>
                <a href="animais.php">Animais marinhos</a>
                <a href="monitoramento.php">Monitoramento</a>
            </div>
        </nav>

        <section class="hero-conteudo">
            <div class="hero-texto">
                <span class="etiqueta">Monitoramento marinho inteligente</span>
                <h1>Sistema de monitoramento ambiental e rastreamento de animais marinhos</h1>
                <p>
                    O AquaSelf centraliza o cadastro de animais marinhos, o acompanhamento de
                    localizacao e saude, e o registro de indicadores ambientais em um painel
                    moderno, pratico e organizado.
                </p>

                <div class="botoes">
                    <a class="botao botao-principal" href="animais.php">Cadastrar animais</a>
                    <a class="botao botao-secundario" href="monitoramento.php">Registrar ambiente</a>
                </div>
            </div>

            <aside class="hero-card">
                <h2>Objetivos do AquaSelf</h2>
                <ul>
                    <li>Monitorar a saude e a localizacao de animais marinhos.</li>
                    <li>Registrar temperatura, pH, salinidade e nivel de poluicao.</li>
                    <li>Reunir informacoes importantes para analise e tomada de decisao.</li>
                </ul>
            </aside>
        </section>
    </header>

    <main class="container">
        <section class="grid-info">
            <article class="card">
                <h2>Visao geral do sistema</h2>
                <p>
                    O sistema foi desenvolvido para acompanhar animais marinhos e condicoes
                    ambientais em diferentes regioes costeiras, reunindo registros operacionais em
                    um fluxo simples de consulta e atualizacao.
                </p>
            </article>

            <article class="card">
                <h2>Status do banco de dados</h2>
                <p class="status <?php echo htmlspecialchars($classeStatus); ?>">
                    <?php echo htmlspecialchars($statusConexao); ?>
                </p>
                <p>
                    Se a conexao falhar, abra o XAMPP ou WAMP, ligue o MySQL e execute o arquivo
                    <strong>aquaself.sql</strong> no phpMyAdmin.
                </p>
            </article>

            <article class="card">
                <h2>Recursos principais</h2>
                <ol class="lista-etapas">
                    <li>Cadastro detalhado de animais monitorados.</li>
                    <li>Registro de indicadores ambientais por coleta.</li>
                    <li>Consulta rapida dos dados mais recentes.</li>
                    <li>Painel visual com navegacao objetiva.</li>
                </ol>
            </article>
        </section>

        <section class="card destaque">
            <h2>Modulos do AquaSelf</h2>
            <div class="topicos">
                <div>
                    <h3>Rastreamento animal</h3>
                    <p>Cadastre especie, identificacao, localizacao atual e condicao de saude.</p>
                </div>
                <div>
                    <h3>Monitoramento ambiental</h3>
                    <p>Armazene temperatura da agua, salinidade, pH e nivel de poluicao.</p>
                </div>
                <div>
                    <h3>Consulta operacional</h3>
                    <p>Visualize registros recentes para apoiar analises e acompanhamento continuo.</p>
                </div>
            </div>
        </section>
    </main>
</body>
</html>
