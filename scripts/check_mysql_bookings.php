<?php
$host = '127.0.0.1';
$port = 3306;
$user = 'root';
$pass = '';
try {
    $pdo = new PDO("mysql:host=$host;port=$port", $user, $pass, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
    $dbs = $pdo->query('SHOW DATABASES')->fetchAll(PDO::FETCH_COLUMN);
    $candidates = array();
    foreach ($dbs as $db) {
        try {
            $stmt = $pdo->prepare("SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'bookings'");
            $stmt->execute([$db]);
            $t = $stmt->fetchAll(PDO::FETCH_COLUMN);
            if (count($t)) $candidates[] = $db;
        } catch (Exception $e) {
            // ignore
        }
    }
    echo json_encode(array('databases_with_bookings' => $candidates));
} catch (Exception $e) {
    echo json_encode(array('error' => $e->getMessage()));
}
