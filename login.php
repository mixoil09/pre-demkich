<?php
require __DIR__ . '/config.php';
require __DIR__ . '/includes/functions.php';
session_boot();

const ADMIN_LOGIN = 'hotel123';
const ADMIN_HASH  = '$2y$12$o.SXQEQfQncihrpWrrL2iO.T2cNL/H.rszR3Na7sN7m3By6qADUES'; // Пароль: adminHotel

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check($_POST['csrf'] ?? null)) {
        $error = 'Ошибка безопасности. Обновите страницу.';
    } else {
        $login = trim((string)($_POST['login'] ?? ''));
        $pass  = (string)($_POST['password'] ?? '');
        if ($login === ADMIN_LOGIN && password_verify($pass, ADMIN_HASH)) {
            session_regenerate_id(true);
            $_SESSION['admin'] = true;
            header('Location: admin.php');
            exit;
        }
        $error = 'Неверный логин или пароль.';
    }
}

$pageTitle = 'Вход администратора — Сладкие сны';
require __DIR__ . '/includes/header.php';
?>
<h1>Вход администратора</h1>
<?php if ($error): ?><div class="error-box"><?= e($error) ?></div><?php endif; ?>
<form method="post" class="booking-form" style="max-width:400px">
  <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
  <div class="form-group"><label for="login">Логин</label><input type="text" id="login" name="login" required autofocus></div>
  <div class="form-group"><label for="password">Пароль</label><input type="password" id="password" name="password" required></div>
  <button type="submit" class="btn btn--primary">Войти</button>
</form>
<?php require __DIR__ . '/includes/footer.php'; ?>