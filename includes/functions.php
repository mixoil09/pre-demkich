<?php
declare(strict_types=1);

function e(?string $s): string {
    return htmlspecialchars($s ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function session_boot(): void {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
}

function csrf_token(): string {
    session_boot();
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_check(?string $token): bool {
    session_boot();
    return is_string($token) && hash_equals($_SESSION['csrf'] ?? '', $token);
}

function validate_booking(array $input): array {
    $errors = [];
    $clean  = [];
    $nameRe  = '/^[А-Яа-яЁё\s.\-]+$/u';
    $phoneRe = '/^\+7\(\d{3}\)\d{3}-\d{2}-\d{2}$/';

    $firstName = trim((string)($input['first_name'] ?? ''));
    if ($firstName === '') $errors['first_name'] = 'Укажите имя.';
    elseif (!preg_match($nameRe, $firstName)) $errors['first_name'] = 'Имя: только кириллица, пробелы, точки, тире.';
    else $clean['first_name'] = $firstName;

    $lastName = trim((string)($input['last_name'] ?? ''));
    if ($lastName === '') $errors['last_name'] = 'Укажите фамилию.';
    elseif (!preg_match($nameRe, $lastName)) $errors['last_name'] = 'Фамилия: только кириллица, пробелы, точки, тире.';
    else $clean['last_name'] = $lastName;

    $phone = trim((string)($input['phone'] ?? ''));
    if ($phone === '') $errors['phone'] = 'Укажите телефон.';
    elseif (!preg_match($phoneRe, $phone)) $errors['phone'] = 'Телефон в формате +7(XXX)XXX-XX-XX.';
    else $clean['phone'] = $phone;

    $email = trim((string)($input['email'] ?? ''));
    if ($email === '') $errors['email'] = 'Укажите e-mail.';
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Некорректный e-mail.';
    else $clean['email'] = $email;

    $toDate = static function (string $s): ?DateTimeImmutable {
        $dt = DateTimeImmutable::createFromFormat('!d.m.Y', $s);
        return ($dt && $dt->format('d.m.Y') === $s) ? $dt : null;
    };

    $checkIn  = $toDate(trim((string)($input['check_in'] ?? '')));
    $checkOut = $toDate(trim((string)($input['check_out'] ?? '')));
    $today    = new DateTimeImmutable('today');

    if (!$checkIn) $errors['check_in'] = 'Дата заезда в формате ДД.ММ.ГГГГ.';
    elseif ($checkIn < $today) $errors['check_in'] = 'Дата заезда не раньше сегодняшней.';
    else $clean['check_in'] = $checkIn->format('Y-m-d');

    if (!$checkOut) $errors['check_out'] = 'Дата выезда в формате ДД.ММ.ГГГГ.';
    elseif ($checkIn && $checkOut <= $checkIn) $errors['check_out'] = 'Дата выезда должна быть позже даты заезда.';
    else $clean['check_out'] = $checkOut->format('Y-m-d');

    return [$errors, $clean];
}

function is_room_available(int $roomId, string $checkIn, string $checkOut): bool {
    $sql = "SELECT COUNT(*) FROM bookings
            WHERE room_id = :room AND status = 'approved'
              AND NOT (check_out <= :in OR check_in >= :out)";
    $stmt = db()->prepare($sql);
    $stmt->execute([':room' => $roomId, ':in' => $checkIn, ':out' => $checkOut]);
    return (int)$stmt->fetchColumn() === 0;
}

function get_booked_periods(int $roomId): array {
    $stmt = db()->prepare("SELECT check_in, check_out FROM bookings WHERE room_id = :room AND status = 'approved' ORDER BY check_in");
    $stmt->execute([':room' => $roomId]);
    return $stmt->fetchAll();
}

function is_admin(): bool {
    session_boot();
    return !empty($_SESSION['admin']);
}

function require_admin(): void {
    if (!is_admin()) {
        header('Location: login.php');
        exit;
    }
}