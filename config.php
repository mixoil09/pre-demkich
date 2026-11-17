<?php
declare(strict_types=1);

// Настройки базы данных (ЗАМЕНИТЕ НА СВОИ!)
const DB_HOST = 'sql305.infinityfree.com'; // Ваш хост
const DB_NAME = 'if0_43461341_predemkich'; // Имя базы
const DB_USER = 'if0_43461341';            // Имя пользователя
const DB_PASS = 'Mishka8857';              // Пароль

function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', DB_HOST, DB_NAME);
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    }
    return $pdo;
}