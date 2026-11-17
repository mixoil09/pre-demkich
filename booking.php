<?php
require __DIR__ . '/config.php';
require __DIR__ . '/includes/functions.php';

$roomId = isset($_GET['room']) && ctype_digit((string)$_GET['room']) ? (int)$_GET['room'] : 0;

$stmt = db()->prepare("SELECT r.*, c.name AS category_name FROM rooms r JOIN room_categories c ON c.id = r.category_id WHERE r.id = :id");
$stmt->execute([':id' => $roomId]);
$room = $stmt->fetch();

$errors = [];
$old    = ['first_name'=>'','last_name'=>'','phone'=>'','email'=>'','check_in'=>'','check_out'=>''];
$success = false;
$bookedPeriods = $room ? get_booked_periods((int)$room['id']) : [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check($_POST['csrf'] ?? null)) {
        $errors['_global'] = 'Ошибка безопасности. Обновите страницу и попробуйте снова.';
    } elseif (!$room) {
        $errors['_global'] = 'Номер не найден.';
    } else {
        $old = [
            'first_name' => (string)($_POST['first_name'] ?? ''),
            'last_name'  => (string)($_POST['last_name'] ?? ''),
            'phone'      => (string)($_POST['phone'] ?? ''),
            'email'      => (string)($_POST['email'] ?? ''),
            'check_in'   => (string)($_POST['check_in'] ?? ''),
            'check_out'  => (string)($_POST['check_out'] ?? ''),
        ];
        [$errors, $clean] = validate_booking($old);

        if (!$errors && !is_room_available($roomId, $clean['check_in'], $clean['check_out'])) {
            $errors['check_in'] = 'На выбранные даты номер уже забронирован. Выберите другой период.';
        }

        if (!$errors) {
            $sql = "INSERT INTO bookings (room_id, first_name, last_name, phone, email, check_in, check_out, status) VALUES (:room, :fn, :ln, :phone, :email, :in, :out, 'new')";
            db()->prepare($sql)->execute([
                ':room'  => $roomId, ':fn'    => $clean['first_name'], ':ln'    => $clean['last_name'],
                ':phone' => $clean['phone'], ':email' => $clean['email'], ':in'    => $clean['check_in'], ':out'   => $clean['check_out'],
            ]);
            $success = true;
            $old = ['first_name'=>'','last_name'=>'','phone'=>'','email'=>'','check_in'=>'','check_out'=>''];
        }
    }
}

$pageTitle = 'Бронирование номера — Сладкие сны';
require __DIR__ . '/includes/header.php';
?>
<h1>Бронирование номера</h1>

<?php if (!$room): ?>
  <p class="error-box">Номер не найден. Вернитесь в <a href="catalog.php">каталог</a>.</p>
<?php else: ?>
  <p class="room-info">Номер: <strong><?= e($room['category_name']) ?></strong> — <?= number_format((float)$room['price_per_night'], 0, '', ' ') ?> ₽ / сутки, <?= (int)$room['capacity'] ?> мест, <?= e((string)$room['area_sqm']) ?> м²</p>

  <?php if ($success): ?><div class="success">Заявка успешно отправлена! Мы свяжемся с вами для уточнения данных.</div><?php endif; ?>
  <?php if (!empty($errors['_global'])): ?><div class="error-box"><?= e($errors['_global']) ?></div><?php endif; ?>

  <form id="bookingForm" class="booking-form" method="post" action="booking.php?room=<?= (int)$roomId ?>" novalidate>
    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
    <?php
    $fields = [
        'first_name' => ['Имя *', 'text'], 'last_name' => ['Фамилия *', 'text'],
        'phone' => ['Телефон * (+7(XXX)XXX-XX-XX)', 'tel'], 'email' => ['E-mail *', 'email'],
        'check_in' => ['Дата заезда * (ДД.ММ.ГГГГ)', 'text'], 'check_out' => ['Дата выезда * (ДД.ММ.ГГГГ)', 'text'],
    ];
    foreach ($fields as $name => [$label, $type]):
        $hasErr = !empty($errors[$name]);
    ?>
      <div class="form-group">
        <label for="<?= e($name) ?>"><?= e($label) ?></label>
        <input type="<?= e($type) ?>" id="<?= e($name) ?>" name="<?= e($name) ?>" value="<?= e($old[$name] ?? '') ?>" class="<?= $hasErr ? 'invalid' : '' ?>">
        <?php if ($hasErr): ?><small class="error"><?= e($errors[$name]) ?></small><?php endif; ?>
      </div>
    <?php endforeach; ?>
    <button type="submit" class="btn btn--primary">Отправить заявку</button>
  </form>

  <script>
    window.BOOKED_PERIODS = <?= json_encode($bookedPeriods, JSON_UNESCAPED_UNICODE) ?>;
    window.ROOM_ID = <?= (int)$roomId ?>;
  </script>
  <script src="public/js/booking-calendar.js"></script>
<?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>