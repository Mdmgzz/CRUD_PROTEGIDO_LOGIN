<?php
require 'functions.php';
$id = $_GET['id'] ?? null;
if (!$id) { header('Location: user_index.php'); exit; }

$all = read_users();
foreach ($all as $k => $u) {
    if ((int)$u['id'] === (int)$id) {
        array_splice($all, $k, 1);
        break;
    }
}
write_users(array_values($all));
header('Location: user_index.php');
exit;
