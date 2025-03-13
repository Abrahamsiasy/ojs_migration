<?php
// Database connections
$oldDb = new mysqli("localhost", "root", "", "iraqijms_esite");
$newDb = new mysqli("localhost", "root", "", "ojs_sync");

// Check for connection errors
if ($oldDb->connect_error) {
    die("Old DB connection failed: " . $oldDb->connect_error);
}
if ($newDb->connect_error) {
    die("New DB connection failed: " . $newDb->connect_error);
}

try {
    // Fetch all articles from the old DB which are except status = 3
    $oldDbPublications = $oldDb->query("SELECT title, keywords FROM esite_article where status != 3");

    // Store old articles in an array
    $oldArticles = [];
    while ($row = $oldDbPublications->fetch_assoc()) {
        $oldArticles[] = [
            'title' => $row['title'],
            'keywords' => $row['keywords']
        ];
    }

    // Fetch all articles from the new DB which are published (status = 3)
    $newDbPublications = $newDb->query("
    SELECT submissions.submission_id as submission_id, publication_settings.setting_value, publications.publication_id FROM publication_settings 
    join publications on publications.publication_id = publication_settings.publication_id 
    join submissions on submissions.submission_id = publications.submission_id 
    where publication_settings.setting_name = 'title' 
    and publications.status = 3
    group by publication_settings.publication_id");

    $totalMatches = 0;

    try{        
        while ($article = $newDbPublications->fetch_assoc()) {
            $articleTitle = $article['setting_value'];
            $submissionId = $article['submission_id'];
            $publicationId = $article['publication_id'];
    
            $bestMatch = null;
            $bestSimilarity = 0;
    
            foreach ($oldArticles as $oldArticle) {
                $oldTitle = $oldArticle['title'];
    
                // Calculate similarity percentage
                similar_text(strtolower($articleTitle), strtolower($oldTitle), $percent);
                $levDistance = levenshtein(strtolower($articleTitle), strtolower($oldTitle));
    
                // Adjust threshold based on title length (short titles need higher similarity)
                $lengthFactor = max(strlen($articleTitle), strlen($oldTitle));
                $levThreshold = max(3, $lengthFactor * 0.2); // 20% of the longest title
    
                if ($percent > $bestSimilarity && $levDistance < $levThreshold) {
                    $bestSimilarity = $percent;
                    $bestMatch = $oldArticle;
                }
            }
    
            // If a good match is found, retrieve the keywords
            if ($bestMatch && $bestSimilarity >= 70) { // 70% similarity threshold
                $totalMatches++;
                echo "Matched Article:\n";
                echo "New Title: $articleTitle (Publication ID: $publicationId, Submission ID: $submissionId)\n";
                echo "Old Title: {$bestMatch['title']}\n";
                echo "Title Match: {$bestMatch['title']} (Similarity: " . round($bestSimilarity, 2) . "%)\n";
                echo "Keywords: {$bestMatch['keywords']}\n\n";
    
                echo "------------------------------------------------------------------------------------------\n";
    
                $articleKeywords = $bestMatch['keywords'];
    
                if (!empty($articleKeywords)) {
    
                    // insert record on submission_search_objects
                    // $searchObjectQuery = $newDb->prepare("
                    // INSERT INTO submission_search_objects 
                    // (submission_id, type, assoc_id)
                    // VALUES (?, ?, ?)");
    
                    // $searchObjType = 17;
                    // $searchObjAssocID = null;
    
                    // $searchObjectQuery->bind_param("iii", $submissionId, $searchObjType, $searchObjAssocID);
    
                    // if ($searchObjectQuery->execute()) {
                    //     echo "Record inserted successfully for submission_search_objects.\n";
                    // } else {
                    //     echo "Error inserting record for submission_search_objects - " . $searchObjectQuery->error . "\n";
                    // }
    
                    // insert or update keywords here
                    $controlledVocabs = [
                        ["symbolic" => "submissionKeyword", "assoc_type" => 1048588, "assoc_id" => $publicationId],
                        // ["symbolic" => "submissionSubject", "assoc_type" => 1048588, "assoc_id" => $submissionId],
                        // ["symbolic" => "submissionDiscipline", "assoc_type" => 1048588, "assoc_id" => $submissionId],
                        // ["symbolic" => "submissionLanguage", "assoc_type" => 1048588, "assoc_id" => $submissionId],
                        // ["symbolic" => "submissionAgency", "assoc_type" => 1048588, "assoc_id" => $submissionId],
                    ];
    
                    foreach ($controlledVocabs as $entry) {
    
                        $symbolic = $entry['symbolic'];
                        $assocType = $entry['assoc_type'];
                        $assocID = $entry['assoc_id'];
    
                        $query = $newDb->prepare("
                            INSERT INTO controlled_vocabs 
                            (symbolic, assoc_type, assoc_id) 
                            VALUES (?, ?, ?)
                        ");
    
                        $query->bind_param("sii", $symbolic, $assocType, $assocID);
    
                        if ($query->execute()) {
                            if ($entry['symbolic'] == "submissionKeyword") {
                                $controlledVocabId = $query->insert_id;
                                $seq = 1.00;
    
                                // Initialize an array to store the cleaned keywords
                                $allKeywords = [];
    
                                // Step 1: Remove common prefixes like "Key words:" or "Keywords:"
                                $line = preg_replace("/^Key(\\s*words|words)?:\\s*/i", "", $articleKeywords);
    
                                // Step 2: Split the line into individual keywords using comma or semicolon as delimiters
                                $keywords = preg_split("/[;,]/", $line);
    
                                // Step 3: Trim whitespace and clean up special characters
                                foreach ($keywords as $keyword) {
                                    $keyword = trim($keyword); // Remove leading and trailing whitespace
                                    $keyword = preg_replace("/\s+/", " ", $keyword); // Normalize multiple spaces to one
    
                                    // Skip empty keywords
                                    if (!empty($keyword)) {
                                        $allKeywords[] = $keyword;
                                    }
                                }
    
                                $uniqueKeywords = array_values(array_unique($allKeywords));
    
                                foreach ($uniqueKeywords as $keyword) {
                                    $pos = 0;
    
                                    $controlledVocabEntryQuery = $newDb->prepare("
                                        INSERT INTO controlled_vocab_entries 
                                        (controlled_vocab_id, seq) 
                                        VALUES (?, ?)
                                    ");
                                    $controlledVocabEntryQuery->bind_param("id", $controlledVocabId, $seq);
                                    $controlledVocabEntryQuery->execute();
    
                                    $controlledVocabEntryId = $controlledVocabEntryQuery->insert_id;
    
                                    $locale = 'en';
                                    $settingName = $entry['symbolic'];
                                    $settingValue = $keyword;
                                    $settingType = 'string';
    
                                    $controlledVocabEntrySettingQuery = $newDb->prepare("
                                        INSERT INTO controlled_vocab_entry_settings 
                                        (controlled_vocab_entry_id, locale, setting_name, setting_value, setting_type) 
                                        VALUES (?, ?, ?, ?, ?)
                                    ");
                                    $controlledVocabEntrySettingQuery->bind_param(
                                        "issss",
                                        $controlledVocabEntryId,
                                        $locale,
                                        $settingName,
                                        $settingValue,
                                        $settingType
                                    );
                                    $controlledVocabEntrySettingQuery->execute();
    
                                    $seq += 1.00;
    
                                    echo "Keyword '$keyword' processed successfully.\n";
    

                                    // insert submission_search_keyword_list
                                    // $searchKeywordQuery = $newDb->prepare("
                                    // INSERT INTO submission_search_keyword_list 
                                    // (keyword_text)
                                    // VALUES (?)");
                                    
                                    // $searchKeywordQuery->bind_param("s", $keyword);
    
                                    // if ($searchObjectQuery->insert_id) {
                                        
                                    //     $searchKeywordQuery->execute();
    
                                    //     // insert submission_search_object_keywords
                                    //     $searchObjectKeywordQuery = $newDb->prepare("
                                    //     INSERT INTO submission_search_object_keywords 
                                    //     (object_id, keyword_id, pos)
                                    //     VALUES (?, ?, ?)");
                        
                                    //     $objectId = $searchObjectQuery->insert_id;
                                    //     $keywordId = $searchKeywordQuery->insert_id;
                        
                                    //     $searchObjectKeywordQuery->bind_param("iii", $objectId, $keywordId, $pos);
                                    //     $searchObjectKeywordQuery->execute();
    
                                    //     $pos += 1;
    
                                    // } else {
                                    //     echo "Error inserting record for search keyword - " . $searchObjectKeywordQuery->error . "\n";
                                    // }
                                }
                            }
                            echo "Record inserted successfully for symbolic: $symbolic\n";
                        } else {
                            echo "Error inserting record for symbolic: $symbolic - " . $query->error . "\n";
                        }
                    }
                } else {
                    // echo "No keywords found for matched article.\n";
                }
            } else {
                // echo "================================================\n";
                // echo "================================================\n";
                // echo "No close match found for '$articleTitle'\n";
                // echo "================================================\n";
                // echo "================================================\n";
            }
        }
    } catch(Exception $e) {

        echo "===================EXCEPTION===================\n";
        echo $e . "\n";
        echo "===================EXCEPTION===============\n";
    }

    echo "===============================================\n";
    echo "Total Match count: " . $totalMatches . "\n";
    echo "===============================================\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
