<?php
// NOTE: find_users_burst_purchases.php currently has `var_dump($matchingUsers); die();`
// before its `return` statement. That will dump output and halt the script before
// this benchmark can run the streaming comparison — remove those two lines first.

require __DIR__.'/find_users_burst_purchases.php';        // old: findUsersWithThreePurchasesInFiveMinutes()
require __DIR__.'/find_users_burst_purchases_stream.php'; // new: BurstPurchaseDetector

$csvPath = __DIR__.'/orders.csv';

// ---------------------------------------------------------------------
// OLD ALGORITHM: loads every row into memory, then sorts + slides per user.
// ---------------------------------------------------------------------
$csv = array_map('str_getcsv', file($csvPath));
$hdr = array_shift($csv);

$orders = [];
foreach ($csv as $row) {
    $orders[] = ['user' => $row[0], 'time' => $row[1]];
}

$memBefore = memory_get_peak_usage(true);
$start = microtime(true);
$oldResult = findUsersWithThreePurchasesInFiveMinutes($orders);
$oldDur = microtime(true) - $start;
$oldMemPeak = memory_get_peak_usage(true) - $memBefore;

echo "[old: sort+sliding]   users=" . count($oldResult)
    . ", time=" . round($oldDur, 4) . "s"
    . ", peak_mem=" . round($oldMemPeak / 1024 / 1024, 2) . "MB\n";

unset($orders, $csv); // free before running the streaming version

// ---------------------------------------------------------------------
// NEW ALGORITHM: streams the file row by row, one pass, no full array,
// no sort. Memory stays bounded regardless of file size.
// ---------------------------------------------------------------------
$memBefore = memory_get_peak_usage(true);
$start = microtime(true);

$detector = new BurstPurchaseDetector();
$handle = fopen($csvPath, 'r');
fgetcsv($handle); // skip header
while (($row = fgetcsv($handle)) !== false) {
    $detector->processOrder($row[0], $row[1]);
}
fclose($handle);

$newResult = $detector->getMatchingUsers();
$newDur = microtime(true) - $start;
$newMemPeak = memory_get_peak_usage(true) - $memBefore;

echo "[new: streaming]      users=" . count($newResult)
    . ", time=" . round($newDur, 4) . "s"
    . ", peak_mem=" . round($newMemPeak / 1024 / 1024, 2) . "MB\n";

// ---------------------------------------------------------------------
// Sanity check: both algorithms should agree on the result set.
// ---------------------------------------------------------------------
sort($oldResult);
sort($newResult);
$match = ($oldResult === $newResult) ? 'MATCH' : 'MISMATCH';
echo "result parity: $match\n";