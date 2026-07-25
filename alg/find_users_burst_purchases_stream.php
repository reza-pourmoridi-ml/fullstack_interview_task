<?php
class BurstPurchaseDetector {
    private int $windowSeconds;
    private int $minCount;

    private array $recentTimestampsByUser = [];

    private array $matchedUsers = [];

    public function __construct(int $windowSeconds = 300, int $minCount = 3) {
        $this->windowSeconds = $windowSeconds;
        $this->minCount = $minCount;
    }

    public function processOrder(string $userId, string $timeString): void {
        // Already matched: no need to keep tracking this user at all.
        if (isset($this->matchedUsers[$userId])) {
            return;
        }

        $timestamp = strtotime($timeString);

        $queue = &$this->recentTimestampsByUser[$userId];
        $queue ??= [];

        // Evict timestamps that have fallen outside the trailing window.
        while (!empty($queue) && $timestamp - $queue[0] > $this->windowSeconds) {
            array_shift($queue);
        }

        $queue[] = $timestamp;

        if (count($queue) >= $this->minCount) {
            $this->matchedUsers[$userId] = true;
            unset($this->recentTimestampsByUser[$userId]); // free memory, nothing left to track
        }
    }

    public function getMatchingUsers(): array {
        return array_keys($this->matchedUsers);
    }
}

function findUsersWithThreePurchasesInFiveMinutesStreaming(array $orders): array {
    $detector = new BurstPurchaseDetector();

    foreach ($orders as $order) {
        $detector->processOrder($order['user'], $order['time']);
    }

    return $detector->getMatchingUsers();
}