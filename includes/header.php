<?php
$pageTitle = $pageTitle ?? 'Studioflix';
$activeNav = $activeNav ?? 'catalog';
$showSearch = $showSearch ?? false;
$user = current_user();
$flashMessage = take_flash();
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#111b27">
    <title><?= h($pageTitle) ?> · Studioflix</title>
    <link rel="stylesheet" href="assets/css/studioflix.css">
</head>
<body class="app-page">
<div class="app-shell">
    <aside class="sidebar">
        <a class="brand-lockup" href="index.php" aria-label="Studioflix — catálogo">
            <img src="assets/images/studioflix-mark.webp" alt="" class="brand-mark">
            <span class="brand-word">studio<span>flix</span></span>
        </a>
        <p class="side-caption">SEU CINEMA, NO SEU TEMPO</p>
        <nav class="side-nav" aria-label="Navegação principal">
            <a class="side-link <?= $activeNav === 'catalog' ? 'is-active' : '' ?>" href="index.php">
                <span class="side-index">01</span><span>Catálogo</span>
            </a>
            <a class="side-link <?= $activeNav === 'profile' ? 'is-active' : '' ?>" href="<?= $user ? 'perfil.php' : 'login.php' ?>" <?php if (!$user): ?>data-auth-modal<?php endif; ?>>
                <span class="side-index">02</span><span>Meu perfil</span>
            </a>
            <?php if (is_admin()): ?>
                <a class="side-link <?= $activeNav === 'admin' ? 'is-active' : '' ?>" href="admin.php">
                    <span class="side-index">03</span><span>Administração</span>
                </a>
            <?php endif; ?>
        </nav>
        <div class="sidebar-bottom">
            <div class="sidebar-promo">
                <span class="eyebrow">UMA BOA HISTÓRIA</span>
                <p>Começa com um clique.</p>
            </div>
            <?php if ($user): ?>
                <div class="sidebar-user">
                    <span class="avatar"><?= h(initials($user['name'])) ?></span>
                    <span class="sidebar-user-copy"><strong><?= h($user['name']) ?></strong><small><?= is_admin() ? 'Administrador' : 'Membro Studioflix' ?></small></span>
                </div>
            <?php else: ?>
                <a class="side-login" href="login.php" data-auth-modal>Entrar na minha conta</a>
            <?php endif; ?>
        </div>
    </aside>

    <main class="main-column">
        <header class="topbar">
            <div class="topbar-context">
                <span class="topbar-overline">STUDIOFLIX</span>
                <span class="topbar-divider">/</span>
                <span><?= h($pageTitle) ?></span>
            </div>
            <?php if ($showSearch): ?>
                <form class="search-form" action="index.php" method="get" role="search">
                    <label class="sr-only" for="catalog-search">Buscar no catálogo</label>
                    <input id="catalog-search" type="search" name="q" placeholder="Busque um filme ou gênero" value="<?= h($_GET['q'] ?? '') ?>">
                    <button type="submit">Buscar</button>
                </form>
            <?php endif; ?>
            <div class="topbar-actions">
                <?php if ($user): ?>
                    <a class="topbar-user" href="perfil.php"><span class="avatar avatar-small"><?= h(initials($user['name'])) ?></span><span><?= h(explode(' ', trim($user['name']))[0] ?? 'Perfil') ?></span></a>
                    <a class="button button-quiet button-small" href="logout.php">Sair</a>
                <?php else: ?>
                    <a class="button button-quiet button-small" href="login.php" data-auth-modal>Entrar</a>
                    <a class="button button-primary button-small" href="cadastro.php">Criar conta</a>
                <?php endif; ?>
            </div>
        </header>
        <div class="page-content">
            <?php if ($flashMessage): ?>
                <div class="flash flash-<?= h($flashMessage['type'] ?? 'info') ?>" role="status"><?= h($flashMessage['message'] ?? '') ?></div>
            <?php endif; ?>
