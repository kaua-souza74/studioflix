<?php
require __DIR__ . '/includes/bootstrap.php';

$search = trim((string) ($_GET['q'] ?? ''));
$genre = trim((string) ($_GET['genre'] ?? ''));
$result = catalog_result($search, $genre);
$movies = $result['movies'];
$featured = null;
foreach ($movies as $movie) {
    if ((int) ($movie['featured'] ?? 0) === 1) {
        $featured = $movie;
        break;
    }
}
if ($featured === null) {
    $allMovies = catalog_result()['movies'];
    $featured = $allMovies[0] ?? fallback_movies()[0];
}
$featuredTarget = 'assistir.php?id=' . (int) $featured['id'];
$genres = array_values(array_unique(array_column(catalog_result()['movies'], 'genre')));
$pageTitle = 'Catálogo';
$activeNav = 'catalog';
$showSearch = true;
require __DIR__ . '/includes/header.php';
?>
<section class="catalog-page">
    <?php if (!$result['database']): ?>
        <div class="setup-note"><strong>Prévia local</strong><span>Importe <code>database/schema.sql</code> no phpMyAdmin para ativar o MySQL e o login.</span></div>
    <?php endif; ?>

    <section class="feature-panel" style="background-image: url('<?= h(image_src((string) $featured['backdrop'])) ?>')" aria-labelledby="feature-title">
        <div class="feature-copy">
            <p class="eyebrow feature-eyebrow">FILME EM DESTAQUE <span>•</span> <?= h((string) $featured['genre']) ?></p>
            <h1 id="feature-title"><?= h($featured['title']) ?></h1>
            <p class="feature-description"><?= h($featured['synopsis']) ?></p>
            <div class="feature-meta">
                <span><?= (int) $featured['release_year'] ?></span>
                <span class="meta-dot"></span>
                <span><?= (int) $featured['runtime_minutes'] ?> min</span>
                <?php if (!empty($featured['rating'])): ?><span class="rating-pill">★ <?= h($featured['rating']) ?></span><?php endif; ?>
            </div>
            <div class="feature-actions">
                <a class="button button-primary" href="<?= h(logged_in() ? $featuredTarget : 'login.php?next=' . rawurlencode($featuredTarget)) ?>" <?php if (logged_in()): ?>data-watch-transition<?php else: ?>data-auth-modal data-auth-next="<?= h($featuredTarget) ?>" data-auth-movie="<?= h($featured['title']) ?>"<?php endif; ?>><?= logged_in() ? 'Ver trailer' : 'Entrar para assistir' ?></a>
                <a class="button button-glass" href="#catalogo">Explorar catálogo</a>
            </div>
        </div>
        <div class="feature-count"><span>01</span><span class="feature-count-line"></span><span><?= str_pad((string) count(catalog_result()['movies']), 2, '0', STR_PAD_LEFT) ?></span></div>
        <div class="feature-note"><span class="eyebrow">UMA HISTÓRIA PARA DESACELERAR</span><span>Disponível no catálogo</span></div>
    </section>

    <div class="catalog-heading" id="catalogo">
        <div>
            <p class="eyebrow">ESCOLHIDOS PARA VOCÊ</p>
            <h2><?= $search !== '' ? 'Resultados da busca' : 'Encontre sua próxima sessão' ?></h2>
        </div>
        <span class="catalog-count"><?= count($movies) ?> títulos</span>
    </div>

    <nav class="genre-filters" aria-label="Filtrar por gênero">
        <a class="filter-chip <?= $genre === '' ? 'is-active' : '' ?>" href="index.php<?= $search !== '' ? '?q=' . urlencode($search) : '' ?>">Todos</a>
        <?php foreach ($genres as $genreName): ?>
            <?php
            $query = array_filter(['q' => $search, 'genre' => $genreName], static fn ($value) => $value !== '');
            ?>
            <a class="filter-chip <?= $genre === $genreName ? 'is-active' : '' ?>" href="index.php?<?= h(http_build_query($query)) ?>"><?= h($genreName) ?></a>
        <?php endforeach; ?>
    </nav>

    <?php if ($movies === []): ?>
        <div class="empty-state empty-state-inline">
            <h3>Nenhum título encontrado.</h3>
            <p>Tente outro nome ou escolha um gênero diferente.</p>
            <a class="text-link" href="index.php">Limpar filtros</a>
        </div>
    <?php else: ?>
        <div class="movie-grid">
            <?php foreach ($movies as $index => $movie): ?>
                <?php
                $watchTarget = 'assistir.php?id=' . (int) $movie['id'];
                $posterTarget = logged_in() ? $watchTarget : 'login.php?next=' . rawurlencode($watchTarget);
                ?>
                <article class="movie-card">
                    <a class="poster-link" href="<?= h($posterTarget) ?>" aria-label="<?= logged_in() ? 'Abrir ' : 'Entrar para assistir ' ?><?= h($movie['title']) ?>" <?php if (logged_in()): ?>data-watch-transition<?php else: ?>data-auth-modal data-auth-next="<?= h($watchTarget) ?>" data-auth-movie="<?= h($movie['title']) ?>"<?php endif; ?>>
                        <img class="movie-poster" src="<?= h(image_src((string) $movie['poster'])) ?>" data-fallback-src="<?= h(image_fallback_src((string) $movie['poster'])) ?>" onerror="if (this.dataset.fallbackSrc &amp;&amp; this.dataset.fallbackTried !== 'true') { this.dataset.fallbackTried = 'true'; this.src = this.dataset.fallbackSrc; }" alt="Pôster de <?= h($movie['title']) ?>" loading="<?= $index < 4 ? 'eager' : 'lazy' ?>" referrerpolicy="no-referrer">
                        <span class="poster-action"><?= logged_in() ? 'Ver trailer' : 'Entrar para assistir' ?></span>
                    </a>
                    <div class="movie-card-copy">
                        <div class="movie-card-title-row">
                            <h3><?= h($movie['title']) ?></h3>
                            <?php if (!empty($movie['rating'])): ?><span class="movie-rating">★ <?= h($movie['rating']) ?></span><?php endif; ?>
                        </div>
                        <p><?= h($movie['genre']) ?> <span>·</span> <?= (int) $movie['release_year'] ?></p>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <section class="catalog-bottom-callout">
        <div><p class="eyebrow">A SUA PRÓXIMA SESSÃO</p><h2>Histórias boas pedem tempo.</h2></div>
        <?php if (logged_in()): ?>
            <a class="button button-outline" href="perfil.php">Ver meu perfil</a>
        <?php else: ?>
            <a class="button button-outline" href="cadastro.php">Criar minha conta</a>
        <?php endif; ?>
    </section>
</section>
<?php if (!logged_in()): ?>
    <div class="auth-modal" id="auth-modal" hidden>
        <button class="auth-modal-backdrop" type="button" data-auth-modal-close aria-label="Fechar login" tabindex="-1"></button>
        <div class="auth-modal-inner">
            <section class="auth-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="auth-modal-title" aria-describedby="auth-modal-description" tabindex="-1">
                <div class="auth-modal-topline">
                    <a class="brand-lockup" href="index.php" aria-label="Studioflix — catálogo">
                        <img src="assets/images/studioflix-mark.webp" alt="" class="brand-mark">
                        <span class="brand-word">studio<span>flix</span></span>
                    </a>
                    <button class="auth-modal-close" type="button" data-auth-modal-close aria-label="Fechar login">×</button>
                </div>
                <div class="auth-card auth-modal-card">
                    <section class="auth-form-side">
                        <div class="auth-form-inner">
                            <p class="eyebrow" data-auth-modal-eyebrow>BEM-VINDO DE VOLTA</p>
                            <h1 id="auth-modal-title" data-auth-modal-title>Entre no Studioflix</h1>
                            <p class="auth-intro" id="auth-modal-description" data-auth-modal-description>Acesse sua conta e continue de onde a sua próxima história começa.</p>
                            <?php
                            $loginFormAction = 'login.php?modal=1';
                            $loginFormModal = true;
                            $next = '';
                            $nextQuery = '';
                            $email = '';
                            require __DIR__ . '/includes/login_form.php';
                            ?>
                        </div>
                    </section>
                    <aside class="auth-art">
                        <img src="assets/images/hero-arrival.webp" alt="" class="auth-photo">
                        <div class="auth-art-top"><span class="eyebrow">UMA EXPERIÊNCIA STUDIOFLIX</span><span class="auth-art-mark">SF</span></div>
                        <div class="auth-art-caption">
                            <span class="eyebrow">TELA GRANDE. HISTÓRIAS MAIORES.</span>
                            <p>Uma boa história muda a noite.</p>
                        </div>
                    </aside>
                </div>
            </section>
        </div>
    </div>
<?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
