<?php
const CSV_FILE = __DIR__ . '/usuarios.csv';

function read_users(): array {
    if (!file_exists(CSV_FILE)) return [];
    $out = [];
    $f = fopen(CSV_FILE, 'r');
    if (!$f) return [];
    if (flock($f, LOCK_SH)) {
        while (($row = fgetcsv($f)) !== false) {
            // ahora esperamos columnas: id,nombre,email,rol,fecha_alta,password_hash
            if (count($row) < 5) continue; // al menos hasta fecha_alta
            $out[] = [
                'id' => $row[0] ?? '',
                'nombre' => $row[1] ?? '',
                'email' => $row[2] ?? '',
                'rol' => $row[3] ?? '',
                'fecha_alta' => $row[4] ?? '',
                'password_hash' => $row[5] ?? '',
            ];
        }
        flock($f, LOCK_UN);
    }
    fclose($f);
    return $out;
}

function write_users(array $users): bool {
    $tmp = tempnam(sys_get_temp_dir(), 'u');
    $h = fopen($tmp, 'w');
    if (!$h) return false;
    foreach ($users as $u) {
        // asegúrate de mantener el orden correcto
        fputcsv($h, [
            $u['id'],
            $u['nombre'],
            $u['email'],
            $u['rol'],
            $u['fecha_alta'],
            $u['password_hash'] ?? '',
        ]);
    }
    fclose($h);

    $out = fopen(CSV_FILE, 'c+');
    if (!$out) { unlink($tmp); return false; }
    if (!flock($out, LOCK_EX)) { fclose($out); unlink($tmp); return false; }
    ftruncate($out, 0);
    rewind($out);
    fwrite($out, file_get_contents($tmp));
    fflush($out);
    flock($out, LOCK_UN);
    fclose($out);
    unlink($tmp);
    return true;
}

function next_id(): int {
    $max = 0;
    foreach (read_users() as $u) {
        $id = (int)$u['id'];
        if ($id > $max) $max = $id;
    }
    return $max + 1;
}

function find_user($id) {
    foreach (read_users() as $u) {
        if ((int)$u['id'] === (int)$id) return $u;
    }
    return null;
}

function find_user_by_email(string $email) {
    foreach (read_users() as $u) {
        if (strcasecmp($u['email'], $email) === 0) return $u;
    }
    return null;
}

/**
 * Verifica credenciales: devuelve el usuario si ok, false si falla
 */
function verify_credentials(string $email, string $password) {
    $u = find_user_by_email($email);
    if (!$u) return false;
    if (empty($u['password_hash'])) return false;
    if (password_verify($password, $u['password_hash'])) return $u;
    return false;
}

function h($s) { return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }

function validate_user_input(array $data): array {
    $errors = [];
    $name = trim($data['nombre'] ?? '');
    $email = trim($data['email'] ?? '');
    $role = trim($data['rol'] ?? '');

    if ($name === '') $errors['nombre'] = 'Nombre obligatorio';
    if ($email === '') $errors['email'] = 'Email obligatorio';
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Email no válido';
    if ($role === '') $errors['rol'] = 'Rol obligatorio';

    return $errors;
}
