/* CREATE USER 'webuser'@'10.4.2.120' IDENTIFIED BY 'g4';
GRANT ALL PRIVILEGES ON ecotxurdi.* TO 'webuser'@'10.4.2.120';
FLUSH PRIVILEGES; -- Ejecutar unicamente en maquina viurtal de subred privada */

-- Elimina la base de datos si existe
DROP DATABASE IF EXISTS ecotxurdi;

-- Crear la base de datos con codificación UTF-8
CREATE DATABASE ecotxurdi
  CHARACTER SET utf8
  COLLATE utf8_bin;

-- Usar la base de datos creada
USE ecotxurdi;

-- Tabla de usuarios
CREATE TABLE `usuarios` (
    id_usuario INT PRIMARY KEY AUTO_INCREMENT, 
    imagen_user VARCHAR(255),
    usuario VARCHAR(30) NOT NULL UNIQUE, 
    contrasena VARCHAR(255) NOT NULL, 
    rol ENUM('user', 'mod', 'admin') NOT NULL DEFAULT 'user', 
    puntosRanking INT DEFAULT 0, 
    puntosCambio INT DEFAULT 0, 
    ultimo_login DATETIME NULL, -- último inicio de sesión
    remember_token VARCHAR(255) NULL
);

-- Tabla de noticias
CREATE TABLE noticias (
  id_noticia INT AUTO_INCREMENT PRIMARY KEY,
  titulo_noticia VARCHAR(255) NOT NULL,
  descripcion_noticia TEXT NOT NULL,
  imagen_noticia VARCHAR(255),
  fecha_noticia DATE DEFAULT CURRENT_DATE,
  destacado TINYINT(1) DEFAULT 0 -- indica si es noticia destacada
);

-- Tabla de recompensas
CREATE TABLE `recompensas` (
    id_recompensa INT PRIMARY KEY AUTO_INCREMENT,
    imagen_recom VARCHAR(255),
    titulo_recompensa VARCHAR(100) NOT NULL,
    descripcion_recompensa VARCHAR(500) NOT NULL,
    precio INT NOT NULL -- precio en puntos
);

-- Tabla de códigos por recompensa
CREATE TABLE `codigos_recompensa` (
  id_codigo INT PRIMARY KEY AUTO_INCREMENT,
  id_recompensa INT NOT NULL,
  codigo VARCHAR(64) NOT NULL UNIQUE,
  usado BOOLEAN NOT NULL DEFAULT FALSE,
  id_usuario INT NULL,
  fecha_usado DATETIME NULL,
  FOREIGN KEY (id_recompensa) REFERENCES recompensas(id_recompensa)
      ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
      ON DELETE SET NULL ON UPDATE CASCADE
);

-- Tabla de misiones
CREATE TABLE misiones (
    id_mision INT PRIMARY KEY AUTO_INCREMENT,
    titulo_misiones VARCHAR(100) NOT NULL,
    imagen_mision VARCHAR(255),
    descripcion_misiones VARCHAR(500) NOT NULL,
    puntuacion INT NOT NULL,
    ubicacion VARCHAR(100) NOT NULL,
    tipo ENUM('solicitud', 'mision') NOT NULL DEFAULT 'solicitud',
    resuelto BOOLEAN NOT NULL DEFAULT FALSE,
    id_usuario INT,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
        ON DELETE CASCADE ON UPDATE CASCADE
);

-- Tabla que indica qué usuario gestiona qué noticia
CREATE TABLE `gestiona` (
    id_usuario INT,
    id_noticia INT,
    PRIMARY KEY (id_usuario, id_noticia),
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
        ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (id_noticia) REFERENCES noticias(id_noticia)
        ON DELETE CASCADE ON UPDATE CASCADE
);

-- Tabla de recompensas reclamadas por usuarios
CREATE TABLE `reclama` (
    id_usuario INT,
    id_recompensa INT,
    id_codigo INT NULL,
    fecha DATE NOT NULL DEFAULT (CURRENT_DATE),
    PRIMARY KEY (id_usuario, id_recompensa),
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
        ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (id_recompensa) REFERENCES recompensas(id_recompensa)
        ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (id_codigo) REFERENCES codigos_recompensa(id_codigo)
        ON DELETE SET NULL ON UPDATE CASCADE
);

-- Tabla de misiones cumplidas
CREATE TABLE `cumple` (
    id_usuario INT,
    id_mision INT,
    evidencia VARCHAR(255) NULL,
    Fecha DATE NOT NULL DEFAULT (CURRENT_DATE),
    PRIMARY KEY (id_usuario, id_mision),
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
        ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (id_mision) REFERENCES misiones(id_mision)
        ON DELETE CASCADE ON UPDATE CASCADE
);

-- Insertar usuarios iniciales
INSERT INTO usuarios (imagen_user,usuario, contrasena, rol, puntosRanking, puntosCambio, ultimo_login) VALUES
('admin.png', 'admin', '$2y$10$F7bwt4W4SJl6f3GGR2.Pdu/Aqh/0jrWupb0x1puSzr4zN2wbS9QYi', 'admin', 500, 200, NOW()),
('mod.png','mod', '$2y$10$xT2sFLFwNv5cS0N7HY7iWOKhcdAsdZDYcBIAsbrbVi7Uc6GpVdtAe', 'mod', 300, 150, NOW() - INTERVAL 5 MINUTE),
('ana.png','ana', '$2y$10$XUK0Giv7itJSLEHXKEcm7uFuzl1zsbL/4kbnz3XV5lN0wC4UxMhqy', 'user', 120, 50, NOW() - INTERVAL 1 HOUR),
('juan.png','juan', '$2y$10$QAPgp0E8DLajnjfujokp4eW6aL7i7OXWmyMAoAzVSUDOLdIOKkdXK', 'user', 80, 80, NOW() - INTERVAL 2 HOUR),
('luis.png','luis', '$2y$10$MXnGhjDK1l4ruItZ/jxQjOUXHt6LwzZHam2K591sfFgR6bFKtPg4W', 'user', 200, 100, NOW() - INTERVAL 1 DAY),
('mario.png','mario', '$2y$10$Y5useNjT0AzW6Lt4LMKb1OZ29kxOocPbP1sVPOE9oA6GCmphEfS8G', 'user', 220, 110, NOW() - INTERVAL 3 DAY),
('camilo.png','camilo', '$2y$10$ozJk9ozGhWBpKhZVjFw.Z.50pLOYRyTRK/fqsER5v50.AD4KhmkvG', 'user', 115, 25, NOW() - INTERVAL 6 HOUR);

-- Insertar noticias iniciales
INSERT INTO noticias 
(titulo_noticia, descripcion_noticia, imagen_noticia, fecha_noticia, destacado)
VALUES
('Garbiketa jardunaldia parke nagusian', 
 'Larunbat honetan garbiketa jardunaldia egingo da parke nagusian plastikoak eta hondakinak biltzeko. Ekar itzazu zure eskularruak eta batu zaitez!',
 'garbiketa.jpg', '2025-10-05', 1),
('500 zuhaitz landatu dira ibaiaren ertzean', 
 'BOLUNTARIOEI esker, komunitateak 500 zuhaitz berri landatu ditu Ibaia Berdearen inguruan.',
 'zuhaitzak.jpg', '2025-09-28', 0),
('Plastikoaren erabilera murrizteko kanpaina', 
 'Ecotxurdi-k kanpaina hezitzailea abiarazi du plastikoaren ordezko jasangarriei buruz.',
 'plastikoa.jpg', '2025-09-20', 0),
('Argazki lehiaketa ekologikoa', 
 'Parte hartu zure inguruko naturaren argazkirik onenak bidaliz eta irabazi sari ekologikoak.',
 'argazki.jpg', '2025-10-01', 1),
('Konpostatzeko gune berriak hirian', 
 'Udalak hiru konpostatzeko puntu berri jarri ditu auzo ezberdinetan hondakin organikoentzako.',
 'konposta.jpg', '2025-09-15', 0);

-- Insertar recompensas iniciales
INSERT INTO recompensas (titulo_recompensa, imagen_recom, descripcion_recompensa, precio) VALUES
('Amazon opari-txartela', 'recompensa1.jpg', '10€-ko Amazon opari-txartela zure ingurumenarekiko konpromisoa saritzeko.', 200),
('Denda ekologikoan deskontu-bonua', 'recompensa2.jpg', '15%%eko deskontu kupoia produktu jasangarri eta berrerabilgarrietan.', 150),
('Kafe ekologikorako txartela', 'recompensa3.jpg', 'Opari-txartela produktu organikoak erabiltzen dituzten kafetegietan.', 100),
('Garraio publikoaren bonua', 'recompensa4.jpg', '10 bidaiako bonua garraio publikoan karbono aztarna murrizteko.', 250),
('Parke naturalerako sarrera', 'recompensa5.jpg', 'Sarrera doakoa inguruko natura erreserba batean egun batez gozatzeko.', 180);

-- Insertar misiones iniciales
INSERT INTO misiones (titulo_misiones, imagen_mision, descripcion_misiones, puntuacion, ubicacion, tipo, resuelto, id_usuario) VALUES
('Parke batean hondakinak jasotzea', 'mision1.jpg', 'Parte hartu garbiketa jardunaldian eta jaso zure inguruko hondakinak.', 50, 'Donostiako Parke Nagusia', 'mision', TRUE, 1),
('Zuhaitz bat landatu zure auzoan', 'mision2.jpg', 'Landatu zuhaitz bat eta hobetu airearen kalitatea eta biodibertsitatea.', 100, 'Bilboko Erdigunea', 'mision', TRUE, 2),
('Birziklapen tailer batera joatea', 'mision3.jpg', 'Ikasi nola bereizi etxean hondakinak modu egokian.', 40, 'Gasteizko Kultur Etxea', 'mision', FALSE, 1),
('Aste batez garraio publikoa erabiltzea', 'mision4.jpg', 'Autoa erabili gabe ibili aste batez eta pilatu puntu berdeak.', 70, 'Donostia', 'mision', FALSE, 2),
('Etxean baratze txiki bat sortzea', 'mision5.jpg', 'Sortu baratze txiki bat zure etxean barazki ekologikoak hazteko.', 90, 'Iruñeko Auzoa', 'mision', FALSE, 1);

-- Insertar relaciones de gestión de noticias
INSERT INTO gestiona (id_usuario, id_noticia) VALUES
(2, 1),(2, 3),(1, 2),(1, 4),(1, 5);

-- Insertar misiones cumplidas
INSERT INTO cumple (id_usuario, id_mision, evidencia, Fecha) VALUES
(4, 1, 'mision_4_user_6_1760356914.jpg', '2025-09-18'),
(3, 2, 'mision_4_user_6_1760356914.jpg',CURRENT_DATE);

-- Insertar recompensas reclamadas
INSERT INTO reclama (id_usuario, id_recompensa, fecha) VALUES
(3, 3, '2025-09-25'),
(4, 2, '2025-09-26'),
(5, 1, '2025-09-30');

-- Insertar códigos de ejemplo para recompensas
INSERT INTO codigos_recompensa (id_recompensa, codigo) VALUES
(1, 'AMZ-9H3K-1A2B'),
(1, 'AMZ-7JKL-4Z8Q'),
(1, 'AMZ-55RT-99PL'),
(2, 'ECO-15-AB12'),
(2, 'ECO-15-XY34'),
(3, 'CAFE-2025-001'),
(3, 'CAFE-2025-002'),
(4, 'BUS-10TRIPS-01'),
(5, 'PARK-FREE-01');