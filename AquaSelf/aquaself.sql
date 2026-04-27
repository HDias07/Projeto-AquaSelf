CREATE DATABASE IF NOT EXISTS aquaself
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE aquaself;

CREATE TABLE IF NOT EXISTS animais_marinhos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(120) NOT NULL,
    especie VARCHAR(120) NOT NULL,
    identificador VARCHAR(80) NOT NULL,
    localizacao VARCHAR(120) NOT NULL,
    estado_saude VARCHAR(120) NOT NULL,
    data_registro DATE NOT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS monitoramento_ambiental (
    id INT AUTO_INCREMENT PRIMARY KEY,
    local_coleta VARCHAR(120) NOT NULL,
    temperatura_agua DECIMAL(5,2) NOT NULL,
    salinidade DECIMAL(5,2) NOT NULL,
    ph_agua DECIMAL(4,2) NOT NULL,
    nivel_poluicao VARCHAR(30) NOT NULL,
    data_coleta DATE NOT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
