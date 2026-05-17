<?php
/**
 * Utilidades compartidas de seguridad.
 *
 * Reúne manejo de sesión, escape de salida HTML, protección CSRF, validación
 * de método HTTP, control básico de roles y llave privada de recuperación.
 */

function iniciar_sesion_segura(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
        'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
    ]);

    session_start();
}

function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function csrf_token(): string
{
    iniciar_sesion_segura();

    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function csrf_validate(?string $token): bool
{
    iniciar_sesion_segura();

    return !empty($_SESSION['csrf_token'])
        && is_string($token)
        && hash_equals($_SESSION['csrf_token'], $token);
}

function require_post(): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        http_response_code(405);
        exit('Metodo no permitido');
    }
}

function current_user(): ?array
{
    iniciar_sesion_segura();

    return $_SESSION['usuario'] ?? null;
}

function require_role(array $roles): void
{
    $user = current_user();
    $rol = $user['rol'] ?? '';

    if (!$user || !in_array($rol, $roles, true)) {
        http_response_code(403);
        exit('No tienes permiso para acceder a esta seccion.');
    }
}

function recovery_key(): string
{
    return getenv('POS_RECOVERY_KEY') ?: 'sanramon-2026';
}
