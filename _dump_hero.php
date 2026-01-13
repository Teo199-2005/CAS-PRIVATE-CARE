<?php
$path = __DIR__ . '/resources/views/landing.blade.php';
$s = file_get_contents($path);
$pos = strpos($s, 'btn-caregiver');
echo "PATH=$path\n";
echo "POS=$pos\n";
echo substr($s, max(0,$pos-200), 700);
