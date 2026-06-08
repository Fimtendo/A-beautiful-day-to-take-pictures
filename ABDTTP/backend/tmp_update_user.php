<?php
$db = __DIR__ . '/database/database.sqlite';
if (!file_exists($db)) {
    echo "NO_DB\n";
    exit(1);
}
$pdo = new PDO('sqlite:' . $db);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$stmt = $pdo->prepare('UPDATE users SET role = :role WHERE username = :username OR name = :name');
$stmt->execute([':role' => 'admin', ':username' => 'Fimtendo', ':name' => 'Fimtendo']);
echo "UPDATED:" . $stmt->rowCount() . "\n";
$stmt2 = $pdo->prepare('SELECT id, name, username, email, role FROM users WHERE username = :username OR name = :name');
$stmt2->execute([':username' => 'Fimtendo', ':name' => 'Fimtendo']);
$rows = $stmt2->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $row) {
    echo json_encode($row) . "\n";
}
