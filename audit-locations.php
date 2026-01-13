<?php
/**
 * Location Data Audit Script
 * Checks consistency of location fields across the website
 */

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

echo "============================================\n";
echo "LOCATION DATA AUDIT REPORT\n";
echo "============================================\n\n";

// 1. Database Schema Check
echo "1. DATABASE SCHEMA CHECK\n";
echo "------------------------\n";

$tables = ['users', 'bookings'];
foreach ($tables as $table) {
    if (Schema::hasTable($table)) {
        $columns = Schema::getColumnListing($table);
        $locationColumns = array_filter($columns, function($col) {
            return in_array($col, ['state', 'county', 'borough', 'city', 'zip_code', 'zipcode', 'address']);
        });
        echo "$table table:\n";
        foreach ($locationColumns as $col) {
            echo "  - $col ✓\n";
        }
        echo "\n";
    }
}

// 2. Check distinct values in database
echo "\n2. DISTINCT LOCATION VALUES IN DATABASE\n";
echo "----------------------------------------\n";

// States
$states = DB::table('users')->distinct()->pluck('state')->filter()->sort()->values();
echo "States in users table (" . $states->count() . "):\n";
foreach ($states as $state) {
    echo "  - $state\n";
}

// Counties
$counties = DB::table('users')->distinct()->pluck('county')->filter()->sort()->values();
echo "\nCounties in users table (" . $counties->count() . "):\n";
foreach ($counties->take(20) as $county) {
    echo "  - $county\n";
}
if ($counties->count() > 20) {
    echo "  ... and " . ($counties->count() - 20) . " more\n";
}

// Boroughs
$boroughs = DB::table('users')->distinct()->pluck('borough')->filter()->sort()->values();
echo "\nBoroughs in users table (" . $boroughs->count() . "):\n";
foreach ($boroughs as $borough) {
    echo "  - $borough\n";
}

// Check bookings table
if (Schema::hasTable('bookings')) {
    $bookingBoroughs = DB::table('bookings')->distinct()->pluck('borough')->filter()->sort()->values();
    echo "\nBoroughs in bookings table (" . $bookingBoroughs->count() . "):\n";
    foreach ($bookingBoroughs as $borough) {
        echo "  - $borough\n";
    }
    
    $bookingCities = DB::table('bookings')->distinct()->pluck('city')->filter()->sort()->values();
    echo "\nCities in bookings table (" . $bookingCities->count() . "):\n";
    foreach ($bookingCities->take(15) as $city) {
        echo "  - $city\n";
    }
    if ($bookingCities->count() > 15) {
        echo "  ... and " . ($bookingCities->count() - 15) . " more\n";
    }
}

// 3. Check for inconsistencies
echo "\n\n3. POTENTIAL ISSUES\n";
echo "-------------------\n";

// Check for non-NY states
$nonNYStates = DB::table('users')->where('state', '!=', 'NY')->where('state', '!=', 'New York')->whereNotNull('state')->distinct()->pluck('state');
if ($nonNYStates->count() > 0) {
    echo "⚠️  Non-NY states found: " . $nonNYStates->implode(', ') . "\n";
} else {
    echo "✓ All states are NY or New York\n";
}

// Check for invalid boroughs (not in NYC 5 boroughs)
$validBoroughs = ['Manhattan', 'Brooklyn', 'Queens', 'Bronx', 'Staten Island', 'The Bronx'];
$invalidBoroughs = $boroughs->filter(function($b) use ($validBoroughs) {
    return !in_array($b, $validBoroughs) && !empty($b);
});
if ($invalidBoroughs->count() > 0) {
    echo "⚠️  Non-standard boroughs found: " . $invalidBoroughs->implode(', ') . "\n";
} else {
    echo "✓ All boroughs are valid NYC boroughs\n";
}

// Check for zipcode field name inconsistencies
echo "\nField Naming Check:\n";
$hasZipCode = Schema::hasColumn('users', 'zip_code');
$hasZipcode = Schema::hasColumn('users', 'zipcode');
echo "  users.zip_code: " . ($hasZipCode ? '✓ exists' : '✗ missing') . "\n";
echo "  users.zipcode: " . ($hasZipcode ? '⚠️ exists (should use zip_code)' : '✓ not used') . "\n";

if (Schema::hasTable('bookings')) {
    $bHasZipCode = Schema::hasColumn('bookings', 'zip_code');
    $bHasZipcode = Schema::hasColumn('bookings', 'zipcode');
    echo "  bookings.zip_code: " . ($bHasZipCode ? '✓ exists' : '✗ missing') . "\n";
    echo "  bookings.zipcode: " . ($bHasZipcode ? '⚠️ exists (should use zip_code)' : '✓ not used') . "\n";
}

echo "\n\n4. NY COUNTIES CHECK\n";
echo "--------------------\n";

// List of all 62 NY counties
$validNYCounties = [
    'Albany', 'Allegany', 'Bronx', 'Broome', 'Cattaraugus', 'Cayuga', 'Chautauqua',
    'Chemung', 'Chenango', 'Clinton', 'Columbia', 'Cortland', 'Delaware', 'Dutchess',
    'Erie', 'Essex', 'Franklin', 'Fulton', 'Genesee', 'Greene', 'Hamilton', 'Herkimer',
    'Jefferson', 'Kings', 'Lewis', 'Livingston', 'Madison', 'Monroe', 'Montgomery',
    'Nassau', 'New York', 'Niagara', 'Oneida', 'Onondaga', 'Ontario', 'Orange',
    'Orleans', 'Oswego', 'Otsego', 'Putnam', 'Queens', 'Rensselaer', 'Richmond',
    'Rockland', 'Saratoga', 'Schenectady', 'Schoharie', 'Schuyler', 'Seneca',
    'St. Lawrence', 'Steuben', 'Suffolk', 'Sullivan', 'Tioga', 'Tompkins', 'Ulster',
    'Warren', 'Washington', 'Wayne', 'Westchester', 'Wyoming', 'Yates'
];

$invalidCounties = $counties->filter(function($c) use ($validNYCounties) {
    return !in_array($c, $validNYCounties) && !empty($c);
});

if ($invalidCounties->count() > 0) {
    echo "⚠️  Non-standard counties found:\n";
    foreach ($invalidCounties as $c) {
        echo "  - '$c'\n";
    }
} else {
    echo "✓ All counties are valid NY counties\n";
}

echo "\n============================================\n";
echo "AUDIT COMPLETE\n";
echo "============================================\n";
