<?php
function findUsersWithThreePurchasesInFiveMinutes(array $orders): array {
    define('WINDOW_SECONDS', 300); // 5 minutes
    define('MIN_PURCHASES_IN_WINDOW', 3);

    $timestampsByUser = groupTimestampsByUser($orders);

    $matchingUsers = [];
    foreach ($timestampsByUser as $userId => $timestamps) {
        if (userHasBurstOfPurchases($timestamps, WINDOW_SECONDS, MIN_PURCHASES_IN_WINDOW)) {
            $matchingUsers[] = $userId;
        }
    }

    return $matchingUsers;
}

function groupTimestampsByUser(array $orders): array {
    $timestampsByUser = [];

    foreach ($orders as $order) {
        $userId = $order['user'];
        $timestamp = strtotime($order['time']);
        $timestampsByUser[$userId][] = $timestamp;
    }

    return $timestampsByUser;
}

function userHasBurstOfPurchases(array $timestamps, int $windowSeconds, int $minCount): bool {
    sort($timestamps);

    $left = 0;
    $count = count($timestamps);

    for ($right = 0; $right < $count; $right++) {
        // Shrink the window from the left until it fits within the time limit.
        while ($timestamps[$right] - $timestamps[$left] > $windowSeconds) {
            $left++;
        }

        $purchasesInWindow = $right - $left + 1;
        if ($purchasesInWindow >= $minCount) {
            return true;
        }
    }

    return false;
}