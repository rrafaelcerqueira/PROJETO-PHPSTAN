-- Banco usado pela demonstracao. Abra no HeidiSQL para ver o historico das analises.
CREATE DATABASE IF NOT EXISTS phpstan_demo CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE phpstan_demo;

CREATE TABLE IF NOT EXISTS analises (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nivel TINYINT NOT NULL,
    pasta VARCHAR(100) NOT NULL,
    total_erros INT NOT NULL DEFAULT 0,
    criada_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS erros (
    id INT AUTO_INCREMENT PRIMARY KEY,
    analise_id INT NOT NULL,
    arquivo VARCHAR(255) NOT NULL,
    linha INT NOT NULL,
    mensagem TEXT NOT NULL,
    FOREIGN KEY (analise_id) REFERENCES analises(id) ON DELETE CASCADE
) ENGINE=InnoDB;
