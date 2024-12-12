<?php
// Prefix to avoid naming conflicts
$prefix = "custom_";

// List of tables to track
$tables = [
    "access_keys",
    "announcement_settings",
    "announcement_type_settings",
    "announcement_types",
    "announcements",
    "author_settings",
    "authors",
    "categories",
    "category_settings",
    "citation_settings",
    "citations",
    "completed_payments",
    "controlled_vocab_entries",
    "controlled_vocab_entry_settings",
    "controlled_vocabs",
    "custom_issue_orders",
    "custom_section_orders",
    "data_object_tombstone_oai_set_objects",
    "data_object_tombstone_settings",
    "data_object_tombstones",
    "doi_settings",
    "dois",
    "edit_decisions",
    "email_log",
    "email_log_users",
    "email_templates",
    "email_templates_default_data",
    "email_templates_settings",
    "event_log",
    "event_log_settings",
    "failed_jobs",
    "files",
    "filter_groups",
    "filter_settings",
    "filters",
    "genre_settings",
    "genres",
    "institution_ip",
    "institution_settings",
    "institutional_subscriptions",
    "institutions",
    "issue_files",
    "issue_galley_settings",
    "issue_galleys",
    "issue_settings",
    "issues",
    "job_batches",
    "jobs",
    "journal_settings",
    "journals",
    "library_file_settings",
    "library_files",
    "metrics_context",
    "metrics_counter_submission_daily",
    "metrics_counter_submission_institution_daily",
    "metrics_counter_submission_institution_monthly",
    "metrics_counter_submission_monthly",
    "metrics_issue",
    "metrics_submission",
    "metrics_submission_geo_daily",
    "metrics_submission_geo_monthly",
    "navigation_menu_item_assignment_settings",
    "navigation_menu_item_assignments",
    "navigation_menu_item_settings",
    "navigation_menu_items",
    "navigation_menus",
    "notes",
    "notification_settings",
    "notification_subscription_settings",
    "notifications",
    "oai_resumption_tokens",
    "plugin_settings",
    "publication_categories",
    "publication_galley_settings",
    "publication_galleys",
    "publication_settings",
    "publications",
    "queries",
    "query_participants",
    "queued_payments",
    "review_assignments",
    "review_files",
    "review_form_element_settings",
    "review_form_elements",
    "review_form_responses",
    "review_form_settings",
    "review_forms",
    "review_round_files",
    "review_rounds",
    "scheduled_tasks",
    "section_settings",
    "sections",
    "sessions",
    "site",
    "site_settings",
    "stage_assignments",
    "static_page_settings",
    "static_pages",
    "subeditor_submission_group",
    "submission_comments",
    "submission_file_revisions",
    "submission_file_settings",
    "submission_files",
    "submission_search_keyword_list",
    "submission_search_object_keywords",
    "submission_search_objects",
    "submission_settings",
    "submissions",
    "subscription_type_settings",
    "subscription_types",
    "subscriptions",
    "temporary_files",
    "usage_stats_institution_temporary_records",
    "usage_stats_total_temporary_records",
    "usage_stats_unique_item_investigations_temporary_records",
    "usage_stats_unique_item_requests_temporary_records",
    "user_group_settings",
    "user_group_stage",
    "user_groups",
    "user_interests",
    "user_settings",
    "user_user_groups",
    "users",
    "versions"
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
        "access_keys" => ["access_key_id", "context", "key_hash", "user_id", "assoc_id", "expiry_date"],
        "announcements" => ["announcement_id", "assoc_type", "assoc_id", "type_id", "date_expire", "date_posted"],
        "announcement_settings" => ["announcement_setting_id", "announcement_id", "locale", "setting_name", "setting_value"],
        "announcement_types" => ["type_id", "context_id"],
        "announcement_type_settings" => ["announcement_type_setting_id", "type_id", "locale", "setting_name", "setting_value", "setting_type"],
        "authors" => ["author_id", "email", "include_in_browse", "publication_id", "seq", "user_group_id"],
        "author_settings" => ["author_setting_id", "author_id", "locale", "setting_name", "setting_value"],
        "categories" => ["category_id", "context_id", "parent_id", "seq", "path", "image"],
        "category_settings" => ["category_setting_id", "category_id", "locale", "setting_name", "setting_value"],
        "citations" => ["citation_id", "publication_id", "raw_citation", "seq"],
        "citation_settings" => ["citation_setting_id", "citation_id", "locale", "setting_name", "setting_value", "setting_type"],
        "completed_payments" => ["completed_payment_id", "timestamp", "payment_type", "context_id", "user_id", "assoc_id", "amount", "currency_code_alpha", "payment_method_plugin_name"],
        "controlled_vocabs" => ["controlled_vocab_id", "symbolic", "assoc_type", "assoc_id"],
        "controlled_vocab_entries" => ["controlled_vocab_entry_id", "controlled_vocab_id", "seq"],
        "controlled_vocab_entry_settings" => ["controlled_vocab_entry_setting_id", "controlled_vocab_entry_id", "locale", "setting_name", "setting_value", "setting_type"],
        "custom_issue_orders" => ["custom_issue_order_id", "issue_id", "journal_id", "seq"],
        "custom_section_orders" => ["custom_section_order_id", "issue_id", "section_id", "seq"],
        "data_object_tombstones" => ["tombstone_id", "data_object_id", "date_deleted", "set_spec", "set_name", "oai_identifier"],
        "data_object_tombstone_oai_set_objects" => ["object_id", "tombstone_id", "assoc_type", "assoc_id"],
        "data_object_tombstone_settings" => ["tombstone_setting_id", "tombstone_id", "locale", "setting_name", "setting_value", "setting_type"],
        "dois" => ["doi_id", "context_id", "doi", "status"],
        "doi_settings" => ["doi_setting_id", "doi_id", "locale", "setting_name", "setting_value"],
        "edit_decisions" => ["edit_decision_id", "submission_id", "review_round_id", "stage_id", "round", "editor_id", "decision", "date_decided"],
        "email_log" => ["log_id", "assoc_type", "assoc_id", "sender_id", "date_sent", "event_type", "from_address", "recipients", "cc_recipients", "bcc_recipients", "subject", "body"],
        "email_log_users" => ["email_log_user_id", "email_log_id", "user_id"],
        "email_templates" => ["email_id", "email_key", "context_id", "alternate_to"],
        "email_templates_default_data" => ["email_templates_default_data_id", "email_key", "locale", "name", "subject", "body"],
        "email_templates_settings" => ["email_template_setting_id", "email_id", "locale", "setting_name", "setting_value"],
        "event_log" => ["log_id", "assoc_type", "assoc_id", "user_id", "date_logged", "event_type", "message", "is_translated"],
        "event_log_settings" => ["event_log_setting_id", "log_id", "locale", "setting_name", "setting_value"],
        "failed_jobs" => ["id", "connection", "queue", "payload", "exception", "failed_at"],
        "files" => ["file_id", "path", "mimetype"],
        "filters" => ["filter_id", "filter_group_id", "context_id", "display_name", "class_name", "is_template", "parent_filter_id", "seq"],
        "filter_groups" => ["filter_group_id", "symbolic", "display_name", "description", "input_type", "output_type"],
        "filter_settings" => ["filter_setting_id", "filter_id", "locale", "setting_name", "setting_value", "setting_type"],
        "genres" => ["genre_id", "context_id", "seq", "enabled", "category", "dependent", "supplementary", "required", "entry_key"],
        "genre_settings" => ["genre_setting_id", "genre_id", "locale", "setting_name", "setting_value", "setting_type"],
        "institutional_subscriptions" => ["institutional_subscription_id", "subscription_id", "institution_id", "mailing_address", "domain"],
        "institutions" => ["institution_id", "context_id", "ror", "deleted_at"],
        "institution_ip" => ["institution_ip_id", "institution_id", "ip_string", "ip_start", "ip_end"],
        "institution_settings" => ["institution_setting_id", "institution_id", "locale", "setting_name", "setting_value"],
        "issues" => ["issue_id", "journal_id", "volume", "number", "year", "published", "date_published", "date_notified", "last_modified", "access_status", "open_access_date", "show_volume", "show_number", "show_year", "show_title", "style_file_name", "original_style_file_name", "url_path", "doi_id"],
        "issue_files" => ["file_id", "issue_id", "file_name", "file_type", "file_size", "content_type", "original_file_name", "date_uploaded", "date_modified"],
        "issue_galleys" => ["galley_id", "locale", "issue_id", "file_id", "label", "seq", "url_path"],
        "issue_galley_settings" => ["issue_galley_setting_id", "galley_id", "locale", "setting_name", "setting_value", "setting_type"],
        "issue_settings" => ["issue_setting_id", "issue_id", "locale", "setting_name", "setting_value"],
        "jobs" => ["id", "queue", "payload", "attempts", "reserved_at", "available_at", "created_at"],
        "job_batches" => ["id", "name", "total_jobs", "pending_jobs", "failed_jobs", "failed_job_ids", "options", "cancelled_at", "created_at", "finished_at"],
        "journals" => ["journal_id", "path", "seq", "primary_locale", "enabled", "current_issue_id"],
        "journal_settings" => ["journal_setting_id", "journal_id", "locale", "setting_name", "setting_value"],
        "library_files" => ["file_id", "context_id", "file_name", "original_file_name", "file_type", "file_size", "type", "date_uploaded", "date_modified", "submission_id", "public_access"],
        "library_file_settings" => ["library_file_setting_id", "file_id", "locale", "setting_name", "setting_value", "setting_type"],
        "metrics_context" => ["metrics_context_id", "load_id", "context_id", "date", "metric"],
        "metrics_counter_submission_daily" => ["metrics_counter_submission_daily_id", "load_id", "context_id", "submission_id", "date", "metric_investigations", "metric_investigations_unique", "metric_requests", "metric_requests_unique"],
        "metrics_counter_submission_institution_daily" => ["metrics_counter_submission_institution_daily_id", "load_id", "context_id", "submission_id", "institution_id", "date", "metric_investigations", "metric_investigations_unique", "metric_requests", "metric_requests_unique"],
        "metrics_counter_submission_institution_monthly" => ["metrics_counter_submission_institution_monthly_id", "context_id", "submission_id", "institution_id", "month", "metric_investigations", "metric_investigations_unique", "metric_requests", "metric_requests_unique"],
        "metrics_counter_submission_monthly" => ["metrics_counter_submission_monthly_id", "context_id", "submission_id", "month", "metric_investigations", "metric_investigations_unique", "metric_requests", "metric_requests_unique"],  
        "metrics_issue" => ["metrics_issue_id", "load_id", "context_id", "issue_id", "issue_galley_id", "date", "metric"],
        "metrics_submission" => ["metrics_submission_id", "load_id", "context_id", "submission_id", "representation_id", "submission_file_id", "file_type", "assoc_type", "date", "metric"],
        "metrics_submission_geo_daily" => ["metrics_submission_geo_daily_id", "load_id", "context_id", "submission_id", "country", "region", "city", "date", "metric", "metric_unique"],
        "metrics_submission_geo_monthly" => ["metrics_submission_geo_monthly_id", "context_id", "submission_id", "country", "region", "city", "month", "metric", "metric_unique"],
        "navigation_menus" => ["navigation_menu_id", "context_id", "area_name", "title"],
        "navigation_menu_items" => ["navigation_menu_item_id", "context_id", "path", "type"],
        "navigation_menu_item_assignments" => ["navigation_menu_item_assignment_id", "navigation_menu_id", "navigation_menu_item_id", "parent_id", "seq"],
        "navigation_menu_item_assignment_settings" => ["navigation_menu_item_assignment_setting_id", "navigation_menu_item_assignment_id", "locale", "setting_name", "setting_value", "setting_type"],
        "navigation_menu_item_settings" => ["navigation_menu_item_setting_id", "navigation_menu_item_id", "locale", "setting_name", "setting_value", "setting_type"],
        "notes" => ["note_id", "assoc_type", "assoc_id", "user_id", "date_created", "date_modified", "title", "contents"],
        "notifications" => ["notification_id", "context_id", "user_id", "level", "type", "date_created", "date_read", "assoc_type", "assoc_id"],
        "notification_settings" => ["notification_setting_id", "notification_id", "locale", "setting_name", "setting_value", "setting_type"],
        "notification_subscription_settings" => ["setting_id", "setting_name", "setting_value", "user_id", "context", "setting_type"],
        "oai_resumption_tokens" => ["oai_resumption_token_id", "token", "expire", "record_offset", "params"],
        "plugin_settings" => ["plugin_setting_id", "plugin_name", "context_id", "setting_name", "setting_value", "setting_type"],
        "publications" => ["publication_id", "access_status", "date_published", "last_modified", "primary_contact_id", "section_id", "seq", "submission_id", "status", "url_path", "version", "doi_id"],
        "publication_categories" => ["publication_category_id", "publication_id", "category_id"],
        "publication_galleys" => ["galley_id", "locale", "publication_id", "label", "submission_file_id", "seq", "remote_url", "is_approved", "url_path", "doi_id"],
        "publication_galley_settings" => ["publication_galley_setting_id", "galley_id", "locale", "setting_name", "setting_value"],
        "publication_settings" => ["publication_setting_id", "publication_id", "locale", "setting_name", "setting_value"],
        "queries" => ["query_id", "assoc_type", "assoc_id", "stage_id", "seq", "date_posted", "date_modified", "closed"],
        "query_participants" => ["query_participant_id", "query_id", "user_id"],
        "queued_payments" => ["queued_payment_id", "date_created", "date_modified", "expiry_date", "payment_data"],    
        "review_assignments" => ["review_id", "submission_id", "reviewer_id", "competing_interests", "recommendation", "date_assigned", "date_notified", "date_confirmed", "date_completed", "date_acknowledged", "date_due", "date_response_due", "last_modified", "reminder_was_automatic", "declined", "cancelled", "date_rated", "date_reminded", "quality", "review_round_id", "stage_id", "review_method", "round", "step", "review_form_id", "considered", "request_resent"],
        "review_files" => ["review_file_id", "review_id", "submission_file_id"],
        "review_forms" => ["review_form_id", "assoc_type", "assoc_id", "seq", "is_active"],
        "review_form_elements" => ["review_form_element_id", "review_form_id", "seq", "element_type", "required", "included"],
        "review_form_element_settings" => ["review_form_element_setting_id", "review_form_element_id", "locale", "setting_name", "setting_value", "setting_type"],
        "review_form_responses" => ["review_form_response_id", "review_form_element_id", "review_id", "response_type", "response_value"],
        "review_form_settings" => ["review_form_setting_id", "review_form_id", "locale", "setting_name", "setting_value", "setting_type"],
        "review_rounds" => ["review_round_id", "submission_id", "stage_id", "round", "review_revision", "status"],     
        "review_round_files" => ["review_round_file_id", "submission_id", "review_round_id", "stage_id", "submission_file_id"],
        "scheduled_tasks" => ["scheduled_task_id", "class_name", "last_run"],
        "sections" => ["section_id", "journal_id", "review_form_id", "seq", "editor_restricted", "meta_indexed", "meta_reviewed", "abstracts_not_required", "hide_title", "hide_author", "is_inactive", "abstract_word_count"],
        "section_settings" => ["section_setting_id", "section_id", "locale", "setting_name", "setting_value"],
        "sessions" => ["session_id", "user_id", "ip_address", "user_agent", "created", "last_used", "remember", "data", "domain"],
        "site" => ["site_id", "redirect", "primary_locale", "min_password_length", "installed_locales", "supported_locales", "original_style_file_name"],
        "site_settings" => ["site_setting_id", "setting_name", "locale", "setting_value"],
        "stage_assignments" => ["stage_assignment_id", "submission_id", "user_group_id", "user_id", "date_assigned", "recommend_only", "can_change_metadata"],
        "static_pages" => ["static_page_id", "path", "context_id"],
        "static_page_settings" => ["static_page_setting_id", "static_page_id", "locale", "setting_name", "setting_value", "setting_type"],
        "subeditor_submission_group" => ["subeditor_submission_group_id", "context_id", "assoc_id", "assoc_type", "user_id", "user_group_id"],
        "submissions" => ["submission_id", "context_id", "current_publication_id", "date_last_activity", "date_submitted", "last_modified", "stage_id", "locale", "status", "submission_progress", "work_type"],
        "submission_comments" => ["comment_id", "comment_type", "role_id", "submission_id", "assoc_id", "author_id", "comment_title", "comments", "date_posted", "date_modified", "viewable"],
        "submission_files" => ["submission_file_id", "submission_id", "file_id", "source_submission_file_id", "genre_id", "file_stage", "direct_sales_price", "sales_type", "viewable", "created_at", "updated_at", "uploader_user_id", "assoc_type", "assoc_id"],
        "submission_file_revisions" => ["revision_id", "submission_file_id", "file_id"],
        "submission_file_settings" => ["submission_file_setting_id", "submission_file_id", "locale", "setting_name", "setting_value"],
        "submission_search_keyword_list" => ["keyword_id", "keyword_text"],
        "submission_search_objects" => ["object_id", "submission_id", "type", "assoc_id"],
        "submission_search_object_keywords" => ["submission_search_object_keyword_id", "object_id", "keyword_id", "pos"],
        "submission_settings" => ["submission_setting_id", "submission_id", "locale", "setting_name", "setting_value"],
        "subscriptions" => ["subscription_id", "journal_id", "user_id", "type_id", "date_start", "date_end", "status", "membership", "reference_number", "notes"],
        "subscription_types" => ["type_id", "journal_id", "cost", "currency_code_alpha", "duration", "format", "institutional", "membership", "disable_public_display", "seq"],
        "subscription_type_settings" => ["subscription_type_setting_id", "type_id", "locale", "setting_name", "setting_value", "setting_type"],
        "temporary_files" => ["file_id", "user_id", "file_name", "file_type", "file_size", "original_file_name", "date_uploaded"],
        "usage_stats_institution_temporary_records" => ["usage_stats_temp_institution_id", "load_id", "line_number", "institution_id"],
        "usage_stats_total_temporary_records" => ["usage_stats_temp_total_id", "date", "ip", "user_agent", "line_number", "canonical_url", "issue_id", "issue_galley_id", "context_id", "submission_id", "representation_id", "submission_file_id", "assoc_type", "file_type", "country", "region", "city", "load_id"],
        "usage_stats_unique_item_investigations_temporary_records" => ["usage_stats_temp_unique_item_id", "date", "ip", "user_agent", "line_number", "context_id", "submission_id", "representation_id", "submission_file_id", "assoc_type", "file_type", "country", "region", "city", "load_id"],
        "usage_stats_unique_item_requests_temporary_records" => ["usage_stats_temp_item_id", "date", "ip", "user_agent", "line_number", "context_id", "submission_id", "representation_id", "submission_file_id", "assoc_type", "file_type", "country", "region", "city", "load_id"],
        "users" => ["user_id", "username", "password", "email", "url", "phone", "mailing_address", "billing_address", "country", "locales", "gossip", "date_last_email", "date_registered", "date_validated", "date_last_login", "must_change_password", "auth_id", "auth_str", "disabled", "disabled_reason", "inline_help"],
        "user_groups" => ["user_group_id", "context_id", "role_id", "is_default", "show_title", "permit_self_registration", "permit_metadata_edit"],
        "user_group_settings" => ["user_group_setting_id", "user_group_id", "locale", "setting_name", "setting_value"],
        "user_group_stage" => ["user_group_stage_id", "context_id", "user_group_id", "stage_id"],
        "user_interests" => ["user_interest_id", "user_id", "controlled_vocab_entry_id"],
        "user_settings" => ["user_setting_id", "user_id", "locale", "setting_name", "setting_value"],
        "user_user_groups" => ["user_user_group_id", "user_group_id", "user_id"],
        "versions" => ["version_id", "major", "minor", "revision", "build", "date_installed", "current", "product_type", "product", "product_class_name", "lazy_load", "sitewide"],
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
