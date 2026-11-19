<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';

$pageTitle = 'Connexion';
$showFooter = true;
$pseudo = '';
$errors = [];

if (is_logged_in()) {
    redirect(BASE_URL . '/index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        $errors['form'] = 'Formulaire invalide ou session expirée. Veuillez réessayer.';
    } else {
        $pseudo = trim((string) ($_POST['pseudo'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        if ($pseudo === '' || $password === '') {
            $errors['form'] = 'Veuillez renseigner le pseudonyme et le mot de passe.';
        } else {
            try {
                $statement = getPdo()->prepare(
                    'SELECT id, pseudo, password_hash FROM users WHERE pseudo = :pseudo'
                );
                $statement->execute(['pseudo' => $pseudo]);
                $user = $statement->fetch();

                if ($user === false || !password_verify($password, $user['password_hash'])) {
                    $errors['form'] = 'Pseudonyme ou mot de passe incorrect.';
                } else {
                    $guestToken = $_SESSION['guest_token'] ?? null;

                    session_regenerate_id(true);

                    $_SESSION['user_id'] = (int) $user['id'];
                    $_SESSION['username'] = $user['pseudo'];

                    if (is_string($guestToken) && $guestToken !== '') {
                        $migrate = getPdo()->prepare(
                            'UPDATE verifications
                             SET user_id = :user_id, guest_token = NULL
                             WHERE guest_token = :guest_token AND user_id IS NULL'
                        );
                        $migrate->execute([
                            'user_id'     => $_SESSION['user_id'],
                            'guest_token' => $guestToken,
                        ]);
                    }
                    unset($_SESSION['guest_token']);

                    set_flash('success', 'Bienvenue ' . $user['pseudo'] . ' !');
                    redirect(BASE_URL . '/index.php');
                }
            } catch (Throwable $exception) {
                $errors['form'] = 'Connexion impossible pour le moment. Réessayez plus tard.';
            }
        }
    }
}

require_once dirname(__DIR__) . '/includes/header.php';
?>

<div class="auth-wrap">
    <h1>Connexion</h1>

    <?php if (isset($errors['form'])): ?>
        <div class="alert alert-error" role="alert"><?= e($errors['form']) ?></div>
    <?php endif; ?>

    <form class="auth-form" method="post" action="<?= BASE_URL ?>/auth/login.php" novalidate>
        <?= csrf_field() ?>

        <?php foreach ([
            'pseudo'   => ['Pseudo', 'text', 'username'],
            'password' => ['Mot de passe', 'password', 'current-password'],
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
                        class="text-input"
                        value="<?= $name === 'pseudo' ? e($pseudo) : '' ?>"
                        required
                    >
                </div>
            </div>
        <?php endforeach; ?>

        <div class="auth-actions">
            <button class="btn" type="submit">Se connecter</button>
        </div>
    </form>

    <p class="form-footnote">Avez-vous un compte ? <a href="<?= BASE_URL ?>/auth/register.php">S'inscrire</a></p>
</div>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
