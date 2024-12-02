<?php
// Prefix to avoid naming conflicts
$prefix = "custom_";

// List of tables to track
$tables = [
    "author_settings", "authors", "custom_issue_orders",
    "files", "issue_files", "issue_galley_settings", "issue_galleys", "issue_settings", "issues",
    "library_file_settings", "library_files",
    "publication_categories", "publication_galley_settings", "publication_galleys", "publication_settings",
    "publications", "subeditor_submission_group", "submission_file_settings", "submission_files",
    "submission_search_keyword_list", "submission_settings", "submissions",
    "user_group_settings", "user_group_stage", "user_groups", "user_settings", "user_user_groups", "users"
];

// Function to generate triggers for a table
function generateTriggers($prefix, $tableName, $columns) {
    // Create the column list in the format for JSON_OBJECT
    $columnList = implode(", ", array_map(function ($column) {
        return "'$column', NEW.$column";
    }, $columns));

    return "
-- Triggers for $prefix$tableName

CREATE TRIGGER {$prefix}{$tableName}_after_insert
AFTER INSERT ON $tableName
FOR EACH ROW
INSERT INTO {$prefix}change_log (table_name, operation_type, new_data)
VALUES ('$prefix$tableName', 'INSERT', JSON_OBJECT($columnList));

CREATE TRIGGER {$prefix}{$tableName}_after_update
AFTER UPDATE ON $tableName
FOR EACH ROW
INSERT INTO {$prefix}change_log (table_name, operation_type, old_data, new_data)
VALUES ('$prefix$tableName', 'UPDATE', JSON_OBJECT(" . implode(", ", array_map(function ($column) {
        return "'old_$column', OLD.$column";
    }, $columns)) . "), JSON_OBJECT(" . implode(", ", array_map(function ($column) {
        return "'new_$column', NEW.$column";
    }, $columns)) . "));

CREATE TRIGGER {$prefix}{$tableName}_after_delete
AFTER DELETE ON $tableName
FOR EACH ROW
INSERT INTO {$prefix}change_log (table_name, operation_type, old_data)
VALUES ('$prefix$tableName', 'DELETE', JSON_OBJECT(" . implode(", ", array_map(function ($column) {
        return "'old_$column', OLD.$column";
    }, $columns)) . "));
";
}

// Define columns for each table manually or fetch from the database
function getColumnsForTable($table) {
    // Manually defined columns for each table (based on the schema you provided)
    $columns = [
        "author_settings" => ["author_setting_id", "author_id", "locale", "setting_name", "setting_value"],
        "authors" => ["author_id", "email", "include_in_browse", "publication_id", "seq", "user_group_id"],
        "custom_issue_orders" => ["custom_issue_order_id", "issue_id", "journal_id", "seq"],
        "files" => ["file_id", "path", "mimetype"],
        "issue_files" => ["file_id", "issue_id", "file_name", "file_type", "file_size", "content_type", "original_file_name", "date_uploaded", "date_modified"],
        "issue_galley_settings" => ["issue_galley_setting_id", "galley_id", "locale", "setting_name", "setting_value", "setting_type"],
        "issue_galleys" => ["galley_id", "locale", "issue_id", "file_id", "label", "seq", "url_path"],
        "issue_settings" => ["issue_setting_id", "issue_id", "locale", "setting_name", "setting_value"],
        "issues" => ["issue_id", "journal_id", "volume", "number", "year", "published", "date_published", "date_notified", "last_modified", "access_status", "open_access_date", "show_volume", "show_number", "show_year", "show_title", "style_file_name", "original_style_file_name", "url_path", "doi_id"],
        "library_file_settings" => ["library_file_setting_id", "file_id", "locale", "setting_name", "setting_value", "setting_type"],
        "library_files" => ["file_id", "context_id", "file_name", "original_file_name", "file_type", "file_size", "type", "date_uploaded", "date_modified", "submission_id", "public_access"],
        "publication_categories" => ["publication_category_id", "publication_id", "category_id"],
        "publication_galley_settings" => ["publication_galley_setting_id", "galley_id", "locale", "setting_name", "setting_value"],
        "publication_galleys" => ["galley_id", "locale", "publication_id", "label", "submission_file_id", "seq", "remote_url", "is_approved", "url_path", "doi_id"],
        "publication_settings" => ["publication_setting_id", "publication_id", "locale", "setting_name", "setting_value"],
        "publications" => ["publication_id", "access_status", "date_published", "last_modified", "primary_contact_id", "section_id", "seq", "submission_id", "status", "url_path", "version", "doi_id"],
        "subeditor_submission_group" => ["subeditor_submission_group_id", "context_id", "assoc_id", "assoc_type", "user_id", "user_group_id"],
        "submission_file_settings" => ["submission_file_setting_id", "submission_file_id", "locale", "setting_name", "setting_value"],
        "submission_files" => ["submission_file_id", "submission_id", "file_id", "source_submission_file_id", "genre_id", "file_stage", "direct_sales_price", "sales_type", "viewable", "created_at", "updated_at", "uploader_user_id", "assoc_type", "assoc_id"],
        "submission_search_keyword_list" => ["keyword_id", "keyword_text"],
        "submission_settings" => ["submission_setting_id", "submission_id", "locale", "setting_name", "setting_value"],
        "submissions" => ["submission_id", "context_id", "current_publication_id", "date_last_activity", "date_submitted", "last_modified", "stage_id", "locale", "status", "submission_progress", "work_type"],
        "user_group_settings" => ["user_group_setting_id", "user_group_id", "locale", "setting_name", "setting_value"],
        "user_group_stage" => ["user_group_stage_id", "context_id", "user_group_id", "stage_id"],
        "user_groups" => ["user_group_id", "context_id", "role_id", "is_default", "show_title", "permit_self_registration", "permit_metadata_edit"],
        "user_settings" => ["user_setting_id", "user_id", "locale", "setting_name", "setting_value"],
        "user_user_groups" => ["user_user_group_id", "user_group_id", "user_id"],
        "users" => ["user_id", "username", "password", "email", "url", "phone", "mailing_address", "billing_address", "country", "locales", "gossip", "date_last_email", "date_registered", "date_validated", "date_last_login", "must_change_password", "auth_id", "auth_str", "disabled", "disabled_reason", "inline_help"]
    ];

    // Return columns for the requested table
    return isset($columns[$table]) ? $columns[$table] : [];
}

// Loop through the tables and generate SQL for each
foreach ($tables as $table) {
    $columns = getColumnsForTable($table);  // Fetch columns for the current table
    
    if (!empty($columns)) {
        echo generateTriggers($prefix, $table, $columns);
        echo "\n\n";
    } else {
        echo "-- No columns found for table: $table\n\n";
    }
}
?>
