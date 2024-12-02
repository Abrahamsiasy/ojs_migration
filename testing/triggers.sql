
-- Triggers for custom_author_settings

CREATE TRIGGER custom_author_settings_after_insert
AFTER INSERT ON author_settings
FOR EACH ROW
INSERT INTO custom_change_log (table_name, operation_type, new_data)
VALUES ('custom_author_settings', 'INSERT', JSON_OBJECT('author_setting_id', NEW.author_setting_id, 'author_id', NEW.author_id, 'locale', NEW.locale, 'setting_name', NEW.setting_name, 'setting_value', NEW.setting_value));

CREATE TRIGGER custom_author_settings_after_update
AFTER UPDATE ON author_settings
FOR EACH ROW
INSERT INTO custom_change_log (table_name, operation_type, old_data, new_data)
VALUES ('custom_author_settings', 'UPDATE', JSON_OBJECT('old_author_setting_id', OLD.author_setting_id, 'old_author_id', OLD.author_id, 'old_locale', OLD.locale, 'old_setting_name', OLD.setting_name, 'old_setting_value', OLD.setting_value), JSON_OBJECT('new_author_setting_id', NEW.author_setting_id, 'new_author_id', NEW.author_id, 'new_locale', NEW.locale, 'new_setting_name', NEW.setting_name, 'new_setting_value', NEW.setting_value));

CREATE TRIGGER custom_author_settings_after_delete
AFTER DELETE ON author_settings
FOR EACH ROW
INSERT INTO custom_change_log (table_name, operation_type, old_data)
VALUES ('custom_author_settings', 'DELETE', JSON_OBJECT('old_author_setting_id', OLD.author_setting_id, 'old_author_id', OLD.author_id, 'old_locale', OLD.locale, 'old_setting_name', OLD.setting_name, 'old_setting_value', OLD.setting_value));



-- Triggers for custom_authors

CREATE TRIGGER custom_authors_after_insert
AFTER INSERT ON authors
FOR EACH ROW
INSERT INTO custom_change_log (table_name, operation_type, new_data)
VALUES ('custom_authors', 'INSERT', JSON_OBJECT('author_id', NEW.author_id, 'email', NEW.email, 'include_in_browse', NEW.include_in_browse, 'publication_id', NEW.publication_id, 'seq', NEW.seq, 'user_group_id', NEW.user_group_id));

CREATE TRIGGER custom_authors_after_update
AFTER UPDATE ON authors
FOR EACH ROW
INSERT INTO custom_change_log (table_name, operation_type, old_data, new_data)
VALUES ('custom_authors', 'UPDATE', JSON_OBJECT('old_author_id', OLD.author_id, 'old_email', OLD.email, 'old_include_in_browse', OLD.include_in_browse, 'old_publication_id', OLD.publication_id, 'old_seq', OLD.seq, 'old_user_group_id', OLD.user_group_id), JSON_OBJECT('new_author_id', NEW.author_id, 'new_email', NEW.email, 'new_include_in_browse', NEW.include_in_browse, 'new_publication_id', NEW.publication_id, 'new_seq', NEW.seq, 'new_user_group_id', NEW.user_group_id));

CREATE TRIGGER custom_authors_after_delete
AFTER DELETE ON authors
FOR EACH ROW
INSERT INTO custom_change_log (table_name, operation_type, old_data)
VALUES ('custom_authors', 'DELETE', JSON_OBJECT('old_author_id', OLD.author_id, 'old_email', OLD.email, 'old_include_in_browse', OLD.include_in_browse, 'old_publication_id', OLD.publication_id, 'old_seq', OLD.seq, 'old_user_group_id', OLD.user_group_id));



-- Triggers for custom_custom_issue_orders

CREATE TRIGGER custom_custom_issue_orders_after_insert
AFTER INSERT ON custom_issue_orders
FOR EACH ROW
INSERT INTO custom_change_log (table_name, operation_type, new_data)
VALUES ('custom_custom_issue_orders', 'INSERT', JSON_OBJECT('custom_issue_order_id', NEW.custom_issue_order_id, 'issue_id', NEW.issue_id, 'journal_id', NEW.journal_id, 'seq', NEW.seq));

CREATE TRIGGER custom_custom_issue_orders_after_update
AFTER UPDATE ON custom_issue_orders
FOR EACH ROW
INSERT INTO custom_change_log (table_name, operation_type, old_data, new_data)
VALUES ('custom_custom_issue_orders', 'UPDATE', JSON_OBJECT('old_custom_issue_order_id', OLD.custom_issue_order_id, 'old_issue_id', OLD.issue_id, 'old_journal_id', OLD.journal_id, 'old_seq', OLD.seq), JSON_OBJECT('new_custom_issue_order_id', NEW.custom_issue_order_id, 'new_issue_id', NEW.issue_id, 'new_journal_id', NEW.journal_id, 'new_seq', NEW.seq));

CREATE TRIGGER custom_custom_issue_orders_after_delete
AFTER DELETE ON custom_issue_orders
FOR EACH ROW
INSERT INTO custom_change_log (table_name, operation_type, old_data)
VALUES ('custom_custom_issue_orders', 'DELETE', JSON_OBJECT('old_custom_issue_order_id', OLD.custom_issue_order_id, 'old_issue_id', OLD.issue_id, 'old_journal_id', OLD.journal_id, 'old_seq', OLD.seq));



-- Triggers for custom_files

CREATE TRIGGER custom_files_after_insert
AFTER INSERT ON files
FOR EACH ROW
INSERT INTO custom_change_log (table_name, operation_type, new_data)
VALUES ('custom_files', 'INSERT', JSON_OBJECT('file_id', NEW.file_id, 'path', NEW.path, 'mimetype', NEW.mimetype));

CREATE TRIGGER custom_files_after_update
AFTER UPDATE ON files
FOR EACH ROW
INSERT INTO custom_change_log (table_name, operation_type, old_data, new_data)
VALUES ('custom_files', 'UPDATE', JSON_OBJECT('old_file_id', OLD.file_id, 'old_path', OLD.path, 'old_mimetype', OLD.mimetype), JSON_OBJECT('new_file_id', NEW.file_id, 'new_path', NEW.path, 'new_mimetype', NEW.mimetype));

CREATE TRIGGER custom_files_after_delete
AFTER DELETE ON files
FOR EACH ROW
INSERT INTO custom_change_log (table_name, operation_type, old_data)
VALUES ('custom_files', 'DELETE', JSON_OBJECT('old_file_id', OLD.file_id, 'old_path', OLD.path, 'old_mimetype', OLD.mimetype));



-- Triggers for custom_issue_files

CREATE TRIGGER custom_issue_files_after_insert
AFTER INSERT ON issue_files
FOR EACH ROW
INSERT INTO custom_change_log (table_name, operation_type, new_data)
VALUES ('custom_issue_files', 'INSERT', JSON_OBJECT('file_id', NEW.file_id, 'issue_id', NEW.issue_id, 'file_name', NEW.file_name, 'file_type', NEW.file_type, 'file_size', NEW.file_size, 'content_type', NEW.content_type, 'original_file_name', NEW.original_file_name, 'date_uploaded', NEW.date_uploaded, 'date_modified', NEW.date_modified));

CREATE TRIGGER custom_issue_files_after_update
AFTER UPDATE ON issue_files
FOR EACH ROW
INSERT INTO custom_change_log (table_name, operation_type, old_data, new_data)
VALUES ('custom_issue_files', 'UPDATE', JSON_OBJECT('old_file_id', OLD.file_id, 'old_issue_id', OLD.issue_id, 'old_file_name', OLD.file_name, 'old_file_type', OLD.file_type, 'old_file_size', OLD.file_size, 'old_content_type', OLD.content_type, 'old_original_file_name', OLD.original_file_name, 'old_date_uploaded', OLD.date_uploaded, 'old_date_modified', OLD.date_modified), JSON_OBJECT('new_file_id', NEW.file_id, 'new_issue_id', NEW.issue_id, 'new_file_name', NEW.file_name, 'new_file_type', NEW.file_type, 'new_file_size', NEW.file_size, 'new_content_type', NEW.content_type, 'new_original_file_name', NEW.original_file_name, 'new_date_uploaded', NEW.date_uploaded, 'new_date_modified', NEW.date_modified));

CREATE TRIGGER custom_issue_files_after_delete
AFTER DELETE ON issue_files
FOR EACH ROW
INSERT INTO custom_change_log (table_name, operation_type, old_data)
VALUES ('custom_issue_files', 'DELETE', JSON_OBJECT('old_file_id', OLD.file_id, 'old_issue_id', OLD.issue_id, 'old_file_name', OLD.file_name, 'old_file_type', OLD.file_type, 'old_file_size', OLD.file_size, 'old_content_type', OLD.content_type, 'old_original_file_name', OLD.original_file_name, 'old_date_uploaded', OLD.date_uploaded, 'old_date_modified', OLD.date_modified));



-- Triggers for custom_issue_galley_settings

CREATE TRIGGER custom_issue_galley_settings_after_insert
AFTER INSERT ON issue_galley_settings
FOR EACH ROW
INSERT INTO custom_change_log (table_name, operation_type, new_data)
VALUES ('custom_issue_galley_settings', 'INSERT', JSON_OBJECT('issue_galley_setting_id', NEW.issue_galley_setting_id, 'galley_id', NEW.galley_id, 'locale', NEW.locale, 'setting_name', NEW.setting_name, 'setting_value', NEW.setting_value, 'setting_type', NEW.setting_type));

CREATE TRIGGER custom_issue_galley_settings_after_update
AFTER UPDATE ON issue_galley_settings
FOR EACH ROW
INSERT INTO custom_change_log (table_name, operation_type, old_data, new_data)
VALUES ('custom_issue_galley_settings', 'UPDATE', JSON_OBJECT('old_issue_galley_setting_id', OLD.issue_galley_setting_id, 'old_galley_id', OLD.galley_id, 'old_locale', OLD.locale, 'old_setting_name', OLD.setting_name, 'old_setting_value', OLD.setting_value, 'old_setting_type', OLD.setting_type), JSON_OBJECT('new_issue_galley_setting_id', NEW.issue_galley_setting_id, 'new_galley_id', NEW.galley_id, 'new_locale', NEW.locale, 'new_setting_name', NEW.setting_name, 'new_setting_value', NEW.setting_value, 'new_setting_type', NEW.setting_type));

CREATE TRIGGER custom_issue_galley_settings_after_delete
AFTER DELETE ON issue_galley_settings
FOR EACH ROW
INSERT INTO custom_change_log (table_name, operation_type, old_data)
VALUES ('custom_issue_galley_settings', 'DELETE', JSON_OBJECT('old_issue_galley_setting_id', OLD.issue_galley_setting_id, 'old_galley_id', OLD.galley_id, 'old_locale', OLD.locale, 'old_setting_name', OLD.setting_name, 'old_setting_value', OLD.setting_value, 'old_setting_type', OLD.setting_type));



-- Triggers for custom_issue_galleys

CREATE TRIGGER custom_issue_galleys_after_insert
AFTER INSERT ON issue_galleys
FOR EACH ROW
INSERT INTO custom_change_log (table_name, operation_type, new_data)
VALUES ('custom_issue_galleys', 'INSERT', JSON_OBJECT('galley_id', NEW.galley_id, 'locale', NEW.locale, 'issue_id', NEW.issue_id, 'file_id', NEW.file_id, 'label', NEW.label, 'seq', NEW.seq, 'url_path', NEW.url_path));

CREATE TRIGGER custom_issue_galleys_after_update
AFTER UPDATE ON issue_galleys
FOR EACH ROW
INSERT INTO custom_change_log (table_name, operation_type, old_data, new_data)
VALUES ('custom_issue_galleys', 'UPDATE', JSON_OBJECT('old_galley_id', OLD.galley_id, 'old_locale', OLD.locale, 'old_issue_id', OLD.issue_id, 'old_file_id', OLD.file_id, 'old_label', OLD.label, 'old_seq', OLD.seq, 'old_url_path', OLD.url_path), JSON_OBJECT('new_galley_id', NEW.galley_id, 'new_locale', NEW.locale, 'new_issue_id', NEW.issue_id, 'new_file_id', NEW.file_id, 'new_label', NEW.label, 'new_seq', NEW.seq, 'new_url_path', NEW.url_path));

CREATE TRIGGER custom_issue_galleys_after_delete
AFTER DELETE ON issue_galleys
FOR EACH ROW
INSERT INTO custom_change_log (table_name, operation_type, old_data)
VALUES ('custom_issue_galleys', 'DELETE', JSON_OBJECT('old_galley_id', OLD.galley_id, 'old_locale', OLD.locale, 'old_issue_id', OLD.issue_id, 'old_file_id', OLD.file_id, 'old_label', OLD.label, 'old_seq', OLD.seq, 'old_url_path', OLD.url_path));



-- Triggers for custom_issue_settings

CREATE TRIGGER custom_issue_settings_after_insert
AFTER INSERT ON issue_settings
FOR EACH ROW
INSERT INTO custom_change_log (table_name, operation_type, new_data)
VALUES ('custom_issue_settings', 'INSERT', JSON_OBJECT('issue_setting_id', NEW.issue_setting_id, 'issue_id', NEW.issue_id, 'locale', NEW.locale, 'setting_name', NEW.setting_name, 'setting_value', NEW.setting_value));

CREATE TRIGGER custom_issue_settings_after_update
AFTER UPDATE ON issue_settings
FOR EACH ROW
INSERT INTO custom_change_log (table_name, operation_type, old_data, new_data)
VALUES ('custom_issue_settings', 'UPDATE', JSON_OBJECT('old_issue_setting_id', OLD.issue_setting_id, 'old_issue_id', OLD.issue_id, 'old_locale', OLD.locale, 'old_setting_name', OLD.setting_name, 'old_setting_value', OLD.setting_value), JSON_OBJECT('new_issue_setting_id', NEW.issue_setting_id, 'new_issue_id', NEW.issue_id, 'new_locale', NEW.locale, 'new_setting_name', NEW.setting_name, 'new_setting_value', NEW.setting_value));

CREATE TRIGGER custom_issue_settings_after_delete
AFTER DELETE ON issue_settings
FOR EACH ROW
INSERT INTO custom_change_log (table_name, operation_type, old_data)
VALUES ('custom_issue_settings', 'DELETE', JSON_OBJECT('old_issue_setting_id', OLD.issue_setting_id, 'old_issue_id', OLD.issue_id, 'old_locale', OLD.locale, 'old_setting_name', OLD.setting_name, 'old_setting_value', OLD.setting_value));



-- Triggers for custom_issues

CREATE TRIGGER custom_issues_after_insert
AFTER INSERT ON issues
FOR EACH ROW
INSERT INTO custom_change_log (table_name, operation_type, new_data)
VALUES ('custom_issues', 'INSERT', JSON_OBJECT('issue_id', NEW.issue_id, 'journal_id', NEW.journal_id, 'volume', NEW.volume, 'number', NEW.number, 'year', NEW.year, 'published', NEW.published, 'date_published', NEW.date_published, 'date_notified', NEW.date_notified, 'last_modified', NEW.last_modified, 'access_status', NEW.access_status, 'open_access_date', NEW.open_access_date, 'show_volume', NEW.show_volume, 'show_number', NEW.show_number, 'show_year', NEW.show_year, 'show_title', NEW.show_title, 'style_file_name', NEW.style_file_name, 'original_style_file_name', NEW.original_style_file_name, 'url_path', NEW.url_path, 'doi_id', NEW.doi_id));

CREATE TRIGGER custom_issues_after_update
AFTER UPDATE ON issues
FOR EACH ROW
INSERT INTO custom_change_log (table_name, operation_type, old_data, new_data)
VALUES ('custom_issues', 'UPDATE', JSON_OBJECT('old_issue_id', OLD.issue_id, 'old_journal_id', OLD.journal_id, 'old_volume', OLD.volume, 'old_number', OLD.number, 'old_year', OLD.year, 'old_published', OLD.published, 'old_date_published', OLD.date_published, 'old_date_notified', OLD.date_notified, 'old_last_modified', OLD.last_modified, 'old_access_status', OLD.access_status, 'old_open_access_date', OLD.open_access_date, 'old_show_volume', OLD.show_volume, 'old_show_number', OLD.show_number, 'old_show_year', OLD.show_year, 'old_show_title', OLD.show_title, 'old_style_file_name', OLD.style_file_name, 'old_original_style_file_name', OLD.original_style_file_name, 'old_url_path', OLD.url_path, 'old_doi_id', OLD.doi_id), JSON_OBJECT('new_issue_id', NEW.issue_id, 'new_journal_id', NEW.journal_id, 'new_volume', NEW.volume, 'new_number', NEW.number, 'new_year', NEW.year, 'new_published', NEW.published, 'new_date_published', NEW.date_published, 'new_date_notified', NEW.date_notified, 'new_last_modified', NEW.last_modified, 'new_access_status', NEW.access_status, 'new_open_access_date', NEW.open_access_date, 'new_show_volume', NEW.show_volume, 'new_show_number', NEW.show_number, 'new_show_year', NEW.show_year, 'new_show_title', NEW.show_title, 'new_style_file_name', NEW.style_file_name, 'new_original_style_file_name', NEW.original_style_file_name, 'new_url_path', NEW.url_path, 'new_doi_id', NEW.doi_id));

CREATE TRIGGER custom_issues_after_delete
AFTER DELETE ON issues
FOR EACH ROW
INSERT INTO custom_change_log (table_name, operation_type, old_data)
VALUES ('custom_issues', 'DELETE', JSON_OBJECT('old_issue_id', OLD.issue_id, 'old_journal_id', OLD.journal_id, 'old_volume', OLD.volume, 'old_number', OLD.number, 'old_year', OLD.year, 'old_published', OLD.published, 'old_date_published', OLD.date_published, 'old_date_notified', OLD.date_notified, 'old_last_modified', OLD.last_modified, 'old_access_status', OLD.access_status, 'old_open_access_date', OLD.open_access_date, 'old_show_volume', OLD.show_volume, 'old_show_number', OLD.show_number, 'old_show_year', OLD.show_year, 'old_show_title', OLD.show_title, 'old_style_file_name', OLD.style_file_name, 'old_original_style_file_name', OLD.original_style_file_name, 'old_url_path', OLD.url_path, 'old_doi_id', OLD.doi_id));



-- Triggers for custom_library_file_settings

CREATE TRIGGER custom_library_file_settings_after_insert
AFTER INSERT ON library_file_settings
FOR EACH ROW
INSERT INTO custom_change_log (table_name, operation_type, new_data)
VALUES ('custom_library_file_settings', 'INSERT', JSON_OBJECT('library_file_setting_id', NEW.library_file_setting_id, 'file_id', NEW.file_id, 'locale', NEW.locale, 'setting_name', NEW.setting_name, 'setting_value', NEW.setting_value, 'setting_type', NEW.setting_type));

CREATE TRIGGER custom_library_file_settings_after_update
AFTER UPDATE ON library_file_settings
FOR EACH ROW
INSERT INTO custom_change_log (table_name, operation_type, old_data, new_data)
VALUES ('custom_library_file_settings', 'UPDATE', JSON_OBJECT('old_library_file_setting_id', OLD.library_file_setting_id, 'old_file_id', OLD.file_id, 'old_locale', OLD.locale, 'old_setting_name', OLD.setting_name, 'old_setting_value', OLD.setting_value, 'old_setting_type', OLD.setting_type), JSON_OBJECT('new_library_file_setting_id', NEW.library_file_setting_id, 'new_file_id', NEW.file_id, 'new_locale', NEW.locale, 'new_setting_name', NEW.setting_name, 'new_setting_value', NEW.setting_value, 'new_setting_type', NEW.setting_type));

CREATE TRIGGER custom_library_file_settings_after_delete
AFTER DELETE ON library_file_settings
FOR EACH ROW
INSERT INTO custom_change_log (table_name, operation_type, old_data)
VALUES ('custom_library_file_settings', 'DELETE', JSON_OBJECT('old_library_file_setting_id', OLD.library_file_setting_id, 'old_file_id', OLD.file_id, 'old_locale', OLD.locale, 'old_setting_name', OLD.setting_name, 'old_setting_value', OLD.setting_value, 'old_setting_type', OLD.setting_type));



-- Triggers for custom_library_files

CREATE TRIGGER custom_library_files_after_insert
AFTER INSERT ON library_files
FOR EACH ROW
INSERT INTO custom_change_log (table_name, operation_type, new_data)
VALUES ('custom_library_files', 'INSERT', JSON_OBJECT('file_id', NEW.file_id, 'context_id', NEW.context_id, 'file_name', NEW.file_name, 'original_file_name', NEW.original_file_name, 'file_type', NEW.file_type, 'file_size', NEW.file_size, 'type', NEW.type, 'date_uploaded', NEW.date_uploaded, 'date_modified', NEW.date_modified, 'submission_id', NEW.submission_id, 'public_access', NEW.public_access));

CREATE TRIGGER custom_library_files_after_update
AFTER UPDATE ON library_files
FOR EACH ROW
INSERT INTO custom_change_log (table_name, operation_type, old_data, new_data)
VALUES ('custom_library_files', 'UPDATE', JSON_OBJECT('old_file_id', OLD.file_id, 'old_context_id', OLD.context_id, 'old_file_name', OLD.file_name, 'old_original_file_name', OLD.original_file_name, 'old_file_type', OLD.file_type, 'old_file_size', OLD.file_size, 'old_type', OLD.type, 'old_date_uploaded', OLD.date_uploaded, 'old_date_modified', OLD.date_modified, 'old_submission_id', OLD.submission_id, 'old_public_access', OLD.public_access), JSON_OBJECT('new_file_id', NEW.file_id, 'new_context_id', NEW.context_id, 'new_file_name', NEW.file_name, 'new_original_file_name', NEW.original_file_name, 'new_file_type', NEW.file_type, 'new_file_size', NEW.file_size, 'new_type', NEW.type, 'new_date_uploaded', NEW.date_uploaded, 'new_date_modified', NEW.date_modified, 'new_submission_id', NEW.submission_id, 'new_public_access', NEW.public_access));

CREATE TRIGGER custom_library_files_after_delete
AFTER DELETE ON library_files
FOR EACH ROW
INSERT INTO custom_change_log (table_name, operation_type, old_data)
VALUES ('custom_library_files', 'DELETE', JSON_OBJECT('old_file_id', OLD.file_id, 'old_context_id', OLD.context_id, 'old_file_name', OLD.file_name, 'old_original_file_name', OLD.original_file_name, 'old_file_type', OLD.file_type, 'old_file_size', OLD.file_size, 'old_type', OLD.type, 'old_date_uploaded', OLD.date_uploaded, 'old_date_modified', OLD.date_modified, 'old_submission_id', OLD.submission_id, 'old_public_access', OLD.public_access));



-- Triggers for custom_publication_categories

CREATE TRIGGER custom_publication_categories_after_insert
AFTER INSERT ON publication_categories
FOR EACH ROW
INSERT INTO custom_change_log (table_name, operation_type, new_data)
VALUES ('custom_publication_categories', 'INSERT', JSON_OBJECT('publication_category_id', NEW.publication_category_id, 'publication_id', NEW.publication_id, 'category_id', NEW.category_id));

CREATE TRIGGER custom_publication_categories_after_update
AFTER UPDATE ON publication_categories
FOR EACH ROW
INSERT INTO custom_change_log (table_name, operation_type, old_data, new_data)
VALUES ('custom_publication_categories', 'UPDATE', JSON_OBJECT('old_publication_category_id', OLD.publication_category_id, 'old_publication_id', OLD.publication_id, 'old_category_id', OLD.category_id), JSON_OBJECT('new_publication_category_id', NEW.publication_category_id, 'new_publication_id', NEW.publication_id, 'new_category_id', NEW.category_id));

CREATE TRIGGER custom_publication_categories_after_delete
AFTER DELETE ON publication_categories
FOR EACH ROW
INSERT INTO custom_change_log (table_name, operation_type, old_data)
VALUES ('custom_publication_categories', 'DELETE', JSON_OBJECT('old_publication_category_id', OLD.publication_category_id, 'old_publication_id', OLD.publication_id, 'old_category_id', OLD.category_id));



-- Triggers for custom_publication_galley_settings

CREATE TRIGGER custom_publication_galley_settings_after_insert
AFTER INSERT ON publication_galley_settings
FOR EACH ROW
INSERT INTO custom_change_log (table_name, operation_type, new_data)
VALUES ('custom_publication_galley_settings', 'INSERT', JSON_OBJECT('publication_galley_setting_id', NEW.publication_galley_setting_id, 'galley_id', NEW.galley_id, 'locale', NEW.locale, 'setting_name', NEW.setting_name, 'setting_value', NEW.setting_value));

CREATE TRIGGER custom_publication_galley_settings_after_update
AFTER UPDATE ON publication_galley_settings
FOR EACH ROW
INSERT INTO custom_change_log (table_name, operation_type, old_data, new_data)
VALUES ('custom_publication_galley_settings', 'UPDATE', JSON_OBJECT('old_publication_galley_setting_id', OLD.publication_galley_setting_id, 'old_galley_id', OLD.galley_id, 'old_locale', OLD.locale, 'old_setting_name', OLD.setting_name, 'old_setting_value', OLD.setting_value), JSON_OBJECT('new_publication_galley_setting_id', NEW.publication_galley_setting_id, 'new_galley_id', NEW.galley_id, 'new_locale', NEW.locale, 'new_setting_name', NEW.setting_name, 'new_setting_value', NEW.setting_value));

CREATE TRIGGER custom_publication_galley_settings_after_delete
AFTER DELETE ON publication_galley_settings
FOR EACH ROW
INSERT INTO custom_change_log (table_name, operation_type, old_data)
VALUES ('custom_publication_galley_settings', 'DELETE', JSON_OBJECT('old_publication_galley_setting_id', OLD.publication_galley_setting_id, 'old_galley_id', OLD.galley_id, 'old_locale', OLD.locale, 'old_setting_name', OLD.setting_name, 'old_setting_value', OLD.setting_value));



-- Triggers for custom_publication_galleys

CREATE TRIGGER custom_publication_galleys_after_insert
AFTER INSERT ON publication_galleys
FOR EACH ROW
INSERT INTO custom_change_log (table_name, operation_type, new_data)
VALUES ('custom_publication_galleys', 'INSERT', JSON_OBJECT('galley_id', NEW.galley_id, 'locale', NEW.locale, 'publication_id', NEW.publication_id, 'label', NEW.label, 'submission_file_id', NEW.submission_file_id, 'seq', NEW.seq, 'remote_url', NEW.remote_url, 'is_approved', NEW.is_approved, 'url_path', NEW.url_path, 'doi_id', NEW.doi_id));

CREATE TRIGGER custom_publication_galleys_after_update
AFTER UPDATE ON publication_galleys
FOR EACH ROW
INSERT INTO custom_change_log (table_name, operation_type, old_data, new_data)
VALUES ('custom_publication_galleys', 'UPDATE', JSON_OBJECT('old_galley_id', OLD.galley_id, 'old_locale', OLD.locale, 'old_publication_id', OLD.publication_id, 'old_label', OLD.label, 'old_submission_file_id', OLD.submission_file_id, 'old_seq', OLD.seq, 'old_remote_url', OLD.remote_url, 'old_is_approved', OLD.is_approved, 'old_url_path', OLD.url_path, 'old_doi_id', OLD.doi_id), JSON_OBJECT('new_galley_id', NEW.galley_id, 'new_locale', NEW.locale, 'new_publication_id', NEW.publication_id, 'new_label', NEW.label, 'new_submission_file_id', NEW.submission_file_id, 'new_seq', NEW.seq, 'new_remote_url', NEW.remote_url, 'new_is_approved', NEW.is_approved, 'new_url_path', NEW.url_path, 'new_doi_id', NEW.doi_id));

CREATE TRIGGER custom_publication_galleys_after_delete
AFTER DELETE ON publication_galleys
FOR EACH ROW
INSERT INTO custom_change_log (table_name, operation_type, old_data)
VALUES ('custom_publication_galleys', 'DELETE', JSON_OBJECT('old_galley_id', OLD.galley_id, 'old_locale', OLD.locale, 'old_publication_id', OLD.publication_id, 'old_label', OLD.label, 'old_submission_file_id', OLD.submission_file_id, 'old_seq', OLD.seq, 'old_remote_url', OLD.remote_url, 'old_is_approved', OLD.is_approved, 'old_url_path', OLD.url_path, 'old_doi_id', OLD.doi_id));



-- Triggers for custom_publication_settings

CREATE TRIGGER custom_publication_settings_after_insert
AFTER INSERT ON publication_settings
FOR EACH ROW
INSERT INTO custom_change_log (table_name, operation_type, new_data)
VALUES ('custom_publication_settings', 'INSERT', JSON_OBJECT('publication_setting_id', NEW.publication_setting_id, 'publication_id', NEW.publication_id, 'locale', NEW.locale, 'setting_name', NEW.setting_name, 'setting_value', NEW.setting_value));

CREATE TRIGGER custom_publication_settings_after_update
AFTER UPDATE ON publication_settings
FOR EACH ROW
INSERT INTO custom_change_log (table_name, operation_type, old_data, new_data)
VALUES ('custom_publication_settings', 'UPDATE', JSON_OBJECT('old_publication_setting_id', OLD.publication_setting_id, 'old_publication_id', OLD.publication_id, 'old_locale', OLD.locale, 'old_setting_name', OLD.setting_name, 'old_setting_value', OLD.setting_value), JSON_OBJECT('new_publication_setting_id', NEW.publication_setting_id, 'new_publication_id', NEW.publication_id, 'new_locale', NEW.locale, 'new_setting_name', NEW.setting_name, 'new_setting_value', NEW.setting_value));

CREATE TRIGGER custom_publication_settings_after_delete
AFTER DELETE ON publication_settings
FOR EACH ROW
INSERT INTO custom_change_log (table_name, operation_type, old_data)
VALUES ('custom_publication_settings', 'DELETE', JSON_OBJECT('old_publication_setting_id', OLD.publication_setting_id, 'old_publication_id', OLD.publication_id, 'old_locale', OLD.locale, 'old_setting_name', OLD.setting_name, 'old_setting_value', OLD.setting_value));



-- Triggers for custom_publications

CREATE TRIGGER custom_publications_after_insert
AFTER INSERT ON publications
FOR EACH ROW
INSERT INTO custom_change_log (table_name, operation_type, new_data)
VALUES ('custom_publications', 'INSERT', JSON_OBJECT('publication_id', NEW.publication_id, 'access_status', NEW.access_status, 'date_published', NEW.date_published, 'last_modified', NEW.last_modified, 'primary_contact_id', NEW.primary_contact_id, 'section_id', NEW.section_id, 'seq', NEW.seq, 'submission_id', NEW.submission_id, 'status', NEW.status, 'url_path', NEW.url_path, 'version', NEW.version, 'doi_id', NEW.doi_id));

CREATE TRIGGER custom_publications_after_update
AFTER UPDATE ON publications
FOR EACH ROW
INSERT INTO custom_change_log (table_name, operation_type, old_data, new_data)
VALUES ('custom_publications', 'UPDATE', JSON_OBJECT('old_publication_id', OLD.publication_id, 'old_access_status', OLD.access_status, 'old_date_published', OLD.date_published, 'old_last_modified', OLD.last_modified, 'old_primary_contact_id', OLD.primary_contact_id, 'old_section_id', OLD.section_id, 'old_seq', OLD.seq, 'old_submission_id', OLD.submission_id, 'old_status', OLD.status, 'old_url_path', OLD.url_path, 'old_version', OLD.version, 'old_doi_id', OLD.doi_id), JSON_OBJECT('new_publication_id', NEW.publication_id, 'new_access_status', NEW.access_status, 'new_date_published', NEW.date_published, 'new_last_modified', NEW.last_modified, 'new_primary_contact_id', NEW.primary_contact_id, 'new_section_id', NEW.section_id, 'new_seq', NEW.seq, 'new_submission_id', NEW.submission_id, 'new_status', NEW.status, 'new_url_path', NEW.url_path, 'new_version', NEW.version, 'new_doi_id', NEW.doi_id));

CREATE TRIGGER custom_publications_after_delete
AFTER DELETE ON publications
FOR EACH ROW
INSERT INTO custom_change_log (table_name, operation_type, old_data)
VALUES ('custom_publications', 'DELETE', JSON_OBJECT('old_publication_id', OLD.publication_id, 'old_access_status', OLD.access_status, 'old_date_published', OLD.date_published, 'old_last_modified', OLD.last_modified, 'old_primary_contact_id', OLD.primary_contact_id, 'old_section_id', OLD.section_id, 'old_seq', OLD.seq, 'old_submission_id', OLD.submission_id, 'old_status', OLD.status, 'old_url_path', OLD.url_path, 'old_version', OLD.version, 'old_doi_id', OLD.doi_id));



-- Triggers for custom_subeditor_submission_group

CREATE TRIGGER custom_subeditor_submission_group_after_insert
AFTER INSERT ON subeditor_submission_group
FOR EACH ROW
INSERT INTO custom_change_log (table_name, operation_type, new_data)
VALUES ('custom_subeditor_submission_group', 'INSERT', JSON_OBJECT('subeditor_submission_group_id', NEW.subeditor_submission_group_id, 'context_id', NEW.context_id, 'assoc_id', NEW.assoc_id, 'assoc_type', NEW.assoc_type, 'user_id', NEW.user_id, 'user_group_id', NEW.user_group_id));

CREATE TRIGGER custom_subeditor_submission_group_after_update
AFTER UPDATE ON subeditor_submission_group
FOR EACH ROW
INSERT INTO custom_change_log (table_name, operation_type, old_data, new_data)
VALUES ('custom_subeditor_submission_group', 'UPDATE', JSON_OBJECT('old_subeditor_submission_group_id', OLD.subeditor_submission_group_id, 'old_context_id', OLD.context_id, 'old_assoc_id', OLD.assoc_id, 'old_assoc_type', OLD.assoc_type, 'old_user_id', OLD.user_id, 'old_user_group_id', OLD.user_group_id), JSON_OBJECT('new_subeditor_submission_group_id', NEW.subeditor_submission_group_id, 'new_context_id', NEW.context_id, 'new_assoc_id', NEW.assoc_id, 'new_assoc_type', NEW.assoc_type, 'new_user_id', NEW.user_id, 'new_user_group_id', NEW.user_group_id));

CREATE TRIGGER custom_subeditor_submission_group_after_delete
AFTER DELETE ON subeditor_submission_group
FOR EACH ROW
INSERT INTO custom_change_log (table_name, operation_type, old_data)
VALUES ('custom_subeditor_submission_group', 'DELETE', JSON_OBJECT('old_subeditor_submission_group_id', OLD.subeditor_submission_group_id, 'old_context_id', OLD.context_id, 'old_assoc_id', OLD.assoc_id, 'old_assoc_type', OLD.assoc_type, 'old_user_id', OLD.user_id, 'old_user_group_id', OLD.user_group_id));



-- Triggers for custom_submission_file_settings

CREATE TRIGGER custom_submission_file_settings_after_insert
AFTER INSERT ON submission_file_settings
FOR EACH ROW
INSERT INTO custom_change_log (table_name, operation_type, new_data)
VALUES ('custom_submission_file_settings', 'INSERT', JSON_OBJECT('submission_file_setting_id', NEW.submission_file_setting_id, 'submission_file_id', NEW.submission_file_id, 'locale', NEW.locale, 'setting_name', NEW.setting_name, 'setting_value', NEW.setting_value));

CREATE TRIGGER custom_submission_file_settings_after_update
AFTER UPDATE ON submission_file_settings
FOR EACH ROW
INSERT INTO custom_change_log (table_name, operation_type, old_data, new_data)
VALUES ('custom_submission_file_settings', 'UPDATE', JSON_OBJECT('old_submission_file_setting_id', OLD.submission_file_setting_id, 'old_submission_file_id', OLD.submission_file_id, 'old_locale', OLD.locale, 'old_setting_name', OLD.setting_name, 'old_setting_value', OLD.setting_value), JSON_OBJECT('new_submission_file_setting_id', NEW.submission_file_setting_id, 'new_submission_file_id', NEW.submission_file_id, 'new_locale', NEW.locale, 'new_setting_name', NEW.setting_name, 'new_setting_value', NEW.setting_value));

CREATE TRIGGER custom_submission_file_settings_after_delete
AFTER DELETE ON submission_file_settings
FOR EACH ROW
INSERT INTO custom_change_log (table_name, operation_type, old_data)
VALUES ('custom_submission_file_settings', 'DELETE', JSON_OBJECT('old_submission_file_setting_id', OLD.submission_file_setting_id, 'old_submission_file_id', OLD.submission_file_id, 'old_locale', OLD.locale, 'old_setting_name', OLD.setting_name, 'old_setting_value', OLD.setting_value));



-- Triggers for custom_submission_files

CREATE TRIGGER custom_submission_files_after_insert
AFTER INSERT ON submission_files
FOR EACH ROW
INSERT INTO custom_change_log (table_name, operation_type, new_data)
VALUES ('custom_submission_files', 'INSERT', JSON_OBJECT('submission_file_id', NEW.submission_file_id, 'submission_id', NEW.submission_id, 'file_id', NEW.file_id, 'source_submission_file_id', NEW.source_submission_file_id, 'genre_id', NEW.genre_id, 'file_stage', NEW.file_stage, 'direct_sales_price', NEW.direct_sales_price, 'sales_type', NEW.sales_type, 'viewable', NEW.viewable, 'created_at', NEW.created_at, 'updated_at', NEW.updated_at, 'uploader_user_id', NEW.uploader_user_id, 'assoc_type', NEW.assoc_type, 'assoc_id', NEW.assoc_id));

CREATE TRIGGER custom_submission_files_after_update
AFTER UPDATE ON submission_files
FOR EACH ROW
INSERT INTO custom_change_log (table_name, operation_type, old_data, new_data)
VALUES ('custom_submission_files', 'UPDATE', JSON_OBJECT('old_submission_file_id', OLD.submission_file_id, 'old_submission_id', OLD.submission_id, 'old_file_id', OLD.file_id, 'old_source_submission_file_id', OLD.source_submission_file_id, 'old_genre_id', OLD.genre_id, 'old_file_stage', OLD.file_stage, 'old_direct_sales_price', OLD.direct_sales_price, 'old_sales_type', OLD.sales_type, 'old_viewable', OLD.viewable, 'old_created_at', OLD.created_at, 'old_updated_at', OLD.updated_at, 'old_uploader_user_id', OLD.uploader_user_id, 'old_assoc_type', OLD.assoc_type, 'old_assoc_id', OLD.assoc_id), JSON_OBJECT('new_submission_file_id', NEW.submission_file_id, 'new_submission_id', NEW.submission_id, 'new_file_id', NEW.file_id, 'new_source_submission_file_id', NEW.source_submission_file_id, 'new_genre_id', NEW.genre_id, 'new_file_stage', NEW.file_stage, 'new_direct_sales_price', NEW.direct_sales_price, 'new_sales_type', NEW.sales_type, 'new_viewable', NEW.viewable, 'new_created_at', NEW.created_at, 'new_updated_at', NEW.updated_at, 'new_uploader_user_id', NEW.uploader_user_id, 'new_assoc_type', NEW.assoc_type, 'new_assoc_id', NEW.assoc_id));

CREATE TRIGGER custom_submission_files_after_delete
AFTER DELETE ON submission_files
FOR EACH ROW
INSERT INTO custom_change_log (table_name, operation_type, old_data)
VALUES ('custom_submission_files', 'DELETE', JSON_OBJECT('old_submission_file_id', OLD.submission_file_id, 'old_submission_id', OLD.submission_id, 'old_file_id', OLD.file_id, 'old_source_submission_file_id', OLD.source_submission_file_id, 'old_genre_id', OLD.genre_id, 'old_file_stage', OLD.file_stage, 'old_direct_sales_price', OLD.direct_sales_price, 'old_sales_type', OLD.sales_type, 'old_viewable', OLD.viewable, 'old_created_at', OLD.created_at, 'old_updated_at', OLD.updated_at, 'old_uploader_user_id', OLD.uploader_user_id, 'old_assoc_type', OLD.assoc_type, 'old_assoc_id', OLD.assoc_id));



-- Triggers for custom_submission_search_keyword_list

CREATE TRIGGER custom_submission_search_keyword_list_after_insert
AFTER INSERT ON submission_search_keyword_list
FOR EACH ROW
INSERT INTO custom_change_log (table_name, operation_type, new_data)
VALUES ('custom_submission_search_keyword_list', 'INSERT', JSON_OBJECT('keyword_id', NEW.keyword_id, 'keyword_text', NEW.keyword_text));

CREATE TRIGGER custom_submission_search_keyword_list_after_update
AFTER UPDATE ON submission_search_keyword_list
FOR EACH ROW
INSERT INTO custom_change_log (table_name, operation_type, old_data, new_data)
VALUES ('custom_submission_search_keyword_list', 'UPDATE', JSON_OBJECT('old_keyword_id', OLD.keyword_id, 'old_keyword_text', OLD.keyword_text), JSON_OBJECT('new_keyword_id', NEW.keyword_id, 'new_keyword_text', NEW.keyword_text));

CREATE TRIGGER custom_submission_search_keyword_list_after_delete
AFTER DELETE ON submission_search_keyword_list
FOR EACH ROW
INSERT INTO custom_change_log (table_name, operation_type, old_data)
VALUES ('custom_submission_search_keyword_list', 'DELETE', JSON_OBJECT('old_keyword_id', OLD.keyword_id, 'old_keyword_text', OLD.keyword_text));



-- Triggers for custom_submission_settings

CREATE TRIGGER custom_submission_settings_after_insert
AFTER INSERT ON submission_settings
FOR EACH ROW
INSERT INTO custom_change_log (table_name, operation_type, new_data)
VALUES ('custom_submission_settings', 'INSERT', JSON_OBJECT('submission_setting_id', NEW.submission_setting_id, 'submission_id', NEW.submission_id, 'locale', NEW.locale, 'setting_name', NEW.setting_name, 'setting_value', NEW.setting_value));

CREATE TRIGGER custom_submission_settings_after_update
AFTER UPDATE ON submission_settings
FOR EACH ROW
INSERT INTO custom_change_log (table_name, operation_type, old_data, new_data)
VALUES ('custom_submission_settings', 'UPDATE', JSON_OBJECT('old_submission_setting_id', OLD.submission_setting_id, 'old_submission_id', OLD.submission_id, 'old_locale', OLD.locale, 'old_setting_name', OLD.setting_name, 'old_setting_value', OLD.setting_value), JSON_OBJECT('new_submission_setting_id', NEW.submission_setting_id, 'new_submission_id', NEW.submission_id, 'new_locale', NEW.locale, 'new_setting_name', NEW.setting_name, 'new_setting_value', NEW.setting_value));

CREATE TRIGGER custom_submission_settings_after_delete
AFTER DELETE ON submission_settings
FOR EACH ROW
INSERT INTO custom_change_log (table_name, operation_type, old_data)
VALUES ('custom_submission_settings', 'DELETE', JSON_OBJECT('old_submission_setting_id', OLD.submission_setting_id, 'old_submission_id', OLD.submission_id, 'old_locale', OLD.locale, 'old_setting_name', OLD.setting_name, 'old_setting_value', OLD.setting_value));



-- Triggers for custom_submissions

CREATE TRIGGER custom_submissions_after_insert
AFTER INSERT ON submissions
FOR EACH ROW
INSERT INTO custom_change_log (table_name, operation_type, new_data)
VALUES ('custom_submissions', 'INSERT', JSON_OBJECT('submission_id', NEW.submission_id, 'context_id', NEW.context_id, 'current_publication_id', NEW.current_publication_id, 'date_last_activity', NEW.date_last_activity, 'date_submitted', NEW.date_submitted, 'last_modified', NEW.last_modified, 'stage_id', NEW.stage_id, 'locale', NEW.locale, 'status', NEW.status, 'submission_progress', NEW.submission_progress, 'work_type', NEW.work_type));

CREATE TRIGGER custom_submissions_after_update
AFTER UPDATE ON submissions
FOR EACH ROW
INSERT INTO custom_change_log (table_name, operation_type, old_data, new_data)
VALUES ('custom_submissions', 'UPDATE', JSON_OBJECT('old_submission_id', OLD.submission_id, 'old_context_id', OLD.context_id, 'old_current_publication_id', OLD.current_publication_id, 'old_date_last_activity', OLD.date_last_activity, 'old_date_submitted', OLD.date_submitted, 'old_last_modified', OLD.last_modified, 'old_stage_id', OLD.stage_id, 'old_locale', OLD.locale, 'old_status', OLD.status, 'old_submission_progress', OLD.submission_progress, 'old_work_type', OLD.work_type), JSON_OBJECT('new_submission_id', NEW.submission_id, 'new_context_id', NEW.context_id, 'new_current_publication_id', NEW.current_publication_id, 'new_date_last_activity', NEW.date_last_activity, 'new_date_submitted', NEW.date_submitted, 'new_last_modified', NEW.last_modified, 'new_stage_id', NEW.stage_id, 'new_locale', NEW.locale, 'new_status', NEW.status, 'new_submission_progress', NEW.submission_progress, 'new_work_type', NEW.work_type));

CREATE TRIGGER custom_submissions_after_delete
AFTER DELETE ON submissions
FOR EACH ROW
INSERT INTO custom_change_log (table_name, operation_type, old_data)
VALUES ('custom_submissions', 'DELETE', JSON_OBJECT('old_submission_id', OLD.submission_id, 'old_context_id', OLD.context_id, 'old_current_publication_id', OLD.current_publication_id, 'old_date_last_activity', OLD.date_last_activity, 'old_date_submitted', OLD.date_submitted, 'old_last_modified', OLD.last_modified, 'old_stage_id', OLD.stage_id, 'old_locale', OLD.locale, 'old_status', OLD.status, 'old_submission_progress', OLD.submission_progress, 'old_work_type', OLD.work_type));



-- Triggers for custom_user_group_settings

CREATE TRIGGER custom_user_group_settings_after_insert
AFTER INSERT ON user_group_settings
FOR EACH ROW
INSERT INTO custom_change_log (table_name, operation_type, new_data)
VALUES ('custom_user_group_settings', 'INSERT', JSON_OBJECT('user_group_setting_id', NEW.user_group_setting_id, 'user_group_id', NEW.user_group_id, 'locale', NEW.locale, 'setting_name', NEW.setting_name, 'setting_value', NEW.setting_value));

CREATE TRIGGER custom_user_group_settings_after_update
AFTER UPDATE ON user_group_settings
FOR EACH ROW
INSERT INTO custom_change_log (table_name, operation_type, old_data, new_data)
VALUES ('custom_user_group_settings', 'UPDATE', JSON_OBJECT('old_user_group_setting_id', OLD.user_group_setting_id, 'old_user_group_id', OLD.user_group_id, 'old_locale', OLD.locale, 'old_setting_name', OLD.setting_name, 'old_setting_value', OLD.setting_value), JSON_OBJECT('new_user_group_setting_id', NEW.user_group_setting_id, 'new_user_group_id', NEW.user_group_id, 'new_locale', NEW.locale, 'new_setting_name', NEW.setting_name, 'new_setting_value', NEW.setting_value));

CREATE TRIGGER custom_user_group_settings_after_delete
AFTER DELETE ON user_group_settings
FOR EACH ROW
INSERT INTO custom_change_log (table_name, operation_type, old_data)
VALUES ('custom_user_group_settings', 'DELETE', JSON_OBJECT('old_user_group_setting_id', OLD.user_group_setting_id, 'old_user_group_id', OLD.user_group_id, 'old_locale', OLD.locale, 'old_setting_name', OLD.setting_name, 'old_setting_value', OLD.setting_value));



-- Triggers for custom_user_group_stage

CREATE TRIGGER custom_user_group_stage_after_insert
AFTER INSERT ON user_group_stage
FOR EACH ROW
INSERT INTO custom_change_log (table_name, operation_type, new_data)
VALUES ('custom_user_group_stage', 'INSERT', JSON_OBJECT('user_group_stage_id', NEW.user_group_stage_id, 'context_id', NEW.context_id, 'user_group_id', NEW.user_group_id, 'stage_id', NEW.stage_id));

CREATE TRIGGER custom_user_group_stage_after_update
AFTER UPDATE ON user_group_stage
FOR EACH ROW
INSERT INTO custom_change_log (table_name, operation_type, old_data, new_data)
VALUES ('custom_user_group_stage', 'UPDATE', JSON_OBJECT('old_user_group_stage_id', OLD.user_group_stage_id, 'old_context_id', OLD.context_id, 'old_user_group_id', OLD.user_group_id, 'old_stage_id', OLD.stage_id), JSON_OBJECT('new_user_group_stage_id', NEW.user_group_stage_id, 'new_context_id', NEW.context_id, 'new_user_group_id', NEW.user_group_id, 'new_stage_id', NEW.stage_id));

CREATE TRIGGER custom_user_group_stage_after_delete
AFTER DELETE ON user_group_stage
FOR EACH ROW
INSERT INTO custom_change_log (table_name, operation_type, old_data)
VALUES ('custom_user_group_stage', 'DELETE', JSON_OBJECT('old_user_group_stage_id', OLD.user_group_stage_id, 'old_context_id', OLD.context_id, 'old_user_group_id', OLD.user_group_id, 'old_stage_id', OLD.stage_id));



-- Triggers for custom_user_groups

CREATE TRIGGER custom_user_groups_after_insert
AFTER INSERT ON user_groups
FOR EACH ROW
INSERT INTO custom_change_log (table_name, operation_type, new_data)
VALUES ('custom_user_groups', 'INSERT', JSON_OBJECT('user_group_id', NEW.user_group_id, 'context_id', NEW.context_id, 'role_id', NEW.role_id, 'is_default', NEW.is_default, 'show_title', NEW.show_title, 'permit_self_registration', NEW.permit_self_registration, 'permit_metadata_edit', NEW.permit_metadata_edit));

CREATE TRIGGER custom_user_groups_after_update
AFTER UPDATE ON user_groups
FOR EACH ROW
INSERT INTO custom_change_log (table_name, operation_type, old_data, new_data)
VALUES ('custom_user_groups', 'UPDATE', JSON_OBJECT('old_user_group_id', OLD.user_group_id, 'old_context_id', OLD.context_id, 'old_role_id', OLD.role_id, 'old_is_default', OLD.is_default, 'old_show_title', OLD.show_title, 'old_permit_self_registration', OLD.permit_self_registration, 'old_permit_metadata_edit', OLD.permit_metadata_edit), JSON_OBJECT('new_user_group_id', NEW.user_group_id, 'new_context_id', NEW.context_id, 'new_role_id', NEW.role_id, 'new_is_default', NEW.is_default, 'new_show_title', NEW.show_title, 'new_permit_self_registration', NEW.permit_self_registration, 'new_permit_metadata_edit', NEW.permit_metadata_edit));

CREATE TRIGGER custom_user_groups_after_delete
AFTER DELETE ON user_groups
FOR EACH ROW
INSERT INTO custom_change_log (table_name, operation_type, old_data)
VALUES ('custom_user_groups', 'DELETE', JSON_OBJECT('old_user_group_id', OLD.user_group_id, 'old_context_id', OLD.context_id, 'old_role_id', OLD.role_id, 'old_is_default', OLD.is_default, 'old_show_title', OLD.show_title, 'old_permit_self_registration', OLD.permit_self_registration, 'old_permit_metadata_edit', OLD.permit_metadata_edit));



-- Triggers for custom_user_settings

CREATE TRIGGER custom_user_settings_after_insert
AFTER INSERT ON user_settings
FOR EACH ROW
INSERT INTO custom_change_log (table_name, operation_type, new_data)
VALUES ('custom_user_settings', 'INSERT', JSON_OBJECT('user_setting_id', NEW.user_setting_id, 'user_id', NEW.user_id, 'locale', NEW.locale, 'setting_name', NEW.setting_name, 'setting_value', NEW.setting_value));

CREATE TRIGGER custom_user_settings_after_update
AFTER UPDATE ON user_settings
FOR EACH ROW
INSERT INTO custom_change_log (table_name, operation_type, old_data, new_data)
VALUES ('custom_user_settings', 'UPDATE', JSON_OBJECT('old_user_setting_id', OLD.user_setting_id, 'old_user_id', OLD.user_id, 'old_locale', OLD.locale, 'old_setting_name', OLD.setting_name, 'old_setting_value', OLD.setting_value), JSON_OBJECT('new_user_setting_id', NEW.user_setting_id, 'new_user_id', NEW.user_id, 'new_locale', NEW.locale, 'new_setting_name', NEW.setting_name, 'new_setting_value', NEW.setting_value));

CREATE TRIGGER custom_user_settings_after_delete
AFTER DELETE ON user_settings
FOR EACH ROW
INSERT INTO custom_change_log (table_name, operation_type, old_data)
VALUES ('custom_user_settings', 'DELETE', JSON_OBJECT('old_user_setting_id', OLD.user_setting_id, 'old_user_id', OLD.user_id, 'old_locale', OLD.locale, 'old_setting_name', OLD.setting_name, 'old_setting_value', OLD.setting_value));



-- Triggers for custom_user_user_groups

CREATE TRIGGER custom_user_user_groups_after_insert
AFTER INSERT ON user_user_groups
FOR EACH ROW
INSERT INTO custom_change_log (table_name, operation_type, new_data)
VALUES ('custom_user_user_groups', 'INSERT', JSON_OBJECT('user_user_group_id', NEW.user_user_group_id, 'user_group_id', NEW.user_group_id, 'user_id', NEW.user_id));

CREATE TRIGGER custom_user_user_groups_after_update
AFTER UPDATE ON user_user_groups
FOR EACH ROW
INSERT INTO custom_change_log (table_name, operation_type, old_data, new_data)
VALUES ('custom_user_user_groups', 'UPDATE', JSON_OBJECT('old_user_user_group_id', OLD.user_user_group_id, 'old_user_group_id', OLD.user_group_id, 'old_user_id', OLD.user_id), JSON_OBJECT('new_user_user_group_id', NEW.user_user_group_id, 'new_user_group_id', NEW.user_group_id, 'new_user_id', NEW.user_id));

CREATE TRIGGER custom_user_user_groups_after_delete
AFTER DELETE ON user_user_groups
FOR EACH ROW
INSERT INTO custom_change_log (table_name, operation_type, old_data)
VALUES ('custom_user_user_groups', 'DELETE', JSON_OBJECT('old_user_user_group_id', OLD.user_user_group_id, 'old_user_group_id', OLD.user_group_id, 'old_user_id', OLD.user_id));



-- Triggers for custom_users

CREATE TRIGGER custom_users_after_insert
AFTER INSERT ON users
FOR EACH ROW
INSERT INTO custom_change_log (table_name, operation_type, new_data)
VALUES ('custom_users', 'INSERT', JSON_OBJECT('user_id', NEW.user_id, 'username', NEW.username, 'password', NEW.password, 'email', NEW.email, 'url', NEW.url, 'phone', NEW.phone, 'mailing_address', NEW.mailing_address, 'billing_address', NEW.billing_address, 'country', NEW.country, 'locales', NEW.locales, 'gossip', NEW.gossip, 'date_last_email', NEW.date_last_email, 'date_registered', NEW.date_registered, 'date_validated', NEW.date_validated, 'date_last_login', NEW.date_last_login, 'must_change_password', NEW.must_change_password, 'auth_id', NEW.auth_id, 'auth_str', NEW.auth_str, 'disabled', NEW.disabled, 'disabled_reason', NEW.disabled_reason, 'inline_help', NEW.inline_help));

CREATE TRIGGER custom_users_after_update
AFTER UPDATE ON users
FOR EACH ROW
INSERT INTO custom_change_log (table_name, operation_type, old_data, new_data)
VALUES ('custom_users', 'UPDATE', JSON_OBJECT('old_user_id', OLD.user_id, 'old_username', OLD.username, 'old_password', OLD.password, 'old_email', OLD.email, 'old_url', OLD.url, 'old_phone', OLD.phone, 'old_mailing_address', OLD.mailing_address, 'old_billing_address', OLD.billing_address, 'old_country', OLD.country, 'old_locales', OLD.locales, 'old_gossip', OLD.gossip, 'old_date_last_email', OLD.date_last_email, 'old_date_registered', OLD.date_registered, 'old_date_validated', OLD.date_validated, 'old_date_last_login', OLD.date_last_login, 'old_must_change_password', OLD.must_change_password, 'old_auth_id', OLD.auth_id, 'old_auth_str', OLD.auth_str, 'old_disabled', OLD.disabled, 'old_disabled_reason', OLD.disabled_reason, 'old_inline_help', OLD.inline_help), JSON_OBJECT('new_user_id', NEW.user_id, 'new_username', NEW.username, 'new_password', NEW.password, 'new_email', NEW.email, 'new_url', NEW.url, 'new_phone', NEW.phone, 'new_mailing_address', NEW.mailing_address, 'new_billing_address', NEW.billing_address, 'new_country', NEW.country, 'new_locales', NEW.locales, 'new_gossip', NEW.gossip, 'new_date_last_email', NEW.date_last_email, 'new_date_registered', NEW.date_registered, 'new_date_validated', NEW.date_validated, 'new_date_last_login', NEW.date_last_login, 'new_must_change_password', NEW.must_change_password, 'new_auth_id', NEW.auth_id, 'new_auth_str', NEW.auth_str, 'new_disabled', NEW.disabled, 'new_disabled_reason', NEW.disabled_reason, 'new_inline_help', NEW.inline_help));

CREATE TRIGGER custom_users_after_delete
AFTER DELETE ON users
FOR EACH ROW
INSERT INTO custom_change_log (table_name, operation_type, old_data)
VALUES ('custom_users', 'DELETE', JSON_OBJECT('old_user_id', OLD.user_id, 'old_username', OLD.username, 'old_password', OLD.password, 'old_email', OLD.email, 'old_url', OLD.url, 'old_phone', OLD.phone, 'old_mailing_address', OLD.mailing_address, 'old_billing_address', OLD.billing_address, 'old_country', OLD.country, 'old_locales', OLD.locales, 'old_gossip', OLD.gossip, 'old_date_last_email', OLD.date_last_email, 'old_date_registered', OLD.date_registered, 'old_date_validated', OLD.date_validated, 'old_date_last_login', OLD.date_last_login, 'old_must_change_password', OLD.must_change_password, 'old_auth_id', OLD.auth_id, 'old_auth_str', OLD.auth_str, 'old_disabled', OLD.disabled, 'old_disabled_reason', OLD.disabled_reason, 'old_inline_help', OLD.inline_help));


