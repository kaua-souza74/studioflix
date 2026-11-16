CREATE DATABASE IF NOT EXISTS studioflix
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE studioflix;

CREATE TABLE IF NOT EXISTS users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_users_role (role)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS movies (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(180) NOT NULL,
    genre VARCHAR(80) NOT NULL,
    release_year SMALLINT UNSIGNED NOT NULL,
    runtime_minutes SMALLINT UNSIGNED NOT NULL,
    rating DECIMAL(3,1) NULL,
    synopsis TEXT NOT NULL,
    poster VARCHAR(255) NOT NULL,
    backdrop VARCHAR(255) NOT NULL,
    trailer_id VARCHAR(32) NULL,
    featured TINYINT(1) NOT NULL DEFAULT 0,
    is_published TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_movies_title_year (title, release_year),
    INDEX idx_movies_featured (featured, is_published),
    INDEX idx_movies_genre (genre, is_published)
) ENGINE=InnoDB;

ALTER TABLE movies
    MODIFY poster VARCHAR(500) NOT NULL,
    MODIFY backdrop VARCHAR(500) NOT NULL;

CREATE TABLE IF NOT EXISTS password_resets (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    token_hash CHAR(64) NOT NULL UNIQUE,
    expires_at DATETIME NOT NULL,
    used_at DATETIME NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_password_resets_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_password_resets_expiration (expires_at, used_at)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS watch_history (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    movie_id BIGINT UNSIGNED NOT NULL,
    watched_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_watch_history_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_watch_history_movie FOREIGN KEY (movie_id) REFERENCES movies(id) ON DELETE CASCADE,
    INDEX idx_watch_history_user_date (user_id, watched_at)
) ENGINE=InnoDB;

INSERT IGNORE INTO users (name, email, password_hash, role)
VALUES ('Administrador Studioflix', 'admin@studioflix.local', '$2y$10$RFIexyeaeZB7jWXTmj9IQedrnfXiDKSIL7rMLP57BD53vyKL/VvoS', 'admin');

UPDATE movies SET title = 'The Batman' WHERE title = 'Batman' AND release_year = 2022;
UPDATE movies
SET title = 'A Origem', genre = 'Suspense', release_year = 2010, runtime_minutes = 148,
    rating = 8.8,
    synopsis = 'Um especialista em invadir sonhos recebe a missão de plantar uma ideia na mente de um herdeiro, enfrentando os limites entre sonho e realidade.',
    trailer_id = 'YoHD9XEInc0', featured = 0
WHERE title = 'Arquivo Profundo' AND release_year = 2025;

INSERT INTO movies (title, genre, release_year, runtime_minutes, rating, synopsis, poster, backdrop, trailer_id, featured)
VALUES
    ('A Chegada', 'Ficção científica', 2016, 116, 7.9,
     'Quando misteriosas naves chegam à Terra, uma linguista é chamada para compreender a mensagem que pode mudar o futuro da humanidade.',
     'https://image.tmdb.org/t/p/w500/x2FJsf1ElAgr63Y3PNPtJrcmpoe.jpg', 'https://i.ytimg.com/vi/tFMo3UJ4B4g/maxresdefault.jpg', 'tFMo3UJ4B4g', 1),
    ('Interestelar', 'Aventura espacial', 2014, 169, 8.7,
     'Uma equipe atravessa os limites do espaço em busca de um novo lar para a humanidade.',
     'https://cdn.ome.lt/legacy/images/galerias/Interstellar/Interstellar-Poster-18Set2014-1.jpg', 'https://i.ytimg.com/vi/zSWdZVtXT7E/maxresdefault.jpg', 'zSWdZVtXT7E', 0),
    ('Duna: Parte Dois', 'Ficção científica', 2024, 166, 8.5,
     'Paul Atreides une forças com os Fremen enquanto busca justiça e enfrenta o destino que se aproxima.',
     'https://images.fandango.com/ImageRenderer/500/0/redesign/static/img/default_poster--dark-mode.png/0/images/masterrepository/Fandango/234877/DUNE2_VERT_MAIN_2764x4096_DOM_REV.jpg', 'https://i.ytimg.com/vi/Way9Dexny3w/maxresdefault.jpg', 'Way9Dexny3w', 0),
    ('The Batman', 'Suspense', 2022, 176, 7.8,
     'Um detetive de Gotham segue uma trilha de enigmas que revela o lado mais sombrio da cidade.',
     'https://image.tmdb.org/t/p/w440_and_h660_face/l3ie0WFra62qkVKKABuHWqdw67M.jpg', 'https://i.ytimg.com/vi/mqqft2x_Aa4/maxresdefault.jpg', 'mqqft2x_Aa4', 0),
    ('Everest', 'Aventura', 2015, 121, 7.1,
     'Duas expedições enfrentam uma tempestade extrema durante uma tentativa de chegar ao topo da montanha.',
     'https://www.longtake.it/upload/documenti/7/71/716/7160.jpg', 'https://i.ytimg.com/vi/5ZQVpPiOji0/maxresdefault.jpg', '5ZQVpPiOji0', 0),
    ('O Segredo do Abismo', 'Ficção científica', 1989, 140, 7.5,
     'Uma equipe de mergulho investiga o desaparecimento de um submarino nas profundezas do oceano.',
     'https://m.media-amazon.com/images/M/MV5BMGRkNzJlNjgtZmUwYS00Y2U3LTljZDctMTQxYWIxOWVjMzNjXkEyXkFqcGc%40._V1_.jpg', 'https://i.ytimg.com/vi/MuM7uRdIcb8/maxresdefault.jpg', 'MuM7uRdIcb8', 0),
    ('A Origem', 'Suspense', 2010, 148, 8.8,
     'Um especialista em invadir sonhos recebe a missão de plantar uma ideia na mente de um herdeiro, enfrentando os limites entre sonho e realidade.',
     'https://image.tmdb.org/t/p/original/evIIv9Cr2NGQz9Y0Wxywckrj0DK.jpg', 'https://i.ytimg.com/vi/YoHD9XEInc0/maxresdefault.jpg', 'YoHD9XEInc0', 0)
ON DUPLICATE KEY UPDATE
    genre = VALUES(genre),
    runtime_minutes = VALUES(runtime_minutes),
    rating = VALUES(rating),
    synopsis = VALUES(synopsis),
    poster = VALUES(poster),
    backdrop = VALUES(backdrop),
    trailer_id = VALUES(trailer_id),
    featured = VALUES(featured),
    is_published = 1;
