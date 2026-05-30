<?php
// Venom Hub V4.2 - Core Configuration
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// admin123 şifresinin güvenli MD5 karşılığıdır.
define('ADMIN_MD5', '0192023a7bbd73250516f069df18b500'); 

define('USERS_FILE', 'logs/users.txt');
define('LOGS_DIR', 'logs/user_logs/');

// Gerekli klasörleri oluştur
if (!is_dir('logs')) { mkdir('logs', 0777, true); }
if (!is_dir(LOGS_DIR)) { mkdir(LOGS_DIR, 0777, true); }

function register_user($username, $password) {
    if (empty($username) || empty($password)) return "Alanlar boş bırakılamaz.";
    $username = preg_replace("/[^a-zA-Z0-9-_]/", "", $username);
    
    if (file_exists(USERS_FILE)) {
        $users = file(USERS_FILE, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($users as $user) {
            list($stored_user, ) = explode('|', $user);
            if (strtolower($stored_user) === strtolower($username)) {
                return "Bu kullanıcı adı zaten alınmış.";
            }
        }
    }
    
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    file_put_contents(USERS_FILE, "$username|$hashed_password" . PHP_EOL, FILE_APPEND);
    return true;
}

function login_user($username, $password) {
    $username = preg_replace("/[^a-zA-Z0-9-_]/", "", $username);
    if (file_exists(USERS_FILE)) {
        $users = file(USERS_FILE, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($users as $user) {
            list($stored_user, $stored_hash) = explode('|', $user);
            if (strtolower($stored_user) === strtolower($username) && password_verify($password, $stored_hash)) {
                $_SESSION['user'] = $stored_user;
                return true;
            }
        }
    }
    return "Kullanıcı adı veya şifre hatalı.";
}

function log_checked_cards($username, $cards_string) {
    if (empty($cards_string)) return;
    $username = preg_replace("/[^a-zA-Z0-9-_]/", "", $username);
    $user_file = LOGS_DIR . $username . ".txt";
    
    $date = date('Y-m-d H:i:s');
    $log_entry = "--- OPERASYON ZAMANI: $date ---\n" . trim($cards_string) . "\n\n";
    file_put_contents($user_file, $log_entry, FILE_APPEND);
}
?>
