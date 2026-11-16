<?php
declare(strict_types=1);

function h(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function current_user(): ?array
{
    return isset($_SESSION['user']) && is_array($_SESSION['user']) ? $_SESSION['user'] : null;
}

function logged_in(): bool
{
    return current_user() !== null;
}

function is_admin(): bool
{
    return (current_user()['role'] ?? null) === 'admin';
}

function redirect_to(string $location): never
{
    header('Location: ' . $location);
    exit;
}

function safe_next(?string $next): ?string
{
    if ($next === null || $next === '' || preg_match('/[\r\n\\\\]/', $next)) {
        return null;
    }

    if (preg_match('/\Aassistir\.php\?id=[1-9][0-9]*\z/', $next) === 1) {
        return $next;
    }

    if (!str_starts_with($next, '/') || str_starts_with($next, '//')) {
        return null;
    }

    return $next;
}

function require_login(): void
{
    if (logged_in()) {
        return;
    }

    $next = $_SERVER['REQUEST_URI'] ?? '/';
    redirect_to('login.php?next=' . urlencode($next));
}

function require_admin(): void
{
    require_login();

    if (is_admin()) {
        return;
    }

    http_response_code(403);
    $pageTitle = 'Acesso restrito';
    $activeNav = '';
    require __DIR__ . '/header.php';
    ?>
    <section class="empty-state">
        <p class="eyebrow">ÁREA RESTRITA</p>
        <h1>Esta área é exclusiva da administração.</h1>
        <p>Entre com uma conta administrativa para continuar.</p>
        <a class="button button-primary" href="index.php">Voltar ao catálogo</a>
    </section>
    <?php
    require __DIR__ . '/footer.php';
    exit;
}

function csrf_token(): string
{
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['_csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . h(csrf_token()) . '">';
}

function csrf_valid(): bool
{
    $submitted = $_POST['_csrf'] ?? '';

    return is_string($submitted)
        && isset($_SESSION['_csrf'])
        && hash_equals($_SESSION['_csrf'], $submitted);
}

function set_flash(string $type, string $message): void
{
    $_SESSION['_flash'] = ['type' => $type, 'message' => $message];
}

function take_flash(): ?array
{
    if (!isset($_SESSION['_flash'])) {
        return null;
    }

    $message = $_SESSION['_flash'];
    unset($_SESSION['_flash']);

    return $message;
}

function initials(string $name): string
{
    $parts = preg_split('/\s+/', trim($name)) ?: [];
    $letters = '';

    foreach (array_slice($parts, 0, 2) as $part) {
        if ($part !== '') {
            $letters .= mb_strtoupper(mb_substr($part, 0, 1));
        }
    }

    return $letters !== '' ? $letters : 'SF';
}

function image_src(string $image): string
{
    $image = trim($image);
    $posterSources = [
        'https://www.impawards.com/2016/posters/arrival_ver12_xxlg.jpg' => 'https://image.tmdb.org/t/p/w500/x2FJsf1ElAgr63Y3PNPtJrcmpoe.jpg',
        'https://www.impawards.com/1989/posters/abyss_ver1_xlg.jpg' => 'https://m.media-amazon.com/images/M/MV5BMGRkNzJlNjgtZmUwYS00Y2U3LTljZDctMTQxYWIxOWVjMzNjXkEyXkFqcGc%40._V1_.jpg',
    ];

    if (isset($posterSources[$image])) {
        return $posterSources[$image];
    }

    if (preg_match('~\Ahttps?://[^\s]+\z~i', $image) === 1) {
        return $image;
    }

    return 'assets/images/' . rawurlencode(basename($image));
}

function image_fallback_src(string $image): string
{
    $fallbacks = [
        'https://www.impawards.com/2016/posters/arrival_ver12_xxlg.jpg' => 'https://www.cinemaclock.com/images/posters/1000x1500/97/arrival-2016-us-poster.jpg',
        'https://image.tmdb.org/t/p/w500/x2FJsf1ElAgr63Y3PNPtJrcmpoe.jpg' => 'https://www.cinemaclock.com/images/posters/1000x1500/97/arrival-2016-us-poster.jpg',
        'https://www.impawards.com/1989/posters/abyss_ver1_xlg.jpg' => 'https://media.senscritique.com/media/000011130892/source_big/Abyss.jpg',
        'https://m.media-amazon.com/images/M/MV5BMGRkNzJlNjgtZmUwYS00Y2U3LTljZDctMTQxYWIxOWVjMzNjXkEyXkFqcGc%40._V1_.jpg' => 'https://media.senscritique.com/media/000011130892/source_big/Abyss.jpg',
    ];

    return $fallbacks[$image] ?? '';
}

function fallback_movies(): array
{
    return [
        [
            'id' => 1, 'title' => 'A Chegada', 'genre' => 'Ficção científica', 'release_year' => 2016,
            'runtime_minutes' => 116, 'rating' => '7.9', 'synopsis' => 'Quando misteriosas naves chegam à Terra, uma linguista é chamada para compreender a mensagem que pode mudar o futuro da humanidade.',
            'poster' => 'https://image.tmdb.org/t/p/w500/x2FJsf1ElAgr63Y3PNPtJrcmpoe.jpg', 'backdrop' => 'https://i.ytimg.com/vi/tFMo3UJ4B4g/maxresdefault.jpg', 'trailer_id' => 'tFMo3UJ4B4g', 'featured' => 1,
        ],
        [
            'id' => 2, 'title' => 'Interestelar', 'genre' => 'Aventura espacial', 'release_year' => 2014,
            'runtime_minutes' => 169, 'rating' => '8.7', 'synopsis' => 'Uma equipe atravessa os limites do espaço em busca de um novo lar para a humanidade.',
            'poster' => 'https://cdn.ome.lt/legacy/images/galerias/Interstellar/Interstellar-Poster-18Set2014-1.jpg', 'backdrop' => 'https://i.ytimg.com/vi/zSWdZVtXT7E/maxresdefault.jpg', 'trailer_id' => 'zSWdZVtXT7E', 'featured' => 0,
        ],
        [
            'id' => 3, 'title' => 'Duna: Parte Dois', 'genre' => 'Ficção científica', 'release_year' => 2024,
            'runtime_minutes' => 166, 'rating' => '8.5', 'synopsis' => 'Paul Atreides une forças com os Fremen enquanto busca justiça e enfrenta o destino que se aproxima.',
            'poster' => 'https://images.fandango.com/ImageRenderer/500/0/redesign/static/img/default_poster--dark-mode.png/0/images/masterrepository/Fandango/234877/DUNE2_VERT_MAIN_2764x4096_DOM_REV.jpg', 'backdrop' => 'https://i.ytimg.com/vi/Way9Dexny3w/maxresdefault.jpg', 'trailer_id' => 'Way9Dexny3w', 'featured' => 0,
        ],
        [
            'id' => 4, 'title' => 'The Batman', 'genre' => 'Suspense', 'release_year' => 2022,
            'runtime_minutes' => 176, 'rating' => '7.8', 'synopsis' => 'Um detetive de Gotham segue uma trilha de enigmas que revela o lado mais sombrio da cidade.',
            'poster' => 'https://image.tmdb.org/t/p/w440_and_h660_face/l3ie0WFra62qkVKKABuHWqdw67M.jpg', 'backdrop' => 'https://i.ytimg.com/vi/mqqft2x_Aa4/maxresdefault.jpg', 'trailer_id' => 'mqqft2x_Aa4', 'featured' => 0,
        ],
        [
            'id' => 5, 'title' => 'Everest', 'genre' => 'Aventura', 'release_year' => 2015,
            'runtime_minutes' => 121, 'rating' => '7.1', 'synopsis' => 'Duas expedições enfrentam uma tempestade extrema durante uma tentativa de chegar ao topo da montanha.',
            'poster' => 'https://www.longtake.it/upload/documenti/7/71/716/7160.jpg', 'backdrop' => 'https://i.ytimg.com/vi/5ZQVpPiOji0/maxresdefault.jpg', 'trailer_id' => '5ZQVpPiOji0', 'featured' => 0,
        ],
        [
            'id' => 6, 'title' => 'O Segredo do Abismo', 'genre' => 'Suspense', 'release_year' => 1989,
            'runtime_minutes' => 140, 'rating' => '7.5', 'synopsis' => 'Uma equipe de mergulho investiga o desaparecimento de um submarino nas profundezas do oceano.',
            'poster' => 'https://m.media-amazon.com/images/M/MV5BMGRkNzJlNjgtZmUwYS00Y2U3LTljZDctMTQxYWIxOWVjMzNjXkEyXkFqcGc%40._V1_.jpg', 'backdrop' => 'https://i.ytimg.com/vi/MuM7uRdIcb8/maxresdefault.jpg', 'trailer_id' => 'MuM7uRdIcb8', 'featured' => 0,
        ],
        [
            'id' => 7, 'title' => 'A Origem', 'genre' => 'Suspense', 'release_year' => 2010,
            'runtime_minutes' => 148, 'rating' => '8.8', 'synopsis' => 'Um especialista em invadir sonhos recebe a missão de plantar uma ideia na mente de um herdeiro, enfrentando os limites entre sonho e realidade.',
            'poster' => 'https://image.tmdb.org/t/p/original/evIIv9Cr2NGQz9Y0Wxywckrj0DK.jpg', 'backdrop' => 'https://i.ytimg.com/vi/YoHD9XEInc0/maxresdefault.jpg', 'trailer_id' => 'YoHD9XEInc0', 'featured' => 0,
        ],
    ];
}

function catalog_result(string $search = '', string $genre = ''): array
{
    try {
        $sql = 'SELECT id, title, genre, release_year, runtime_minutes, rating, synopsis, poster, backdrop, trailer_id, featured
                FROM movies WHERE is_published = 1';
        $params = [];

        if ($search !== '') {
            $sql .= ' AND (title LIKE :search OR genre LIKE :search OR synopsis LIKE :search)';
            $params['search'] = '%' . $search . '%';
        }

        if ($genre !== '' && $genre !== 'Todos') {
            $sql .= ' AND genre = :genre';
            $params['genre'] = $genre;
        }

        $sql .= ' ORDER BY featured DESC, title ASC';
        $statement = db()->prepare($sql);
        $statement->execute($params);
        $movies = $statement->fetchAll();

        if ($movies !== []) {
            return ['movies' => $movies, 'database' => true];
        }
    } catch (Throwable) {
        // O catálogo de demonstração mantém a página inicial visível antes da configuração do MySQL.
    }

    $movies = array_filter(fallback_movies(), static function (array $movie) use ($search, $genre): bool {
        $matchesSearch = $search === ''
            || stripos($movie['title'], $search) !== false
            || stripos($movie['genre'], $search) !== false
            || stripos($movie['synopsis'], $search) !== false;
        $matchesGenre = $genre === '' || $genre === 'Todos' || $movie['genre'] === $genre;

        return $matchesSearch && $matchesGenre;
    });

    return ['movies' => array_values($movies), 'database' => false];
}

function find_movie(int $id): ?array
{
    try {
        $statement = db()->prepare(
            'SELECT id, title, genre, release_year, runtime_minutes, rating, synopsis, poster, backdrop, trailer_id, featured
             FROM movies WHERE id = :id AND is_published = 1 LIMIT 1'
        );
        $statement->execute(['id' => $id]);
        $movie = $statement->fetch();

        if ($movie !== false) {
            return $movie;
        }
    } catch (Throwable) {
        // Permite abrir o protótipo visual antes de importar o banco.
    }

    foreach (fallback_movies() as $movie) {
        if ((int) $movie['id'] === $id) {
            return $movie;
        }
    }

    return null;
}

function app_base_url(): string
{
    $configured = getenv('STUDIOFLIX_APP_URL');
    if (is_string($configured) && $configured !== '') {
        return rtrim($configured, '/');
    }

    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    if (!preg_match('/\A[a-z0-9.\-:\[\]]+\z/i', $host)) {
        $host = 'localhost';
    }

    $isHttps = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    $scheme = $isHttps ? 'https' : 'http';
    $directory = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
    $directory = $directory === '/' || $directory === '.' ? '' : '/' . trim($directory, '/');

    return $scheme . '://' . $host . $directory;
}

function write_local_reset_message(string $email, string $url): void
{
    $environment = getenv('STUDIOFLIX_ENV') ?: 'local';
    if ($environment !== 'local') {
        return;
    }

    $line = sprintf("[%s] %s | %s%s", date('Y-m-d H:i:s'), $email, $url, PHP_EOL);
    $path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'studioflix-reset-outbox.log';
    @file_put_contents($path, $line, FILE_APPEND | LOCK_EX);
}

function send_reset_email(string $email, string $url): void
{
    $from = getenv('STUDIOFLIX_MAIL_FROM');
    $sent = false;

    if (is_string($from) && $from !== '' && function_exists('mail')) {
        $subject = 'Redefinição de senha - Studioflix';
        $message = "Use o link abaixo para criar uma nova senha. Ele expira em 30 minutos:\r\n\r\n" . $url;
        $headers = 'From: ' . $from . "\r\n" . 'Content-Type: text/plain; charset=UTF-8';
        $sent = @mail($email, $subject, $message, $headers);
    }

    if (!$sent) {
        write_local_reset_message($email, $url);
    }
}
