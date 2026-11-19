<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';

$pageTitle = 'Inscription';
$showFooter = true;
$pseudo = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        $errors['form'] = 'Formulaire invalide ou session expirée. Veuillez réessayer.';
    } else {
        $pseudo = trim((string) ($_POST['pseudo'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $confirmation = (string) ($_POST['confirmation'] ?? '');

        if (!preg_match("/^[\p{L}\p{N}_ -]{3,50}$/u", $pseudo)) {
            $errors['pseudo'] = 'Le pseudonyme doit contenir 3 à 50 lettres, chiffres, espaces, tirets ou underscores.';
        }

        if (mb_strlen($password) < 8) {
            $errors['password'] = 'Le mot de passe doit contenir au moins 8 caractères.';
        }

        if ($password !== $confirmation || $confirmation === '') {
            $errors['confirmation'] = 'Les mots de passe ne correspondent pas.';
        }

        if ($errors === []) {
            try {
                $pdo = getPdo();

                $check = $pdo->prepare('SELECT COUNT(*) FROM users WHERE pseudo = :pseudo');
                $check->execute(['pseudo' => $pseudo]);

                if ((int) $check->fetchColumn() > 0) {
                    $errors['pseudo'] = 'Ce pseudonyme est déjà utilisé.';
                } else {
                    $insert = $pdo->prepare(
                        'INSERT INTO users (pseudo, password_hash) VALUES (:pseudo, :password_hash)'
                    );
                    $insert->execute([
                        'pseudo'        => $pseudo,
                        'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                    ]);

                    set_flash('success', 'Compte créé avec succès. Connectez-vous pour continuer.');
                    redirect(BASE_URL . '/auth/login.php');
                }
            } catch (Throwable $exception) {
                $errors['form'] = 'Une erreur est survenue lors de la création du compte.';
            }
        }
    }
}

require_once dirname(__DIR__) . '/includes/header.php';
?>

<div class="auth-wrap">
    <h1>Inscription</h1>

    <?php if (isset($errors['form'])): ?>
        <div class="alert alert-error" role="alert"><?= e($errors['form']) ?></div>
    <?php endif; ?>

    <form class="auth-form" method="post" action="<?= BASE_URL ?>/auth/register.php" novalidate>
        <?= csrf_field() ?>

        <?php foreach ([
            'pseudo'       => ['Pseudo', 'text', 'username'],
            'password'     => ['Mot de passe', 'password', 'new-password'],
            'confirmation' => ['Confirmer mot de passe', 'password', 'new-password'],
        ] as $name => [$label, $type, $autocomplete]): ?>
            <div class="form-group">
                <label for="<?= $name ?>"><?= $label ?></label>
                <div class="input-wrap">
                    <input
                        id="<?= $name ?>"
                        name="<?= $name ?>"
                        type="<?= $type ?>"
                        autocomplete="<?= $autocomplete ?>"
                        maxlength="50"
                        class="text-input <?= isset($errors[$name]) ? 'is-ko' : '' ?>"
                        value="<?= $name === 'pseudo' ? e($pseudo) : '' ?>"
                        required
                    >
                </div>
                <?php if (isset($errors[$name])): ?>
                    <ul class="field-errors"><li><?= e($errors[$name]) ?></li></ul>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>

        <div class="auth-actions">
            <button class="btn" type="submit">S'inscrire</button>
        </div>
    </form>

    <p class="form-footnote">Avez-vous un compte ? <a href="<?= BASE_URL ?>/auth/login.php">Se connecter</a></p>
</div>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
