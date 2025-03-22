<?php
// Database connections
$oldDb = new mysqli("localhost", "root", "", "iraqijms_esite");
$newDb = new mysqli("localhost", "root", "", "ojs_fresh");

// Check for connection errors
if ($oldDb->connect_error) {
    die("Old DB connection failed: " . $oldDb->connect_error);
}
if ($newDb->connect_error) {
    die("New DB connection failed: " . $newDb->connect_error);
}

// Fetch all published articles
$newDbPublications = $newDb->query("
    SELECT submissions.submission_id AS submission_id, 
           publication_settings.setting_value AS abstract, 
           publications.publication_id 
    FROM publication_settings 
    JOIN publications ON publications.publication_id = publication_settings.publication_id 
    JOIN submissions ON submissions.submission_id = publications.submission_id 
    WHERE publication_settings.setting_name = 'abstract' 
    AND publications.status = 3
    GROUP BY publication_settings.publication_id
");

try {
    $emptyKeys = 0;
    $emptyPublications = [];
    while ($article = $newDbPublications->fetch_assoc()) {
        $publicationId = $article['publication_id'];
        $articleAbstract = $article['abstract'];
    
        // Extract keywords
        $keywords = extractKeywords($articleAbstract);

        // // File to write to
        // $filename = "publications.txt";

        // // Data format: publicationId: keywords
        // $line = "$publicationId: " . implode(", ", $keywords) . PHP_EOL;

        // // Append to file
        // file_put_contents($filename, $line, FILE_APPEND | LOCK_EX);
    
        if (!empty($keywords)) {
            // Check if keywords already exist
            $checkStmt = $newDb->prepare("
                SELECT assoc_id FROM controlled_vocabs WHERE assoc_id = ? LIMIT 1
            ");
            $checkStmt->bind_param("i", $publicationId);
            $checkStmt->execute();
            $checkStmt->store_result();
    
            if ($checkStmt->num_rows === 0) {
                // Insert into controlled_vocabs
                $insertVocab = $newDb->prepare("
                    INSERT INTO controlled_vocabs (symbolic, assoc_type, assoc_id) 
                    VALUES (?, ?, ?)
                ");
                $symbolic = "submissionKeyword";
                $assocType = 1048588;
                $insertVocab->bind_param("sii", $symbolic, $assocType, $publicationId);
                $insertVocab->execute();
    
                $controlledVocabId = $insertVocab->insert_id;
    
                // Insert keywords into controlled_vocab_entries
                insertKeywords($newDb, $controlledVocabId, $keywords);
            } else {
                echo "Skipping: Keywords already exist for publication ID: $publicationId\n";
            }
        } else {
            $emptyKeys++;
            $emptyPublications[] = $publicationId;
        }
    }

    echo "\nTotal: empty keys: $emptyKeys\n";
    // File to write to
    $filename = "publications-missed.txt";

    // Data format: publicationId: keywords
    $line = implode(", ", $emptyPublications) . PHP_EOL;

    // Append to file
    file_put_contents($filename, $line, FILE_APPEND | LOCK_EX);
} catch (Exception $e) {
    echo "Error: " . $e;
}

// Loop through each article

/**
 * Extracts keywords from an abstract.
 */
// function extractKeywords($abstract)
// {
//     // Match keywords section
//     // if (preg_match("/(Keywords?|KEYWORDS)[: ](.*)/i", $abstract, $matches)) {
//     if (preg_match("/(Key words?|Keywords?)[: ](.*)/i", $abstract, $matches)) {

//         $rawKeywords = trim($matches[2]);

//         // Remove unwanted parts
//         $rawKeywords = str_replace(["Citation", "\n", "\r"], "", $rawKeywords);
//         $rawKeywords = preg_replace("/[^a-zA-Z0-9, ]/", "", $rawKeywords); // Keep only letters, numbers, commas, and spaces.

//         // Convert to array
//         $keywordsArray = explode(",", $rawKeywords);
//         $keywordsArray = array_map('trim', $keywordsArray);
//         return array_filter(array_unique($keywordsArray)); // Remove empty and duplicate keywords
//     }
//     return [];
// }

function extractKeywords($abstract)
{
    // Match keywords section
    // if (preg_match("/(Key words?|Keywords?)[: ](.*)/i", $abstract, $matches)) {
    if (preg_match("/(keywords?|key\s*words?|Key worlds?)[:\s]*([^\r\n]*)/i", $abstract, $matches)) {
        $rawKeywords = trim($matches[2]);

        // Cut at "Citation" if it exists
        if (stripos($rawKeywords, "Citation") !== false) {
            $rawKeywords = substr($rawKeywords, 0, stripos($rawKeywords, "Citation"));
        } else {
            // If no "Citation", limit to first 4 words
            $words = preg_split('/\s+/', $rawKeywords); // Split into words
            // if (count($words) > 4) {
                $rawKeywords = implode(' ', array_slice($words, 0, 30));
            // }
        }

        // Remove unwanted characters (keep only letters, numbers, commas, and spaces)
        $rawKeywords = preg_replace("/[^a-zA-Z0-9, ]/", "", $rawKeywords);

        // Convert to array
        $keywordsArray = explode(",", $rawKeywords);
        $keywordsArray = array_map('trim', $keywordsArray);
        
        return array_filter(array_unique($keywordsArray)); // Remove empty and duplicate keywords
    }
    return [];
}


/**
 * Inserts keywords into controlled_vocab_entries.
 */
function insertKeywords($db, $vocabId, $keywords)
{
    $seq = 1.00;
    foreach ($keywords as $keyword) {
        // Insert into controlled_vocab_entries
        $insertEntry = $db->prepare("
            INSERT INTO controlled_vocab_entries (controlled_vocab_id, seq) 
            VALUES (?, ?)
        ");
        $insertEntry->bind_param("id", $vocabId, $seq);
        $insertEntry->execute();
        $entryId = $insertEntry->insert_id;

        // Insert into controlled_vocab_entry_settings
        $locale = "en";
        $settingName = "submissionKeyword";
        $settingValue = $keyword;
        $settingType = "string";

        $insertEntrySetting = $db->prepare("
            INSERT INTO controlled_vocab_entry_settings 
            (controlled_vocab_entry_id, locale, setting_name, setting_value, setting_type) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $insertEntrySetting->bind_param("issss", $entryId, $locale, $settingName, $settingValue, $settingType);
        $insertEntrySetting->execute();

        $seq += 1.00;
        echo "Inserted keyword: $keyword\n";
    }
}
