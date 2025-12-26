<?php
$pdo = new PDO('mysql:host=localhost;dbname=cas_db', 'root', '');

echo "=== BOOKINGS WITH ASSIGNMENT STATUS ===\n";
$stmt = $pdo->query('
    SELECT b.id, b.assignment_status, COUNT(ba.id) as assignment_count 
    FROM bookings b 
    LEFT JOIN booking_assignments ba ON b.id = ba.booking_id 
    GROUP BY b.id 
    ORDER BY b.id DESC 
    LIMIT 15
');
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    echo "Booking {$row['id']}: status={$row['assignment_status']}, actual_assignments={$row['assignment_count']}\n";
}

echo "\n=== BOOKING ASSIGNMENTS ===\n";
$stmt = $pdo->query('SELECT * FROM booking_assignments ORDER BY id DESC LIMIT 5');
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

echo "\n=== FIX MISMATCHED STATUSES ===\n";
// Update bookings that have assignments but show as unassigned
$pdo->exec("
    UPDATE bookings b 
    SET assignment_status = 'assigned' 
    WHERE EXISTS (SELECT 1 FROM booking_assignments ba WHERE ba.booking_id = b.id)
    AND b.assignment_status = 'unassigned'
");
echo "Fixed bookings with assignments but showing as unassigned\n";

// Update bookings that have no assignments but show as assigned
$pdo->exec("
    UPDATE bookings b 
    SET assignment_status = 'unassigned' 
    WHERE NOT EXISTS (SELECT 1 FROM booking_assignments ba WHERE ba.booking_id = b.id)
    AND b.assignment_status = 'assigned'
");
echo "Fixed bookings without assignments but showing as assigned\n";

echo "\n=== UPDATED BOOKING STATUSES ===\n";
$stmt = $pdo->query('
    SELECT b.id, b.assignment_status, COUNT(ba.id) as assignment_count 
    FROM bookings b 
    LEFT JOIN booking_assignments ba ON b.id = ba.booking_id 
    GROUP BY b.id 
    ORDER BY b.id DESC 
    LIMIT 15
');
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    echo "Booking {$row['id']}: status={$row['assignment_status']}, actual_assignments={$row['assignment_count']}\n";
}
