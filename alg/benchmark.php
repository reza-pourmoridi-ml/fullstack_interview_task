<?php
require __DIR__.'/find_users_burst_purchases.php';

$csv = array_map('str_getcsv', file(__DIR__.'/orders.csv'));
$hdr = array_shift($csv);

$orders = [];
foreach ($csv as $row) {
    $orders[] = ['user' => $row[0], 'time' => $row[1]];
}

$start = microtime(true);
$r = findUsersWithThreePurchasesInFiveMinutes($orders);
$dur = microtime(true) - $start;

echo "users=" . count($r) . ", time=" . $dur . "s\n";