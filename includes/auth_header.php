<?php
$authTitle = $authTitle ?? 'Entre no Studioflix';
$authDescription = $authDescription ?? 'Sua próxima história está esperando.';
$authEyebrow = $authEyebrow ?? 'BEM-VINDO DE VOLTA';
$authImage = $authImage ?? 'hero-arrival.webp';
$authCaption = $authCaption ?? 'Histórias que ficam com você.';
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#111b27">
    <title><?= h($authTitle) ?> · Studioflix</title>
    <link rel="stylesheet" href="assets/css/studioflix.css">
</head>
<body class="auth-page <?= h($authBodyClass ?? '') ?>">
<main class="auth-wrap">
    <div class="auth-topline">
        <a class="brand-lockup" href="index.php" aria-label="Studioflix — catálogo">
            <img src="assets/images/studioflix-mark.webp" alt="" class="brand-mark">
            <span class="brand-word">studio<span>flix</span></span>
        </a>
        <a class="back-link" href="index.php">Voltar ao catálogo</a>
    </div>
    <div class="auth-card">
        <section class="auth-form-side">
            <div class="auth-form-inner">
                <p class="eyebrow"><?= h($authEyebrow) ?></p>
                <h1><?= h($authTitle) ?></h1>
                <p class="auth-intro"><?= h($authDescription) ?></p>
                <?php if (!empty($authError)): ?>
                    <div class="flash flash-error" role="alert"><?= h($authError) ?></div>
                <?php endif; ?>
