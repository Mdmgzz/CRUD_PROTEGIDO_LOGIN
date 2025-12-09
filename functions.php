<?php

const CSV_FILE = __DIR__ . '/usuarios.csv';


// Lee el archivo CSV y devuelve un array con todos los usuarios
function read_users(): array {
    if (!file_exists(CSV_FILE)) return [];
    $out = [];
    $f = fopen(CSV_FILE, 'r');
    if (!$f) return [];
    if (flock($f, LOCK_SH)) {
        while (($row = fgetcsv($f)) !== false) {
            // esperamos columnas: id,nombre,email,rol
            if (count($row) < 5) continue;
            $out[] = [
                'id' => $row[0],
                'nombre' => $row[1],
                'email' => $row[2],
                'rol' => $row[3],
                'fecha_alta' => $row[4],
            ];
        }
        flock($f, LOCK_UN);
    }
    fclose($f);
    return $out;
}

// Escribe el array de usuarios en el archivo CSV para añadir el  nuevo usuario
function write_users(array $users): bool {
    $tmp = tempnam(sys_get_temp_dir(), 'u');
    $h = fopen($tmp, 'w');
    if (!$h) return false;
    foreach ($users as $u) {
        fputcsv($h, [$u['id'], $u['nombre'], $u['email'], $u['rol'], $u['fecha_alta']]);
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

// Devuelve el siguiente ID disponible para asignarselo al nuevo usuario
function next_id(): int {
    $max = 0;
    foreach (read_users() as $u) {
        $id = (int)$u['id'];
        if ($id > $max) $max = $id;
    }
    return $max + 1;
}

// Devuelve el usuario con el ID dado, o null si no existe
function find_user($id) {
    foreach (read_users() as $u) {
        if ((int)$u['id'] === (int)$id) return $u;
    }
    return null;
}


function h($s) { return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }

// Valida los datos de entrada del usuario
function validate_user_input(array $data): array {
    $errors = [];
    $name = trim($data['nombre'] ?? '');
    $email = trim($data['email'] ?? '');
    $role = trim($data['rol'] ?? '');

    // si hay errores, los añadimos al array $errors
    // si no, devolvemos un array vacío
    if ($name === '') $errors['nombre'] = 'Nombre obligatorio';
    if ($email === '') $errors['email'] = 'Email obligatorio';
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Email no válido';
    if ($role === '') $errors['rol'] = 'Rol obligatorio';


    return $errors; 
}
