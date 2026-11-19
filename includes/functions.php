<?php

declare(strict_types=1);

/** Échappement HTML. */
function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** URL de base de l'application (compatible serveur intégré PHP et XAMPP Apache). */
function base_url(): string
{
    static $base = null;

    if ($base !== null) {
        return $base;
    }

    $root = str_replace('\\', '/', dirname(__DIR__));
    $docRoot = str_replace('\\', '/', rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', '/\\'));

    if ($docRoot !== '' && str_starts_with($root, $docRoot)) {
        $base = rtrim(substr($root, strlen($docRoot)), '/');
    } else {
        $base = '';
    }

    return $base;
}

if (!defined('BASE_URL')) {
    define('BASE_URL', base_url());
}

function redirect(string $path): never
{
    header('Location: ' . $path);
    exit;
}

/* ------------------------------------------------------------------------- */
/* Session, utilisateur et messages                                           */
/* ------------------------------------------------------------------------- */

function is_logged_in(): bool
{
    return isset($_SESSION['user_id'], $_SESSION['username']);
}

function require_login(): void
{
    if (!is_logged_in()) {
        set_flash('error', 'Veuillez vous connecter pour accéder à cette page.');
        redirect(BASE_URL . '/auth/login.php');
    }
}

function set_flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function consume_flash(): ?array
{
    if (!isset($_SESSION['flash'])) {
        return null;
    }

    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);

    return is_array($flash) ? $flash : null;
}

function guest_token(): string
{
    if (empty($_SESSION['guest_token'])) {
        $_SESSION['guest_token'] = bin2hex(random_bytes(24));
    }

    return $_SESSION['guest_token'];
}

/* ------------------------------------------------------------------------- */
/* CSRF                                                                       */
/* ------------------------------------------------------------------------- */

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function verify_csrf(?string $token): bool
{
    return is_string($token)
        && isset($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}

/* ------------------------------------------------------------------------- */
/* Logique des anagrammes                                                      */
/* ------------------------------------------------------------------------- */

/**
 * Normalise une chaîne : minuscules, suppression des accents, des espaces,
 * des apostrophes et des tirets, puis tri des caractères.
 */
function normalize_string(string $value): string
{
    $value = mb_strtolower(trim($value), 'UTF-8');

    $transliteration = [
        'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a',
        'ç' => 'c', 'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e',
        'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i',
        'ñ' => 'n', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o',
        'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u',
        'ý' => 'y', 'ÿ' => 'y', 'œ' => 'o', 'æ' => 'a',
    ];

    $value = strtr($value, $transliteration);
    $value = preg_replace('/\p{M}+/u', '', $value) ?? $value;
    $value = preg_replace("/[\s'-]+/u", '', $value) ?? $value;

    $characters = preg_split('//u', $value, -1, PREG_SPLIT_NO_EMPTY) ?: [];
    sort($characters, SORT_STRING);

    return implode('', $characters);
}

function anagrammes(string $first, string $second): bool
{
    return normalize_string($first) === normalize_string($second);
}

/**
 * Validation d'un champ de saisie : lettres, espaces, apostrophes et tirets
 * uniquement (les chiffres ne sont pas autorisés).
 */
function validate_string_field(string $value): ?string
{
    if (trim($value) === '') {
        return 'Ce champ est obligatoire.';
    }

    if (mb_strlen($value) > 255) {
        return 'La chaîne ne doit pas dépasser 255 caractères.';
    }

    if (!preg_match("/^[\p{L}\s'-]+$/u", $value)) {
        return 'Caractères non autorisés : utilisez uniquement des lettres, des espaces, des apostrophes ou des tirets.';
    }

    return null;
}
