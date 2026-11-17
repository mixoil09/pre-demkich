<?php
require __DIR__ . '/includes/functions.php';
session_boot();
session_destroy();
header('Location: login.php');
exit;