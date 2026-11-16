<?php
require __DIR__ . '/includes/bootstrap.php';
require_login();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: 0;
$movie = find_movie((int) $id);
if ($movie === null) {
    http_response_code(404);
}

$playing = isset($_GET['play']) && $_GET['play'] === '1';
$trailerId = (string) ($movie['trailer_id'] ?? '');
$validTrailer = preg_match('/\A[A-Za-z0-9_-]{6,20}\z/', $trailerId) === 1;

if ($movie !== null && $playing && $validTrailer) {
    try {
        $statement = db()->prepare('INSERT INTO watch_history (user_id, movie_id) VALUES (:user_id, :movie_id)');
        $statement->execute(['user_id' => current_user()['id'], 'movie_id' => $movie['id']]);
    } catch (Throwable) {
        // A reprodução continua disponível no modo visual mesmo antes do banco ser importado.
    }
}

$pageTitle = $movie ? $movie['title'] : 'Título não encontrado';
$activeNav = 'catalog';
require __DIR__ . '/includes/header.php';
?>
<?php if ($movie === null): ?>
    <section class="empty-state">
        <p class="eyebrow">TÍTULO INDISPONÍVEL</p><h1>Não encontramos esse filme.</h1>
        <a class="button button-primary" href="index.php">Voltar ao catálogo</a>
    </section>
<?php else: ?>
    <section class="watch-page">
        <div class="watch-backdrop" style="background-image:url('<?= h(image_src((string) $movie['backdrop'])) ?>')"></div>
        <div class="watch-topline"><a class="text-link" href="index.php">← Voltar ao catálogo</a><span class="eyebrow">SESSÃO STUDIOFLIX</span></div>
        <div class="watch-layout">
            <div class="watch-main">
                <?php if ($playing && $validTrailer): ?>
                    <div class="video-frame">
                        <iframe src="https://www.youtube-nocookie.com/embed/<?= h($trailerId) ?>?autoplay=1&rel=0" title="Trailer de <?= h($movie['title']) ?>" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
                    </div>
                <?php else: ?>
                    <div class="watch-stage" style="background-image:url('<?= h(image_src((string) $movie['backdrop'])) ?>')">
                        <div class="watch-stage-shade"></div>
                        <div class="watch-stage-content">
                            <p class="eyebrow">TRAILER OFICIAL</p>
                            <?php if ($validTrailer): ?>
                                <a class="play-button" href="assistir.php?id=<?= (int) $movie['id'] ?>&amp;play=1" aria-label="Reproduzir trailer de <?= h($movie['title']) ?>">Reproduzir trailer</a>
                            <?php else: ?>
                                <span class="play-button is-disabled">Trailer em breve</span>
                            <?php endif; ?>
                            <span class="stage-caption">Aperte play. O resto é com a história.</span>
                        </div>
                        <span class="stage-tag">PRÉVIA OFICIAL</span>
                    </div>
                <?php endif; ?>
                <div class="watch-info">
                    <div class="watch-title-block">
                        <p class="eyebrow"><?= h($movie['genre']) ?> <span>·</span> <?= (int) $movie['release_year'] ?></p>
                        <h1><?= h($movie['title']) ?></h1>
                        <p class="watch-synopsis"><?= h($movie['synopsis']) ?></p>
                    </div>
                    <div class="watch-facts">
                        <span><?= (int) $movie['runtime_minutes'] ?> min</span>
                        <?php if (!empty($movie['rating'])): ?><span>★ <?= h($movie['rating']) ?> / 10</span><?php endif; ?>
                        <span>Trailer</span>
                    </div>
                </div>
            </div>
            <aside class="watch-side">
                <p class="eyebrow">SOBRE O TÍTULO</p>
                <div class="watch-poster-card">
                    <img src="<?= h(image_src((string) $movie['poster'])) ?>" data-fallback-src="<?= h(image_fallback_src((string) $movie['poster'])) ?>" onerror="if (this.dataset.fallbackSrc &amp;&amp; this.dataset.fallbackTried !== 'true') { this.dataset.fallbackTried = 'true'; this.src = this.dataset.fallbackSrc; }" alt="Pôster de <?= h($movie['title']) ?>" referrerpolicy="no-referrer">
                    <div><strong><?= h($movie['title']) ?></strong><span><?= h($movie['genre']) ?></span></div>
                </div>
                <div class="watch-side-note"><span class="side-note-mark">SF</span><p>O catálogo é público. Os trailers ficam disponíveis depois do login.</p></div>
            </aside>
        </div>
        <?php
        $related = array_values(array_filter(catalog_result()['movies'], static fn (array $item): bool => (int) $item['id'] !== (int) $movie['id']));
        ?>
        <?php if ($related !== []): ?>
            <section class="related-section"><div class="section-heading"><div><p class="eyebrow">MAIS PARA DESCOBRIR</p><h2>Continue explorando</h2></div><a class="text-link" href="index.php">Ver catálogo</a></div>
                <div class="related-row">
                    <?php foreach (array_slice($related, 0, 4) as $item): ?>
                        <a class="related-card" href="assistir.php?id=<?= (int) $item['id'] ?>" data-watch-transition><img src="<?= h(image_src((string) $item['poster'])) ?>" data-fallback-src="<?= h(image_fallback_src((string) $item['poster'])) ?>" onerror="if (this.dataset.fallbackSrc &amp;&amp; this.dataset.fallbackTried !== 'true') { this.dataset.fallbackTried = 'true'; this.src = this.dataset.fallbackSrc; }" alt="" loading="lazy" referrerpolicy="no-referrer"><span><?= h($item['title']) ?></span></a>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>
    </section>
<?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
