-- Banco de Dados: Frota Passos
-- Estrutura Limpa para Instalação

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- --------------------------------------------------------

--
-- 1. Tabela `usuarios`
--
CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `senha` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `usuario` (`usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dados padrão para `usuarios` (Senha: admin)
--
INSERT INTO `usuarios` (`usuario`, `senha`) VALUES
('admin', '$2y$10$8.Dk.X.4.X.4.X.4.X.4.u.X.4.X.4.X.4.X.4.X.4.X.4.X.4.X.4');

-- --------------------------------------------------------

--
-- 2. Tabela `veiculos`
--
CREATE TABLE `veiculos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `marca` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `modelo` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `placa` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ano` int(11) DEFAULT NULL,
  `cor` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
  `km_atual` int(11) DEFAULT '0',
  `criado_em` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  
  -- Dados Técnicos e Documentação
  `renavam` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
  `chassi` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
  `combustivel_padrao` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `oleo_motor` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `calibragem_pneus` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  
  -- Dados de Seguro
  `seguradora` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `apolice` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `telefone_seguro` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  
  -- Regras de Manutenção
  `intervalo_oleo_km` int(11) DEFAULT '10000',
  `intervalo_filtro_ar_km` int(11) DEFAULT '20000',
  `intervalo_filtro_comb_km` int(11) DEFAULT '20000',
  `intervalo_tempo_meses` int(11) DEFAULT '12',
  
  PRIMARY KEY (`id`),
  UNIQUE KEY `placa` (`placa`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- 3. Tabela `abastecimentos`
--
CREATE TABLE `abastecimentos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `veiculo_id` int(11) DEFAULT NULL,
  `data_abastecimento` date DEFAULT NULL,
  `litros` decimal(10,2) DEFAULT NULL,
  `valor_total` decimal(10,2) DEFAULT NULL,
  `km_momento` int(11) DEFAULT NULL,
  `tipo_combustivel` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `especialidade` varchar(50) COLLATE utf8_unicode_ci DEFAULT 'Pessoal',
  PRIMARY KEY (`id`),
  KEY `veiculo_id` (`veiculo_id`),
  CONSTRAINT `abastecimentos_ibfk_1` FOREIGN KEY (`veiculo_id`) REFERENCES `veiculos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- 4. Tabela `manutencoes`
--
CREATE TABLE `manutencoes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `veiculo_id` int(11) DEFAULT NULL,
  `data_manutencao` date DEFAULT NULL,
  `tipo` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `descricao` text COLLATE utf8_unicode_ci,
  `valor` decimal(10,2) DEFAULT NULL,
  `km_momento` int(11) DEFAULT NULL,
  `proxima_troca_km` int(11) DEFAULT NULL,
  `especialidade` varchar(50) COLLATE utf8_unicode_ci DEFAULT 'Pessoal',
  PRIMARY KEY (`id`),
  KEY `veiculo_id` (`veiculo_id`),
  CONSTRAINT `manutencoes_ibfk_1` FOREIGN KEY (`veiculo_id`) REFERENCES `veiculos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- 5. Tabela `checklists`
--
CREATE TABLE `checklists` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `veiculo_id` int(11) NOT NULL,
  `data_verificacao` date DEFAULT NULL,
  `nivel_oleo` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `nivel_agua` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `calibragem_pneus` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `luzes_sinalizacao` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `lataria_pintura` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `observacoes` text COLLATE utf8_unicode_ci,
  `criado_em` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `veiculo_id` (`veiculo_id`),
  CONSTRAINT `checklists_ibfk_1` FOREIGN KEY (`veiculo_id`) REFERENCES `veiculos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- 6. View `v_relatorio_consumo`
--
CREATE OR REPLACE VIEW `v_relatorio_consumo` AS 
SELECT 
    `a`.`id` AS `id`, 
    `a`.`veiculo_id` AS `veiculo_id`, 
    `v`.`modelo` AS `modelo`, 
    `v`.`placa` AS `placa`, 
    `a`.`data_abastecimento` AS `data_abastecimento`, 
    `a`.`km_momento` AS `km_momento`, 
    `a`.`litros` AS `litros`, 
    `a`.`valor_total` AS `valor_total`, 
    -- Cálculo de KM Percorrido
    (`a`.`km_momento` - (
        SELECT max(`b`.`km_momento`) 
        FROM `abastecimentos` `b` 
        WHERE ((`b`.`veiculo_id` = `a`.`veiculo_id`) AND (`b`.`km_momento` < `a`.`km_momento`))
    )) AS `km_percorrido`, 
    -- Cálculo Média
    round(((`a`.`km_momento` - (
        SELECT max(`b`.`km_momento`) 
        FROM `abastecimentos` `b` 
        WHERE ((`b`.`veiculo_id` = `a`.`veiculo_id`) AND (`b`.`km_momento` < `a`.`km_momento`))
    )) / `a`.`litros`),2) AS `media_kml`, 
    -- Cálculo Custo
    round((`a`.`valor_total` / (`a`.`km_momento` - (
        SELECT max(`b`.`km_momento`) 
        FROM `abastecimentos` `b` 
        WHERE ((`b`.`veiculo_id` = `a`.`veiculo_id`) AND (`b`.`km_momento` < `a`.`km_momento`))
    ))),2) AS `custo_por_km` 
FROM (`abastecimentos` `a` JOIN `veiculos` `v` ON ((`a`.`veiculo_id` = `v`.`id`))) 
ORDER BY `a`.`data_abastecimento` DESC;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;