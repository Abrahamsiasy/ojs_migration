<?php
// Database connections
$oldDb = new mysqli("localhost", "root", "", "iraqijms_esite");
$newDb = new mysqli("localhost", "root", "", "ojs");

// Check for connection errors
if ($oldDb->connect_error) {
    die("Old DB connection failed: " . $oldDb->connect_error);
}
if ($newDb->connect_error) {
    die("New DB connection failed: " . $newDb->connect_error);
}

// Query articles from the old database
$query = "SELECT * FROM esite_article where status = 3";
$result = $oldDb->query($query);
if (!$result) {
    die("Query failed: " . $oldDb->error);
}

// Loop through each article for migration
while ($article = $result->fetch_assoc()) {
    // 1. Insert into submissions table
    echo "Inserting submission for article ID: " . $article['id'] . "\n";

    $stmt = $newDb->prepare("
        INSERT INTO submissions (submission_id, context_id, date_submitted, last_modified, stage_id, locale, status, submission_progress, work_type)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->bind_param(
        "iissisiii",
        $id,
        $context_id,
        $date_submitted,
        $last_modified,
        $stage_id,
        $locale,
        $status,
        $submission_progress,
        $work_type
    );
    $id = $article['id'];
    $context_id = 1; // Adjust journal ID
    $date_submitted = $article['date'];
    $last_modified = $article['lastupdate'];
    $stage_id = 5;
    $locale = 'en';
    $status = 3; // Status 5 = Completed
    $submission_progress = 0;
    $work_type = 1; // Default type for submissions

    $stmt->execute();
    $submissionId = $stmt->insert_id;
    //create a folder based on that submission id and copy the file from the old folder


    // Set the variables
    $folder = $id; // Folder name variable
    $pdfName = $article['pdf']; // PDF name
    $pdfName = $pdfName ? basename($pdfName) : null; // PDF name
    $oldDirectory = "./upload/pdf"; // Relative path to the old directory
    $newDirectory = "../../journal/journals/1/articles/{$folder}"; // Relative path to the new directory

    // Create the target directory if it doesn't exist
    if (!is_dir($newDirectory)) {
        if (!mkdir($newDirectory, 0777, true)) {
            die("Failed to create target directory: {$newDirectory}\n");
        }
        echo "Created directory: {$newDirectory}\n";
    }

    // Set the variables
    $folder = $id; // Folder name variable
    $pdfName = $article['pdf']; // PDF name
    $pdfName = $pdfName ? basename($pdfName) : null; // PDF name
    $oldDirectory = "./upload/pdf"; // Relative path to the old directory
    $newDirectory = "../../journal/journals/1/articles/{$id}/"; // Relative path to the new directory

    // Create the target directory if it doesn't exist
    if (!is_dir($newDirectory)) {
        if (!mkdir($newDirectory, 0777, true)) {
            die("Failed to create target directory: {$newDirectory}\n");
        }
        echo "Created directory: {$newDirectory}\n";
    }

    // Copy the file to the new directory
    if ($pdfName) {
        $oldFilePath = "{$oldDirectory}/{$pdfName}";
        $newFilePath = "{$newDirectory}/{$pdfName}";
        
        if (!copy($oldFilePath, $newFilePath)) {
            die("Failed to copy file: {$oldFilePath}\n");
        }
        echo "Copied file to: {$newFilePath}\n";
    }



    $stmt->close();

    // TODO: work on files, submission_files, submission_file_settings,
    // 1. inserting files db
    $filesQuery = $newDb->prepare("
        INSERT INTO files
        (path, mimetype)
        VALUES (?, ?)
    ");

    $stmt->bind_param(
        "ss",
        $path,
        $mimetype,
    );
    $path = $newFilePath; // Adjust journal ID
    $mimetype = "application/pdf";
    $stmt->execute();
    echo 'file inserted';
    // inserting files db end

    // 2. inserting submission_files db
    $stmt = $newDb->prepare("
        INSERT INTO submission_files
        (submission_id, source_submission_file_id, genre_id, file_stage, uploader_user_id)
        VALUES (?, ?, ?, ?, ?)
    ");

    $stmt->bind_param(
        "iiiiii",
        $submission_id,
        $source_submission_file_id,
        $genre_id,
        $file_stage,
        $uploader_user_id,
        $assoc_id
    );
    $submission_id = $submissionId; // Adjust journal ID
    $source_submission_file_id = "application/pdf";// isak last file id or pass the id from above 
    $genre_id=1;
    $file_stage= 2;
    $uploader_user_id = 1; //for now admin
    $assoc_id = NULL; 

    $stmt->execute();
    echo 'file inserted';
    // inserting submission_files db end
    // 3. inserting submission_file_settings db end
    $stmt = $newDb->prepare("
        INSERT INTO submission_file_settings
        (submission_file_id, locale, setting_name, setting_value)
        VALUES (?, ?, ?, ?)
    ");

    $stmt->bind_param(
        "isss",
        $submission_file_id,
        $locale,
        $setting_name,
        $setting_value,
    );
    $submission_id = $submissionId; // Adjust journal ID
    $locale = "en";// isak last file id or pass the id from above 
    $setting_name = 'name'; //for now admin
    $setting_value = $newFilePath; 

    $stmt->execute();
    echo 'file inserted';

    // inserting submission_file_settings db end






    $stmt->close();
    // TODO: forgot to work on submission_settings also (sectionId)

    echo "Submission inserted with submission_id: $submissionId\n";

    // 3. Insert into publications table
    echo "Inserting publication for article ID: " . $article['id'] . "\n";

    $stmt = $newDb->prepare("
        INSERT INTO publications (access_status, date_published, last_modified, section_id, seq, submission_id, status, version)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param(
        "issiiiii",
        $access_status,
        $date_published,
        $last_modified,
        $section_id,
        $seq,
        $submission_id,
        $status,
        $version
    );

    $access_status = 0; // Public access
    $date_published = $article['date'];
    $last_modified = $article['lastupdate'];
    $section_id = 1; // Default section ID
    $seq = $article['vorder'];
    $submission_id = $submissionId ?? null;
    $status = 1; // Active
    $version = 1; // Default version

    $stmt->execute();
    $publicationId = $stmt->insert_id;
    $stmt->close();

    // update current_publication_id on submissions table
    if ($publicationId && $submission_id) {
        $updateStmt = $newDb->prepare("UPDATE submissions SET current_publication_id = ? WHERE submission_id = ?");
        $updateStmt->bind_param("ii", $publicationId, $submission_id);

        if ($updateStmt->execute()) {
            echo "Successfully updated submission with ID $submission_id to have current_publication_id $publicationId.\n";
        } else {
            echo "Failed to update current_publication_id: " . $updateStmt->error . "\n";
        }

        $updateStmt->close();
    } else {
        echo "Error: Missing publicationId or submissionId, cannot update submissions table.\n";
    }

    echo "Publication inserted with publication_id: $publicationId\n";

    // Insert publication settings (title, abstract, etc.)
    echo "Inserting publication settings for publication_id: $publicationId\n";
    $publicationSettings = [
        ['title', $article['title']],
        ['abstract', $article['abstract']],
    ];

    foreach ($publicationSettings as $setting) {
        $stmt = $newDb->prepare("
            INSERT INTO publication_settings (publication_id, locale, setting_name, setting_value)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->bind_param(
            "isss",
            $publication_id,
            $locale,
            $setting_name,
            $setting_value
        );

        $publication_id = $publicationId;
        $locale = 'en';
        $setting_name = $setting[0];
        $setting_value = $setting[1];

        $stmt->execute();
        $stmt->close();
    }

    // 4. Insert publication galleys (default as PDF)
    echo "Inserting publication galleys for publication_id: $publicationId\n";
    $stmt = $newDb->prepare("
        INSERT INTO publication_galleys (publication_id, label, locale)
        VALUES (?, ?, ?)
    ");
    $stmt->bind_param(
        "iss",
        $publication_id,
        $label,
        $locale
    );

    $label = 'PDF';
    $stmt->execute();
    $stmt->close();

    // 2. Insert authors (now after publication insertion)
    echo "Inserting authors for article ID: " . $article['id'] . "\n";

    $authorQuery = "SELECT ea.*,
                       CONCAT(aa.firstname, ' ', aa.middlename, ' ', aa.lastname) AS author_name,
                       aa.affilate AS author_affiliation,
                       aa.country AS author_country,
                       aa.email AS author_email
                FROM esite_article_author ea
                JOIN esite_author aa ON ea.authorid = aa.id
                WHERE ea.articleid = ?";

    $authorStmt = $oldDb->prepare($authorQuery);
    $authorStmt->bind_param("i", $article['id']);
    $authorStmt->execute();
    $authorResult = $authorStmt->get_result();

    while ($author = $authorResult->fetch_assoc()) {
        // Insert into authors table
        echo "Inserting author: " . $author['author_name'] . "\n";

        $stmt = $newDb->prepare("
            INSERT INTO authors (email, include_in_browse, publication_id, seq, user_group_id)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->bind_param(
            "siisi",
            $email,
            $include_in_browse,
            $publication_id,
            $seq,
            $user_group_id
        );

        $email = $author['author_email'];
        $include_in_browse = 1;
        $publication_id = $publicationId;
        $seq = $author['vorder'];
        $user_group_id = 14; // Default group ID for authors

        $stmt->execute();
        $authorId = $stmt->insert_id;
        $stmt->close();


        // Update the primary_contact_id in the publications table
        if ($authorId && $publication_id) {
            $updateStmt = $newDb->prepare("UPDATE publications SET primary_contact_id = ? WHERE publication_id = ?");
            $updateStmt->bind_param("ii", $authorId, $publication_id);

            if ($updateStmt->execute()) {
                echo "Successfully updated publication with ID $publication_id to have primary_contact_id $authorId.\n";
            } else {
                echo "Failed to update primary_contact_id: " . $updateStmt->error . "\n";
            }

            $updateStmt->close();
        } else {
            echo "Error: Missing authorId or publicationId, cannot update publications table.\n";
        }

        // Insert author settings (e.g., name, affiliation, country)
        echo "Inserting author settings for author_id: $authorId\n";
        $authorSettings = [
            ['givenName', $author['author_name']],
            ['affiliation', $author['author_affiliation']],
            ['country', $author['author_country']],
        ];

        foreach ($authorSettings as $setting) {
            $stmt = $newDb->prepare("
                INSERT INTO author_settings (author_id, locale, setting_name, setting_value)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->bind_param(
                "isss",
                $author_id,
                $locale,
                $setting_name,
                $setting_value
            );

            $author_id = $authorId;
            $locale = 'en';
            $setting_name = $setting[0];
            $setting_value = $setting[1];

            $stmt->execute();
            $stmt->close();
        }
    }


    // TODO: ISSUE RELATED CODE HERE
    try {
        $issueId = $article['issue_id'];
        // Fetch issues from old database
        $issueQuery = "
            SELECT ei.*, ev.no AS volume_no, ev.vorder
            FROM esite_issue ei
            JOIN esite_volume ev ON ei.parent = ev.id
            WHERE ei.id = $issueId limit 1
        ";

        $issueResult = $oldDb->query($issueQuery);

        if (!$issueResult) {
            echo "Error fetching issue: " . $oldDb->error . "<br>";
            continue;
        }

        // Migrate each issue
        $issue = $issueResult->fetch_assoc();

        if ($issue) {

            // Prepare data for new database
            $volume = $issue['volume_no'];
            $number = $issue['no'];
            $lastModified = date('Y-m-d H:i:s'); // Current timestamp

            // TODO: check if issue is already created nad update.
            $checkIssueQuery = "
                SELECT id FROM issues
                WHERE id = ?
                LIMIT 1
            ";

            $checkStmt = $newDb->prepare($checkIssueQuery);
            $checkStmt->bind_param("i", $issue['id']);
            $checkStmt->execute();
            $checkStmt->store_result();

            if ($checkStmt->num_rows > 0) {
                echo "Issue already exists in the new database: Volume $volume, Number $number.<br>";
            } else {
                // Insert issue into the new database
                $insertIssueQuery = "
                    INSERT INTO issues (
                        issue_id, journal_id, volume, number, year, published, date_published, 
                        date_notified, last_modified, access_status, open_access_date,
                        show_volume, show_number, show_year, show_title, style_file_name, 
                        original_style_file_name, url_path, doi_id
                    ) VALUES (
                        ?, 1, ?, ?, ?, 0, NULL, 
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
                    "iisis",
                    $issue['id'],
                    $volume,
                    $number,
                    $year,
                    $lastModified
                );

                if ($stmt->execute()) {
                    $newIssueId = $stmt->insert_id; // Get the newly inserted issue ID

                    // Insert into issue_settings
                    $settingsData = [
                        ['locale' => 'en', 'setting_name' => 'issue_title', 'setting_value' => "Volume $volume, Number $number, Year $year"],
                        ['locale' => 'en', 'setting_name' => 'show_title', 'setting_value' => '1'],
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

                // TODO: update issue related tables here, publication_settings
                echo "Inserting publication settings for publication_id: $publicationId\n";
                $publicationSettings = [
                    ['issueId', $issue['id']],
                ];

                foreach ($publicationSettings as $setting) {
                    $stmt = $newDb->prepare("
                        INSERT INTO publication_settings (publication_id, locale, setting_name, setting_value)
                        VALUES (?, ?, ?, ?)
                    ");
                    $stmt->bind_param(
                        "isss",
                        $publication_id,
                        $locale,
                        $setting_name,
                        $setting_value
                    );

                    $publication_id = $publicationId;
                    $locale = 'en';
                    $setting_name = $setting[0];
                    $setting_value = $setting[1];

                    $stmt->execute();
                    $stmt->close();
                }
            }
        }

        $issuesResult->free();

        echo "Migration completed successfully.";
    } catch (Exception $e) {
        echo "An error occurred: " . $e->getMessage();
    }

    echo "Authors inserted for article ID: " . $article['id'] . "\n";

    $authorStmt->close();
}

echo "Migration completed successfully.\n";

// Close database connections
$oldDb->close();
$newDb->close();
