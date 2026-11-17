<?php
require __DIR__ . '/config.php';
require __DIR__ . '/includes/functions.php';
require_admin();

$bookings = db()->query("SELECT b.*, c.name AS category_name FROM bookings b JOIN rooms r ON r.id = b.room_id JOIN room_categories c ON c.id = r.category_id ORDER BY b.created_at DESC")->fetchAll();

$pageTitle = 'Панель управления — Сладкие сны';
require __DIR__ . '/includes/header.php';
?>
<div class="admin-head">
  <h1>Заявки на бронирование</h1>
  <a href="logout.php" class="btn btn--ghost">Выйти</a>
</div>

<?php if (!$bookings): ?><p>Заявок пока нет.</p>
<?php else: ?>
  <table class="admin-table">
    <thead><tr><th>#</th><th>Номер</th><th>Гость</th><th>Контакты</th><th>Даты</th><th>Статус</th><th>Действия</th></tr></thead>
    <tbody>
    <?php foreach ($bookings as $b): ?>
      <tr>
        <td><?= (int)$b['id'] ?></td>
        <td><?= e($b['category_name']) ?></td>
        <td><?= e($b['last_name'] . ' ' . $b['first_name']) ?></td>
        <td><a href="tel:<?= e($b['phone']) ?>"><?= e($b['phone']) ?></a><br><a href="mailto:<?= e($b['email']) ?>"><?= e($b['email']) ?></a></td>
        <td><?= e(date('d.m.Y', strtotime($b['check_in']))) ?> — <?= e(date('d.m.Y', strtotime($b['check_out']))) ?></td>
        <td>
          <span class="status status--<?= e($b['status']) ?>">
            <?= match($b['status']) { 'new' => 'Новая', 'approved' => 'Одобрена', 'rejected' => 'Отклонена', default => $b['status'] } ?>
          </span>
        </td>
        <td class="actions">
          <?php if ($b['status'] !== 'approved'): ?>
            <form method="post" action="approve.php" style="display:inline">
              <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="id" value="<?= (int)$b['id'] ?>">
              <button class="btn btn--sm btn--primary">Одобрить</button>
            </form>
          <?php endif; ?>
          <form method="post" action="delete.php" style="display:inline" onsubmit="return confirm('Удалить заявку?');">
            <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="id" value="<?= (int)$b['id'] ?>">
            <button class="btn btn--sm btn--danger">Удалить</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
<?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>