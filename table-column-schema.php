<?php
$pdo = new PDO('mysql:host=localhost;dbname=ojs', 'root', '');

$query = "
    SELECT TABLE_NAME, COLUMN_NAME
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = 'ojs'
    ORDER BY TABLE_NAME, ORDINAL_POSITION;
";

$statement = $pdo->query($query);
$columns = [];
while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
    $table = $row['TABLE_NAME'];
    $column = $row['COLUMN_NAME'];
    if (!isset($columns[$table])) {
        $columns[$table] = [];
    }
    $columns[$table][] = $column;
}

echo "\$columns = [\n";
foreach ($columns as $table => $cols) {
    echo "    \"$table\" => [\"" . implode('", "', $cols) . "\"],\n";
}
echo "];\n";
?>