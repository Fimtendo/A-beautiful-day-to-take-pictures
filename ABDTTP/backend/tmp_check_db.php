<?php
$db = __DIR__ . '/database/database.sqlite';
if (!file_exists($db)) {
    echo "NO_DB\n";
    exit(0);
}
$pdo = new PDO('sqlite:' . $db);
$tables = array('markers', 'photo_dates', 'users');
foreach ($tables as $table) {
    try {
        $stmt = $pdo->query("SELECT count(*) AS cnt FROM $table");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $cnt = $row['cnt'];
        echo "$table:$cnt\n";
        $stmt2 = $pdo->query("SELECT * FROM $table LIMIT 3");
        while ($row2 = $stmt2->fetch(PDO::FETCH_ASSOC)) {
            echo json_encode($row2) . "\n";
        }
    } catch (Exception $e) {
        echo "ERR $table: " . $e->getMessage() . "\n";
    }
}
