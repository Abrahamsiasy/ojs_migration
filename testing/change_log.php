<?php
// Tables to track
$tables_to_track = [
    'submissions',
    'submission_files',
    'submission_settings',
    'users',
    'authors',
    'author_settings',
    'publications',
    'publication_settings'
];

// Columns to focus on for specific tables
$column_focus = [
    'authors' => ['email', 'publication'],
    'author_settings' => ['affiliation', 'country'],
];

// Generate triggers for each table
function generateTriggers($table, $focusColumns = []) {
    $focusClause = $focusColumns
        ? "JSON_OBJECT(" . implode(", ", array_map(fn($col) => "'$col', NEW.$col", $focusColumns)) . ")"
        : "JSON_OBJECT('data', ROW(NEW.*))";

    return "
-- Triggers for $table

CREATE TRIGGER {$table}_after_insert
AFTER INSERT ON $table
FOR EACH ROW
INSERT INTO change_log (table_name, operation_type, new_data)
VALUES ('$table', 'INSERT', $focusClause);

CREATE TRIGGER {$table}_after_update
AFTER UPDATE ON $table
FOR EACH ROW
INSERT INTO change_log (table_name, operation_type, old_data, new_data)
VALUES ('$table', 'UPDATE', 
        " . ($focusColumns
            ? "JSON_OBJECT(" . implode(", ", array_map(fn($col) => "'$col', OLD.$col", $focusColumns)) . ")"
            : "JSON_OBJECT('data', ROW(OLD.*))") . ", 
        $focusClause);

CREATE TRIGGER {$table}_after_delete
AFTER DELETE ON $table
FOR EACH ROW
INSERT INTO change_log (table_name, operation_type, old_data)
VALUES ('$table', 'DELETE', " . ($focusColumns
            ? "JSON_OBJECT(" . implode(", ", array_map(fn($col) => "'$col', OLD.$col", $focusColumns)) . ")"
            : "JSON_OBJECT('data', ROW(OLD.*))") . ");
";
}

// Loop through each table and generate triggers
foreach ($tables_to_track as $table) {
    $focusColumns = $column_focus[$table] ?? [];
    echo generateTriggers($table, $focusColumns);
    echo "\n\n";
}
?>
