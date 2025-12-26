<?php
$host = '127.0.0.1';
$port = 3306;
$user = 'root';
$pass = '';
$dbname = 'cas_db';
try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $user, $pass, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
    $stmt = $pdo->query("SELECT id, client_id, service_type, duty_type, service_date, created_at, submitted_at, status FROM bookings ORDER BY id DESC LIMIT 50");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['count'=>count($rows),'rows'=>$rows], JSON_PRETTY_PRINT);
} catch (Exception $e) {
    echo json_encode(['error'=>$e->getMessage()]);
}
