<?php
// Intentionally bad controller for code review (no need to run).
class OrderController {
    public function list() {
        // HIGH: hardcoded prod path in /tmp (volatile, no DI)
        $db = new PDO('sqlite:/tmp/production.sqlite');

        // No auth, no validation
        $userId = $_GET['user'] ?? 1; // HIGH: no auth check - anyone can read any user's orders
        $per = $_GET['per'] ?? 1000; // HIGH: unbounded, no max cap -> DoS risk
        $page = $_GET['page'] ?? 1; // MED: not cast to int, no negative/zero check

        $sql = "SELECT * FROM orders WHERE user_id = $userId ORDER BY created_at DESC"; // HIGH:SQL injection via $userId
        $rows = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC); // MED: no try/catch around query

        // manual pagination in PHP (inefficient)
        $offset = ($page-1)*$per; // LOW: negative if page < 1
        $slice = array_slice($rows, $offset, $per); // MED: should paginate in SQL (LIMIT/OFFSET), nott fetch all rows first

        // join in PHP
        foreach ($slice as &$r) {
            $items = $db->query("SELECT * FROM order_items WHERE order_id = ".$r['id'])->fetchAll(PDO::FETCH_ASSOC); // HIGH: SQL injection risk + MED: N+1 query, should be single IN() query or JOIN
            $r['items'] = $items;
        }
        unset($r); // LOW: good practice to unset loop reference, currently missing

        header('Cache-Control: no-store'); // LOW: missing Content-Type: application/json header
        return json_encode($slice); // MED: no error handling if json_encode fails (returns false)
    }
}