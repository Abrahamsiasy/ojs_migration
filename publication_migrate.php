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
$query = "SELECT * FROM esite_article";
$result = $oldDb->query($query);
if (!$result) {
    die("Query failed: " . $oldDb->error);
}

// Loop through each article for migration
while ($article = $result->fetch_assoc()) {
    // 1. Insert into submissions table
    echo "Inserting submission for article ID: " . $article['id'] . "\n";

    $stmt = $newDb->prepare("
        INSERT INTO submissions (id, context_id, date_submitted, last_modified, stage_id, locale, status, submission_progress, work_type)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
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
    $stage_id = 1;
    $locale = 'en_US';
    $status = 5; // Status 5 = Completed
    $submission_progress = 0;
    $work_type = 1; // Default type for submissions

    $stmt->execute();
    $submissionId = $stmt->insert_id;
    //create a folder based on that submission id and copy the file from the old folder


    // Set the variables
    $folder = $id; // Folder name variable
    $pdfName = $article['pdf']; // PDF name
    $oldDirectory = "./upload/pdf"; // Relative path to the old directory
    $newDirectory = "../../new_ojs_files/journals/1/articles/{$folder}"; // Relative path to the new directory

    // Create the target directory if it doesn't exist
    if (!is_dir($newDirectory)) {
        if (!mkdir($newDirectory, 0777, true)) {
            die("Failed to create target directory: {$newDirectory}\n");
        }
        echo "Created directory: {$newDirectory}\n";
    }

    // Full paths to source and destination files
    $sourceFile = "{$oldDirectory}/{$pdfName}";
    $destinationFile = "{$newDirectory}/{$pdfName}";

    // Copy the file
    if (!file_exists($sourceFile)) {
        die("Source file does not exist: {$sourceFile}\n");
    }

    if (copy($sourceFile, $destinationFile)) {
        echo "File successfully copied to: {$destinationFile}\n";
    } else {
        die("Failed to copy file.\n");
    }


    $stmt->close();

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
        $locale = 'en_US';
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
            $locale = 'en_US';
            $setting_name = $setting[0];
            $setting_value = $setting[1];

            $stmt->execute();
            $stmt->close();
        }
    }

    echo "Authors inserted for article ID: " . $article['id'] . "\n";

    $authorStmt->close();
}

echo "Migration completed successfully.\n";

// Close database connections
$oldDb->close();
$newDb->close();
?>
