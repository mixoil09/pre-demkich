<?php
require __DIR__ . '/config.php';
require __DIR__ . '/includes/functions.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_check($_POST['csrf'] ?? null)) { http_response_code(400); exit('Bad request'); }
$id = (int)($_POST['id'] ?? 0);
if ($id > 0) { db()->prepare("UPDATE bookings SET status='approved' WHERE id=:id")->execute([':id' => $id]); }
header('Location: admin.php');
exit;