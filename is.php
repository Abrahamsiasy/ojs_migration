<?php
// Database connection settings
$oldDb = new mysqli("localhost", "root", "", "iraqijms_esite");
$newDb = new mysqli("localhost", "root", "", "ojs2");

// Check connections
if ($oldDb->connect_error) {
    die("Connection failed to old database: " . $oldDb->connect_error);
}
if ($newDb->connect_error) {
    die("Connection failed to new database: " . $newDb->connect_error);
}

// Fetch data from old database and migrate
try {
    for ($year = 2000; $year <= date("Y"); $year++) {
        // Fetch issues from old database
        $issuesQuery = "
            SELECT ei.*, ev.no AS volume_no, ev.vorder
            FROM esite_issue ei
            JOIN esite_volume ev ON ei.parent = ev.id
            WHERE ev.year = $year
            ORDER BY ev.vorder
        ";
        $issuesResult = $oldDb->query($issuesQuery);

        if (!$issuesResult) {
            echo "Error fetching issues for year $year: " . $oldDb->error . "<br>";
            continue;
        }

        // Migrate each issue
        while ($issue = $issuesResult->fetch_assoc()) {
            // Prepare data for new database
            $volume = $issue['volume_no'];
            $number = $issue['no'];
            $lastModified = date('Y-m-d H:i:s'); // Current timestamp

            // Insert issue into the new database
            $insertIssueQuery = "
                INSERT INTO issues (
                    journal_id, volume, number, year, published, date_published, 
                    date_notified, last_modified, access_status, open_access_date,
                    show_volume, show_number, show_year, show_title, style_file_name, 
                    original_style_file_name, url_path, doi_id
                ) VALUES (
                    1, ?, ?, ?, 0, NULL, 
                    NULL, ?, 1, NULL,
                    1, 1, 1, 0, NULL, 
                    NULL, NULL, NULL
                )
            ";

            $stmt = $newDb->prepare($insertIssueQuery);
            if (!$stmt) {
                echo "Error preparing issue insert statement: " . $newDb->error . "<br>";
                continue;
            }

            $stmt->bind_param(
                "isis",
                $volume,
                $number,
                $year,
                $lastModified
            );

            if ($stmt->execute()) {
                $newIssueId = $stmt->insert_id; // Get the newly inserted issue ID

                // Insert into issue_settings
                $settingsData = [
                    ['locale' => 'en_US', 'setting_name' => 'issue_title', 'setting_value' => "Volume $volume, Number $number, Year $year"],
                    ['locale' => 'en_US', 'setting_name' => 'show_title', 'setting_value' => '1'],
                ];

                foreach ($settingsData as $setting) {
                    $insertSettingQuery = "
                        INSERT INTO issue_settings (
                            issue_id, locale, setting_name, setting_value
                        ) VALUES (
                            ?, ?, ?, ?
                        )
                    ";

                    $settingStmt = $newDb->prepare($insertSettingQuery);
                    if (!$settingStmt) {
                        echo "Error preparing issue_settings insert statement: " . $newDb->error . "<br>";
                        continue;
                    }

                    $settingStmt->bind_param(
                        "isss",
                        $newIssueId,
                        $setting['locale'],
                        $setting['setting_name'],
                        $setting['setting_value']
                    );

                    if ($settingStmt->execute()) {
                        echo "Added issue setting for issue ID $newIssueId: {$setting['setting_name']}<br>";
                    } else {
                        echo "Error adding issue setting for issue ID $newIssueId: " . $settingStmt->error . "<br>";
                    }

                    $settingStmt->close();
                }

                echo "Migrated issue: Volume $volume, Number $number, Year $year<br>";
            } else {
                echo "Error migrating issue: Volume $volume, Number $number, Year $year: " . $stmt->error . "<br>";
            }

            $stmt->close();
        }

        $issuesResult->free();
    }

    echo "Migration completed successfully.";
} catch (Exception $e) {
    echo "An error occurred: " . $e->getMessage();
} finally {
    // Close connections
    $oldDb->close();
    $newDb->close();
}
