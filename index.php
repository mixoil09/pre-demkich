<?php
require __DIR__ . '/config.php';
require __DIR__ . '/includes/functions.php';

$pageTitle = 'Сладкие сны — бронирование номеров';
require __DIR__ . '/includes/header.php';
?>

<!-- Закрываем контейнер перед баннером, чтобы он был на всю ширину -->
</main> 

<section class="hero-banner">
  <div class="hero-banner__overlay">
    <h1 class="hero-banner__title">Добро пожаловать в гостиницу «Сладкие сны»</h1>
    <p class="hero-banner__subtitle">Уютные номера на любой вкус: стандарт, студия и люкс.<br>Подарите себе незабываемый отдых.</p>
    <a href="catalog.php" class="btn btn--primary btn--lg">Перейти в каталог</a>
  </div>
</section>

<!-- Снова открываем контейнер для остального контента (если он будет) -->
<main class="container">

<?php require __DIR__ . '/includes/footer.php'; ?>