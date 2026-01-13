<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$blade = app('blade.compiler');
$content = file_get_contents('resources/views/landing.blade.php');
$compiled = $blade->compileString($content);
echo "COMPILED OUTPUT:\n";
echo $compiled;
