<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$pageTitle = 'Vérificateur d’anagrammes';

$string1 = '';
$string2 = '';
$result = null;
$errors = [];

if (isset($_GET['logged_out'])) {
    set_flash('success', 'Vous êtes déconnecté. À bientôt !');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        $errors['form'] = 'Formulaire invalide ou session expirée. Veuillez réessayer.';
    } else {
        $string1 = trim((string) ($_POST['string1'] ?? ''));
        $string2 = trim((string) ($_POST['string2'] ?? ''));

        $errors['string1'] = validate_string_field($string1);
        $errors['string2'] = validate_string_field($string2);
        $errors = array_filter($errors, static fn ($message) => $message !== null);

        if ($errors === []) {
            $result = anagrammes($string1, $string2);

            try {
                $statement = getPdo()->prepare(
                    'INSERT INTO verifications (user_id, guest_token, string1, string2, is_anagram)
                     VALUES (:user_id, :guest_token, :string1, :string2, :is_anagram)'
                );
                $statement->execute([
                    'user_id'     => is_logged_in() ? (int) $_SESSION['user_id'] : null,
                    'guest_token' => is_logged_in() ? null : guest_token(),
                    'string1'     => $string1,
                    'string2'     => $string2,
                    'is_anagram'  => $result ? 1 : 0,
                ]);
            } catch (Throwable $exception) {
                $errors['form'] = 'La vérification a été effectuée mais n’a pas pu être enregistrée.';
            }
        }
    }
}

/* Historique affiché sous le formulaire lorsque l'utilisateur est connecté. */
$rows = [];
if (is_logged_in()) {
    try {
        $statement = getPdo()->prepare(
            'SELECT string1, string2, is_anagram, created_at
             FROM verifications
             WHERE user_id = :user_id
             ORDER BY id ASC'
        );
        $statement->execute(['user_id' => (int) $_SESSION['user_id']]);
        $rows = $statement->fetchAll();
    } catch (Throwable $exception) {
        $rows = [];
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="panel">
    <h1>Vérifier des anagrammes</h1>

    <?php if (isset($errors['form'])): ?>
        <div class="alert alert-error" role="alert"><?= e($errors['form']) ?></div>
    <?php endif; ?>

    <form id="verification-form" class="verification-form" method="post" action="<?= BASE_URL ?>/index.php" novalidate>
        <?= csrf_field() ?>

        <div class="fields">
            <?php foreach ([1 => $string1, 2 => $string2] as $number => $value): ?>
                <div class="field">
                    <label for="string<?= $number ?>">String <?= $number ?></label>
                    <div class="input-wrap">
                        <input
                            id="string<?= $number ?>"
                            name="string<?= $number ?>"
                            type="text"
                            maxlength="255"
                            autocomplete="off"
                            class="text-input <?= $result !== null ? ($result ? 'is-ok' : 'is-ko') : '' ?>"
                            value="<?= e($value) ?>"
                        >
                        <button class="clear-btn" type="button" aria-label="Effacer le champ">×</button>
                    </div>
                    <?php if (isset($errors['string' . $number])): ?>
                        <ul class="field-errors"><li><?= e($errors['string' . $number]) ?></li></ul>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </form>

    <?php if (!empty($errors['string1']) || !empty($errors['string2'])): ?>
        <div class="error-box" role="alert">
            • Caractères non autorisés : utilisez uniquement des lettres, des espaces, des apostrophes ou des tirets.
        </div>
    <?php endif; ?>

    <?php if ($result !== null): ?>
        <div class="result <?= $result ? 'result-ok' : 'result-ko' ?>" role="status">
            <?= $result ? 'Les deux chaînes sont des anagrammes.' : 'Les deux chaînes ne sont pas des anagrammes.' ?>
        </div>
    <?php endif; ?>

    <div class="form-actions">
        <button class="btn" type="submit" form="verification-form">Soumettre</button>
    </div>
</div>

<?php if (is_logged_in()): ?>
    <section class="history" id="historique">
        <h2>Historique</h2>
        <?php if ($rows === []): ?>
            <p class="empty-state">Aucune vérification pour le moment.</p>
        <?php else: ?>
            <table>
                <colgroup>
                    <col style="width:40px">
                    <col style="width:85px">
                    <col style="width:86px">
                    <col style="width:85px">
                    <col style="width:165px">
                </colgroup>
                <thead>
                <tr>
                    <th>N°</th>
                    <th>String 1</th>
                    <th>String 2</th>
                    <th>Résultat</th>
                    <th>Date</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($rows as $index => $row): ?>
                    <tr>
                        <td><?= $index ?></td>
                        <td><?= e($row['string1']) ?></td>
                        <td><?= e($row['string2']) ?></td>
                        <td><?= (int) $row['is_anagram'] === 1 ? 'Oui' : 'Non' ?></td>
                        <td><?= e(date('Y-m-d', strtotime((string) $row['created_at']))) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>
<?php endif; ?>

<script>
document.querySelectorAll('.field').forEach(function (field) {
    var input = field.querySelector('.text-input');
    var button = field.querySelector('.clear-btn');

    function update() {
        button.style.display = input.value.length > 0 ? 'block' : 'none';
    }

    button.addEventListener('click', function () {
        input.value = '';
        update();
        input.focus();
    });
    input.addEventListener('input', update);
    update();
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
