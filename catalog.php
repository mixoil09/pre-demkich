<?php
require __DIR__ . '/config.php';
require __DIR__ . '/includes/functions.php';

$categoryId = isset($_GET['category']) && ctype_digit((string)$_GET['category']) ? (int)$_GET['category'] : null;
$maxPrice   = isset($_GET['max_price']) && is_numeric($_GET['max_price']) ? (float)$_GET['max_price'] : null;
$minCapacity = isset($_GET['capacity']) && ctype_digit((string)$_GET['capacity']) ? (int)$_GET['capacity'] : null;

$categories = db()->query("SELECT id, name FROM room_categories ORDER BY id")->fetchAll();

$sql = "SELECT r.id, r.price_per_night, r.capacity, r.area_sqm, r.image_url, c.name AS category_name, GROUP_CONCAT(a.name SEPARATOR ', ') AS amenities
        FROM rooms r
        JOIN room_categories c ON c.id = r.category_id
        LEFT JOIN room_amenities ra ON ra.room_id = r.id
        LEFT JOIN amenities a ON a.id = ra.amenity_id
        WHERE 1=1";
$params = [];

if ($categoryId !== null) { $sql .= " AND r.category_id = :category"; $params[':category'] = $categoryId; }
if ($maxPrice !== null) { $sql .= " AND r.price_per_night <= :max_price"; $params[':max_price'] = $maxPrice; }
if ($minCapacity !== null) { $sql .= " AND r.capacity >= :capacity"; $params[':capacity'] = $minCapacity; }

$sql .= " GROUP BY r.id ORDER BY RAND() LIMIT 6";

$stmt = db()->prepare($sql);
$stmt->execute($params);
$rooms = $stmt->fetchAll();

$pageTitle = 'Каталог номеров — Сладкие сны';
require __DIR__ . '/includes/header.php';
?>
<h1>Каталог номеров</h1>

<form class="filter" method="get" action="catalog.php">
  <label>
    Категория:
    <select name="category">
      <option value="">Все</option>
      <?php foreach ($categories as $c): ?>
        <option value="<?= (int)$c['id'] ?>" <?= $categoryId === (int)$c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
      <?php endforeach; ?>
    </select>
  </label>
  <label>Цена до, ₽: <input type="number" name="max_price" min="0" step="100" value="<?= $maxPrice !== null ? e((string)$maxPrice) : '' ?>"></label>
  <label>Мест не менее: <input type="number" name="capacity" min="1" max="10" value="<?= $minCapacity !== null ? e((string)$minCapacity) : '' ?>"></label>
  <button type="submit" class="btn">Применить</button>
  <a href="catalog.php" class="btn btn--ghost">Сбросить фильтр</a>
</form>

<section class="rooms">
  <?php if (!$rooms): ?>
    <p>По выбранным условиям номера не найдены. Попробуйте изменить фильтр.</p>
  <?php else: ?>
    <?php foreach ($rooms as $room): ?>
      <article class="room">
        <img src="public/<?= e($room['image_url']) ?>" alt="<?= e($room['category_name']) ?>" class="room__img">
        <h2 class="room__title"><?= e($room['category_name']) ?></h2>
        <p class="room__price"><?= number_format((float)$room['price_per_night'], 0, '', ' ') ?> ₽ / сутки</p>
        <ul class="room__features">
          <li><?= (int)$room['capacity'] ?> мест</li>
          <li><?= e((string)$room['area_sqm']) ?> м²</li>
          <li><?= e($room['amenities'] ?? '—') ?></li>
        </ul>
        <a href="booking.php?room=<?= (int)$room['id'] ?>" class="btn btn--primary">Забронировать</a>
      </article>
    <?php endforeach; ?>
  <?php endif; ?>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>