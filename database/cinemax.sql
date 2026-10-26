-- Banco de dados: `cinemax` (MariaDB/MySQL)
-- Usuários são FICTÍCIOS, apenas para demonstração. Nenhum dado de cartão real é armazenado:
-- o sistema grava só os 4 últimos dígitos e nunca o CVV.
-- Logins de teste:
--   Mestre (admin): mestre@cinemax.com  / admin123
--   Usuário:        usuario@exemplo.com / teste123

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";
SET NAMES utf8mb4;

CREATE DATABASE IF NOT EXISTS `cinemax` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `cinemax`;

-- --------------------------------------------------------
-- Tabela `usuarios`
-- --------------------------------------------------------
CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `foto` varchar(255) NOT NULL,
  `github` varchar(255) DEFAULT NULL,
  `linkedin` varchar(255) DEFAULT NULL,
  `instagram` varchar(255) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `plano` enum('mensal','anual') DEFAULT 'mensal',
  `meio_pagamento` enum('cartao','pix') DEFAULT 'cartao',
  `numero_cartao` varchar(20) DEFAULT NULL COMMENT 'Somente os 4 últimos dígitos (mascarado)',
  `validade_cartao` varchar(7) DEFAULT NULL,
  `chave_pix` varchar(255) DEFAULT NULL,
  `tipo` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `usuarios` (`id`, `nome`, `email`, `senha`, `criado_em`, `foto`, `github`, `linkedin`, `instagram`, `bio`, `plano`, `meio_pagamento`, `numero_cartao`, `validade_cartao`, `chave_pix`, `tipo`) VALUES
(1, 'Mestre Demo', 'mestre@cinemax.com', '$2y$10$45OKG1uHmGLFpzWp3T/iL.8bcX.Tl/CiMT/uHLzYjqDJHxMYmofDC', '2025-11-06 16:54:51', 'https://cdn0.iconfinder.com/data/icons/solid-corporation-and-business-7/512/867-47-512.png', '', '', '', '', 'anual', 'cartao', '**** **** **** 4242', '12/2030', NULL, 'mestre'),
(2, 'Usuário Demo', 'usuario@exemplo.com', '$2y$10$N9Oa7DAsEC2ez.UX3uG4Fezr15v7BLpDSjhYpv1c78O5kpcR5eShy', '2025-12-05 22:26:05', '', '', '', '', '', 'mensal', 'pix', NULL, NULL, 'usuario@exemplo.com', 'usuario');

-- --------------------------------------------------------
-- Tabela `filmes`
-- --------------------------------------------------------
CREATE TABLE `filmes` (
  `id` int(11) NOT NULL,
  `titulo` varchar(150) NOT NULL,
  `descricao` text DEFAULT NULL,
  `genero` varchar(50) DEFAULT NULL,
  `ano` int(11) DEFAULT NULL,
  `preco` decimal(6,2) DEFAULT 0.00,
  `capa` varchar(255) DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `filmes` (`id`, `titulo`, `descricao`, `genero`, `ano`, `preco`, `capa`, `criado_em`) VALUES
(2, 'Raya e o Último Dragão', 'Raya embarca em uma jornada para restaurar a harmonia entre os povos de Kumandra.', 'Animação / Fantasia', 2021, 25.00, 'https://blogger.googleusercontent.com/img/b/R29vZ2xl/AVvXsEgVpxKA0E4l_wh8eJASgldhvfkjxUkefttjRWoGgufrntNoJpk46o1mgA1of_5N3J6xJxmVQQ8OfqvN6E14W03zVPp3GTFRXyaeZam3ZGZtIVTNu2ejWCqh0XOuS-NO_f-DQwJA8jtQn0GwGaHOd73rkErpTfmEijHY86ieTUTaiY1HiIKEfJZbY_3t9g/s1024/R', '2025-10-27 19:36:21'),
(3, 'Escape from Planet Earth', 'Dois irmãos alienígenas precisam escapar da Terra em uma aventura cheia de humor e ação.', 'Animação / Comédia', 2013, 20.22, 'https://cdn.ome.lt/legacy/images/galerias/Escape-From-Planet-Earth/Escape-From-Planet-Earth-poster.jpg', '2025-10-27 19:36:21'),
(5, 'Os Croods', 'Uma família pré-histórica embarca em uma jornada em busca de um novo lar.', 'Aventura / Família', 2013, 30.23, 'https://nerdrecomenda.com.br/wp-content/uploads/2020/09/the_croods.jpg', '2025-10-27 19:36:21'),
(6, 'Divertidamente', 'As emoções de uma garotinha ganham vida dentro de sua mente em uma comovente aventura.', 'Animação / Comédia / Drama', 2015, 88.33, 'https://image.tmdb.org/t/p/original/66QsqKXhIJH1dMvfONuApmIz4vE.jpg', '2025-10-27 19:36:21'),
(7, 'Bob Esponja: O Incrível Resgate', 'Bob e Patrick partem em uma missão heroica para salvar Gary, o caracol desaparecido.', 'Animação / Aventura / Comédia', 2020, 45.66, 'https://cinepop.com.br/wp-content/uploads/2019/11/ParamountKids-1194367142348673030-20191112_183131-img1.jpg', '2025-10-27 19:36:21'),
(8, 'Detona Ralph', 'Um vilão de jogo decide ser herói e embarca em uma jornada épica entre mundos digitais.', 'Animação / Comédia / Aventura', 2012, 15.55, 'https://blogger.googleusercontent.com/img/b/R29vZ2xl/AVvXsEg6Gj4mq9C7_fHRMwbRFphtSEUw7MjVjefIyLBp15TcaLk-HsV2ujUAzNatR2rKMDhbMzE8_yjtVtLe7X_2sHV09n796MU21SDrF04fi97OB4wpzTdEwogI2Iav3ymPsd6PhElAGrjLBB0/s1600/detona-ralph-poster-missao-heroi.jpg', '2025-10-27 19:36:21'),
(9, 'Os incríveis 2', 'Uma jornada épica para salvar o futuro da vida na Terra, cheia de fé e amizade.', 'Aventura / Família', 2024, 88.88, 'https://cdn.jornaldaparaiba.com.br/wp-content/uploads/2018/04/cartaz-os-incr%C3%ADveis-2.jpg?xid=694901', '2025-10-27 19:40:28'),
(14, 'Paranorman', 'Norman pode ver fantasmas e isso é a única coisa que pode salvar sua cidade de uma maldição.', 'Animação / Terror / Aventura', 2012, 2.49, 'https://cinemacomrapadura.com.br/imagens/2012/02/ParaNorman-CinemacomRapdura-615x911.jpg', '2025-10-31 03:55:15'),
(20, 'Coisa ruim', 'Um filme de terror português de 2006 sobre uma família de Lisboa que herda uma casa em uma pequena aldeia do interior e se depara com uma maldição e acontecimentos inexplicáveis.', 'Terror', 2012, 35.80, 'https://image.tmdb.org/t/p/original/mvrrXSL1AzSGazFRJsSKybE4Lni.jpg', '2025-11-06 18:00:12'),
(21, 'Os Incríveis', 'Aventura cheia de ação sobre uma família com um segredo incrível.', 'Animação', 2010, 19.50, 'https://br.web.img3.acsta.net/medias/nmedia/18/90/98/38/20123472.jpg', '2025-12-05 22:04:39'),
(22, 'Divertidamente 2', 'Com um salto temporal, Riley se encontra mais velha, passando pela tão temida adolescência. Junto com o amadurecimento, a sala de controle também está passando por uma adaptação para dar lugar a algo totalmente inesperado: novas emoções. As já conhecidas, Alegria, Raiva, Medo, Nojinho e Tristeza não têm certeza de como se sentir quando novos inquilinos chegam ao local.', 'Animação', 2024, 20.50, 'https://cicem.com.br/wp-content/uploads/2024/06/Divertidamente-2-cartaz-divulgacao.jpeg', '2025-12-05 22:07:35');

-- --------------------------------------------------------
-- Tabela `filmes_assistidos`
-- --------------------------------------------------------
CREATE TABLE `filmes_assistidos` (
  `id_assistido` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_filme` int(11) NOT NULL,
  `data_assistido` date DEFAULT curdate(),
  `nota` tinyint(4) DEFAULT NULL,
  `comentario` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Tabela `filmes_desejados`
-- --------------------------------------------------------
CREATE TABLE `filmes_desejados` (
  `id_desejo` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_filme` int(11) NOT NULL,
  `prioridade` enum('baixa','média','alta') DEFAULT 'média',
  `data_registro` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Tabela `avaliacao`
-- --------------------------------------------------------
CREATE TABLE `avaliacao` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `filme_id` int(11) NOT NULL,
  `estrelas` tinyint(4) NOT NULL CHECK (`estrelas` between 1 and 5),
  `comentario` text DEFAULT NULL,
  `data_avaliacao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Tabela `avaliacoes`
-- --------------------------------------------------------
CREATE TABLE `avaliacoes` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `filme_id` int(11) NOT NULL,
  `estrelas` int(11) NOT NULL CHECK (`estrelas` between 1 and 5)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Índices
-- --------------------------------------------------------
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

ALTER TABLE `filmes`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `filmes_assistidos`
  ADD PRIMARY KEY (`id_assistido`),
  ADD UNIQUE KEY `uq_usuario_filme` (`id_usuario`,`id_filme`),
  ADD KEY `id_filme` (`id_filme`);

ALTER TABLE `filmes_desejados`
  ADD PRIMARY KEY (`id_desejo`),
  ADD KEY `id_usuario` (`id_usuario`),
  ADD KEY `id_filme` (`id_filme`);

ALTER TABLE `avaliacao`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_usuario_filme` (`usuario_id`,`filme_id`),
  ADD KEY `fk_avaliacao_filme` (`filme_id`);

ALTER TABLE `avaliacoes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_avaliacao` (`usuario_id`,`filme_id`),
  ADD KEY `filme_id` (`filme_id`);

-- --------------------------------------------------------
-- AUTO_INCREMENT
-- --------------------------------------------------------
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

ALTER TABLE `filmes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

ALTER TABLE `filmes_assistidos`
  MODIFY `id_assistido` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

ALTER TABLE `filmes_desejados`
  MODIFY `id_desejo` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `avaliacao`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

ALTER TABLE `avaliacoes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------
-- Chaves estrangeiras
-- --------------------------------------------------------
ALTER TABLE `avaliacao`
  ADD CONSTRAINT `fk_avaliacao_filme` FOREIGN KEY (`filme_id`) REFERENCES `filmes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_avaliacao_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `avaliacoes`
  ADD CONSTRAINT `avaliacoes_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `avaliacoes_ibfk_2` FOREIGN KEY (`filme_id`) REFERENCES `filmes` (`id`);

ALTER TABLE `filmes_assistidos`
  ADD CONSTRAINT `filmes_assistidos_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `filmes_assistidos_ibfk_2` FOREIGN KEY (`id_filme`) REFERENCES `filmes` (`id`) ON DELETE CASCADE;

ALTER TABLE `filmes_desejados`
  ADD CONSTRAINT `filmes_desejados_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `filmes_desejados_ibfk_2` FOREIGN KEY (`id_filme`) REFERENCES `filmes` (`id`) ON DELETE CASCADE;

-- --------------------------------------------------------
-- Trigger: ao avaliar um filme, ele entra (ou é atualizado) no histórico de assistidos
-- --------------------------------------------------------
DELIMITER $$
CREATE TRIGGER `trg_avaliacao_to_assistidos` AFTER INSERT ON `avaliacao` FOR EACH ROW BEGIN
  INSERT INTO filmes_assistidos (id_usuario, id_filme, data_assistido, nota, comentario)
  VALUES (NEW.usuario_id, NEW.filme_id, CURDATE(), NEW.estrelas, NEW.comentario)
  ON DUPLICATE KEY UPDATE nota = NEW.estrelas, comentario = NEW.comentario;
END
$$
CREATE TRIGGER `trg_update_avaliacao_to_assistidos` AFTER UPDATE ON `avaliacao` FOR EACH ROW BEGIN
  INSERT INTO filmes_assistidos (id_usuario, id_filme, data_assistido, nota, comentario)
  VALUES (NEW.usuario_id, NEW.filme_id, CURDATE(), NEW.estrelas, NEW.comentario)
  ON DUPLICATE KEY UPDATE nota = NEW.estrelas, comentario = NEW.comentario;
END
$$
DELIMITER ;

-- Avaliações de exemplo (o trigger preenche filmes_assistidos automaticamente)
INSERT INTO `avaliacao` (`usuario_id`, `filme_id`, `estrelas`, `comentario`) VALUES
(1, 9, 5, NULL),
(1, 5, 3, NULL),
(1, 14, 4, NULL),
(2, 20, 3, NULL);

COMMIT;
