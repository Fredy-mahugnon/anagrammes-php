<?php

$flash = consume_flash();

$authPage = in_array($pageTitle ?? '', ['Inscription', 'Connexion'], true);

// Le résultat colore les bordures du panneau (vert pour un succès, rouge sinon).
$resultState = $result ?? null;
$resultClass = $resultState === true
    ? ' has-result-ok'
    : ($resultState === false ? ' has-result-ko' : '');

?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle ?? 'Vérificateur d’anagrammes') ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body class="<?= trim(($authPage ? 'auth-page' : '') . $resultClass) ?>">
<div class="card">
    <header class="site-header">
        <a class="brand" href="<?= BASE_URL ?>/index.php">Anagramme</a>
        <nav class="main-nav" aria-label="Navigation principale">
            <a href="<?= BASE_URL ?>/index.php">Vérifier des anagrammes</a>
            <?php if (is_logged_in()): ?>
                <a href="<?= BASE_URL ?>/index.php#historique">Historique</a>
                <span class="user-badge"><?= e($_SESSION['username']) ?></span>
                <a href="<?= BASE_URL ?>/auth/logout.php">Déconnexion</a>
            <?php else: ?>
                <a href="<?= BASE_URL ?>/auth/login.php">Se connecter</a>
            <?php endif; ?>
        </nav>
    </header>

    <div class="card-body">
        <?php if ($flash !== null): ?>
            <div class="alert alert-<?= e($flash['type']) ?>" role="alert">
                <?= e($flash['message']) ?>
            </div>
        <?php endif; ?>
