<?php
// Demarrage de la session si pas deja fait

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'domain'   => '',
        'secure'   => false,
        'httponly'  => true,
        'samesite'  => 'Strict'
    ]);
    session_start();
}

// regenerer l'id de session (securite)
function regenerateSession()
{
    session_regenerate_id(true);
}

// verifier si l'utilisateur est connecte
function isLoggedIn()
{
    return isset($_SESSION['user_id']) && isset($_SESSION['user_pseudo']);
}

function getUserId()
{
    return isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
}

function getUserRole()
{
    return $_SESSION['user_role'] ?? null;
}

// verifie si l'utilisateur a un role specifique
function hasRole($role)
{
    return getUserRole() === $role;
}
