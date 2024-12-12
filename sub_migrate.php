<?php
$oldDb = new mysqli("localhost", "root", "", "iraqijms_esite");
$newDb = new mysqli("localhost", "root", "", "ojs2");

if ($oldDb->connect_error || $newDb->connect_error) {
    die("Connection failed: " . ($oldDb->connect_error ?? $newDb->connect_error));
}

// Old and new file paths
$oldFilePath = realpath("C:/xampp/htdocs/migration/upload/pdf");
$newFilePath = realpath("C:/xampp/new_ojs_files/journals/1/articles");

if (!$oldFilePath || !$newFilePath) {
    die("Error: Invalid file paths.");
}

// Fetch all articles from the old database
$articles = $oldDb->query("SELECT * FROM esite_article");


while ($article = $articles->fetch_assoc()) {
    // Map fields from old database
    $title = $article['title'];
    $abstract = $article['abstract'];
    $issueId = $article['issue']; // Ensure issue IDs are mapped correctly
    $keywords = $article['keywords'];
    $pdfFile = basename($article['pdf']);
    $submissionDate = $article['date'];

    // Migrate PDF file
    $oldPdfPath = $oldFilePath . DIRECTORY_SEPARATOR . ltrim($pdfFile, "/"); // Handle paths cross-platform
    $newPdfDir = $newFilePath . DIRECTORY_SEPARATOR . uniqid(); // Create a unique directory for the submission
    $newPdfPath = $newPdfDir . DIRECTORY_SEPARATOR . 'submission.pdf';

    // die($oldPdfPath);

    // Ensure the new directory exists
    if (!is_dir($newPdfDir) && !mkdir($newPdfDir, 0777, true)) {
        die("Error: Failed to create directory '$newPdfDir'.");
    }

    // Check if the source is a valid file
    if (!is_file($oldPdfPath)) {
        die("Error: '$oldPdfPath' is not a valid file. Unable to proceed.");
    }

    // Attempt to copy the file
    if (!copy($oldPdfPath, $newPdfPath)) {
        die("Error: Failed to copy file from '$oldPdfPath' to '$newPdfPath'.");
    }

    // Insert into `submissions`
    $insertSubmission = $newDb->prepare("
        INSERT INTO submissions (
            context_id, submission_progress, current_publication_id, date_submitted, status, submission_id
        ) VALUES (?, ?, ?, ?, ?, NULL)
    ");
    $contextId = 1;
    $submissionProgress = 0;
    $currentPublicationId = null;
    $status = 3; // Assuming 3 for published

    $insertSubmission->bind_param(
        'iiisi',
        $contextId,
        $submissionProgress,
        $currentPublicationId,
        $submissionDate,
        $status
    );
    $insertSubmission->execute();
    $newSubmissionId = $newDb->insert_id;

    // Insert into `publications`
    $insertPublication = $newDb->prepare("
        INSERT INTO publications (
            publication_id, section_id, submission_id, date_published, status
        ) VALUES (?, ?, ?, ?, ?)
    ");
    $datePublished = $submissionDate;
    $publicationStatus = 3; // Published
    $newSectionId = 1; // Placeholder; update with appropriate section ID

    $insertPublication->bind_param(
        'iiisi',
        $newSubmissionId,
        $newSectionId,
        $newSubmissionId,
        $datePublished,
        $publicationStatus
    );
    $insertPublication->execute();

    // Insert metadata into `publication_settings`
    $publicationSettings = [
        ['setting_name' => 'categoryIds', 'setting_value' => '[]', 'locale' => ''],
        ['setting_name' => 'coverImage', 'setting_value' => '{"uploadName":"article_' . $newSubmissionId . '_cover_en.png","altText":"Default Cover"}', 'locale' => 'en'],
        ['setting_name' => 'abstract', 'setting_value' => htmlspecialchars($abstract), 'locale' => 'en'],
        ['setting_name' => 'prefix', 'setting_value' => '', 'locale' => 'en'], // Add prefix if available
        ['setting_name' => 'subtitle', 'setting_value' => '', 'locale' => 'en'], // Add subtitle if available
        ['setting_name' => 'title', 'setting_value' => $title, 'locale' => 'en'],
        ['setting_name' => 'pages', 'setting_value' => '1-12', 'locale' => ''], // Update with actual page numbers
        ['setting_name' => 'issueId', 'setting_value' => $issueId, 'locale' => ''],
        ['setting_name' => 'copyrightHolder', 'setting_value' => 'Iraqi Journal of Medical Sciences', 'locale' => 'en'], // Replace as needed
        ['setting_name' => 'copyrightYear', 'setting_value' => date('Y', strtotime($submissionDate)), 'locale' => ''],
    ];

    foreach ($publicationSettings as $setting) {
        $insertSetting = $newDb->prepare("
            INSERT INTO publication_settings (
                publication_id, setting_name, setting_value, locale
            ) VALUES (?, ?, ?, ?)
        ");
        $insertSetting->bind_param(
            'isss',
            $newSubmissionId,
            $setting['setting_name'],
            $setting['setting_value'],
            $setting['locale']
        );
        $insertSetting->execute();
    }

    echo "Migrated article: $title (ID: $newSubmissionId)\n";
}

echo "Migration completed successfully!";

$oldDb->close();
$newDb->close();
?>

<!-- UPDATE `publication_settings`
SET `locale` = ''
WHERE `setting_name` = 'issueId';
 -->
