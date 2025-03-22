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

try {

    // TODO: implement oldauthors here
    $newDbPublications = $newDb->query("
    SELECT submissions.submission_id as submission_id, publication_settings.setting_value, publications.publication_id FROM publication_settings 
    join publications on publications.publication_id = publication_settings.publication_id 
    join submissions on submissions.submission_id = publications.submission_id 
    where publication_settings.setting_name = 'title' 
    and publications.status = 3
    group by publication_settings.publication_id");

    try {
        while ($article = $newDbPublications->fetch_assoc()) {
            $articleTitle = $article['setting_value'];
            $submissionId = $article['submission_id'];
            $publicationId = $article['publication_id'];

            $oldDbPubWithOldAuthors = $oldDb->query("SELECT * FROM esite_article where status = 3 and approved = 1 and  oldauthors is not null and id = $submissionId");

            while ($pubAuthor = $oldDbPubWithOldAuthors->fetch_assoc()) {
                $oldAuthors = $pubAuthor['oldauthors'];
                $articleId = $pubAuthor['id'];

                // create the authors here
                createAuthor($oldAuthors, $articleId, $publicationId, $newDb);
            }
        }

        echo "\n================================\n";
        echo "AUTHORS INSERTED SUCCESSFULLY \n";
        echo "================================\n";
    } catch (Exception $e) {

        echo "\n===================EXCEPTION===================\n";
        echo $e . "\n";
        echo "===================EXCEPTION=====================\n";
    }

    // ---------------------------------------------------------------------------------

    // TODO: if oldauthors is empty, fallback here for future
    // if (true) {
    //     authorCreatorFallback();
    // }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

function createAuthor($names, $submissionId, $publicationId, $newDb)
{
    // Split the input string by commas to separate multiple names
    $nameArray = explode(", ", $names);

    // Initialize an array to store the generated emails
    $emails = [];

    // Loop through each name in the array
    foreach ($nameArray as $name) {

        // Remove any extra spaces
        $name = trim($name);

        // Split the name into parts (assuming first name, middle initial(s), last name)
        $nameParts = preg_split('/\s+/', $name);

        // Get the first name and last name (taking the first and last parts)
        $firstName = strtolower($nameParts[0]);
        $lastName = strtolower(end($nameParts));

        // Generate the email (remove any unnecessary characters)
        $authorEmail = "temp_" . $firstName . "." . $lastName . "@gmail.com";
        $username = strtolower("temp_" . $firstName . "." . $lastName);

        // TODO: create the user here
        $userId = createUser($username, $authorEmail, $firstName, $lastName, $newDb);

        echo "User ID: " . $userId . "\n";

        /* ---------------------------------------------------------------------------------------- */
        /* Author insertion logic START */
        // Check if the author already exists based on email and publication_id
        $checkAuthorQuery = $newDb->prepare("
            SELECT author_id FROM authors WHERE email = ? AND publication_id = ?
        ");
        $checkAuthorQuery->bind_param("si", $authorEmail, $publicationId);
        $checkAuthorQuery->execute();
        $checkAuthorQuery->store_result();

        $authorId = null;

        if ($checkAuthorQuery->num_rows > 0) {
            echo "Author already exists.\n";
        } else {
            // TODO: insert the author here (authors, author_setting)
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

            $email = $authorEmail;
            $include_in_browse = 1;
            $publication_id = $publicationId;
            $seq = 0.00;
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
                ['givenName', $name],
                ['affiliation', ''],
                ['country', 'Iraq'],
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

        // this is the place to fix logged in authors not showing submission
        // stage assignments
        try {
            $stageAssignmentSettings = [
                [
                    'submission_id' => $submissionId,
                    'user_group_id' => 14,
                    'user_id' => $userId,
                    'date_assigned' => date('Y-m-d H:i:s'),
                    'recommend_only' => 0,
                    'can_change_metadata' => 0,
                ],
            ];

            // check if issue is already created and update.
            $checkStageQuery = "
                SELECT submission_id FROM stage_assignments
                WHERE submission_id = ? and user_id = ?
                LIMIT 1
            ";

            $checkStmt = $newDb->prepare($checkStageQuery);
            $checkStmt->bind_param("ii", $submissionId, $userId);
            $checkStmt->execute();
            $checkStmt->store_result();

            if ($checkStmt->num_rows == 0) {
                foreach ($stageAssignmentSettings as $setting) {
                    $stageAssignmentQuery = $newDb->prepare("
                    INSERT INTO stage_assignments 
                    (submission_id, user_group_id, user_id, date_assigned, recommend_only, can_change_metadata) 
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                    $stageAssignmentQuery->bind_param(
                        "iiisii",
                        $setting['submission_id'],
                        $setting['user_group_id'],
                        $setting['user_id'],
                        $setting['date_assigned'],
                        $setting['recommend_only'],
                        $setting['can_change_metadata']
                    );
                    $stageAssignmentQuery->execute();
                }
            }
        } catch (PDOException $e) {
        }

        /* Author insertion logic END */
        /* ---------------------------------------------------------------------------------------- */

        // Add the generated email to the emails array
        $emails[] = $authorEmail;
    }

    return $emails;
}

function createUser($username = '', $email, $firstName, $lastName, $newDb)
{
    $password = '$2y$10$NB95Jswlug10FuRc5pFYt.hL5yfFCDKYeY1Hk/IlF3/psDaMfL5yC'; // Correctly assign the hash
    $dateRegistered = date("Y-m-d H:i:s");
    $mustChangePassword = 1;

    // Check if the user already exists
    $checkUserQuery = $newDb->prepare("
        SELECT user_id FROM users WHERE username = ? OR email = ?
    ");
    $checkUserQuery->bind_param("ss", $username, $email);
    $checkUserQuery->execute();
    $checkUserQuery->store_result();

    $userId = null;

    if ($checkUserQuery->num_rows > 0) {
        echo "User already exists.\n";
        // Fetch the existing user ID
        $checkUserQuery->bind_result($userId);
        $checkUserQuery->fetch();
        $checkUserQuery->close();
        return $userId;
    } else {
        // Insert into `users` table
        $usersQuery = $newDb->prepare("
            INSERT INTO users 
            (username, password, email, date_registered, must_change_password) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $usersQuery->bind_param("ssssi", $username, $password, $email, $dateRegistered, $mustChangePassword);
        $usersQuery->execute();
        $userId = $usersQuery->insert_id;

        echo "User inserted with ID: " . $userId;

        $checkUserQuery->close();
        $usersQuery->close();

        // Insert into `user_settings` table
        $settings = [
            ['locale' => 'en', 'setting_name' => 'affiliation', 'setting_value' => ''],
            ['locale' => 'en', 'setting_name' => 'familyName', 'setting_value' => $lastName],
            ['locale' => 'en', 'setting_name' => 'givenName', 'setting_value' => $firstName],
            ['locale' => '', 'setting_name' => 'orcid', 'setting_value' => ''], // ORCID not available, use empty string
            ['locale' => 'en', 'setting_name' => 'preferredPublicName', 'setting_value' => $firstName],
        ];

        foreach ($settings as $setting) {
            $userSettingsQuery = $newDb->prepare("
                INSERT INTO user_settings 
                (user_setting_id, user_id, locale, setting_name, setting_value) 
                VALUES (NULL, ?, ?, ?, ?)
            ");
            $userSettingsQuery->bind_param(
                "isss",
                $userId,
                $setting['locale'],
                $setting['setting_name'],
                $setting['setting_value']
            );
            $userSettingsQuery->execute();
        }

        // notification_subscription_settings
        $notificationSubscriptionSettings = [
            ['user_id' => $userId, "setting_type" => "int", "context" => 1, 'setting_name' => 'blocked_emailed_notification', 'setting_value' => "8"],
            ['user_id' => $userId, "setting_type" => "int", "context" => 1, 'setting_name' => 'blocked_emailed_notification', 'setting_value' => "268435477"],
            ['user_id' => $userId, "setting_type" => "int", "context" => 1, 'setting_name' => 'blocked_emailed_notification', 'setting_value' => "50331659"],
        ];

        foreach ($notificationSubscriptionSettings as $setting) {
            $notificationSettingsQuery = $newDb->prepare("
                INSERT INTO notification_subscription_settings 
                (user_id, context, setting_type, setting_name, setting_value) 
                VALUES (?, ?, ?, ?, ?)
            ");
            $notificationSettingsQuery->bind_param(
                "iisss",
                $userId,
                $setting['context'],
                $setting["setting_type"],
                $setting['setting_name'],
                $setting['setting_value']
            );
            $notificationSettingsQuery->execute();
        }

        // Insert into `user_user_groups` table
        $roleReader = 17; // Reader role

        $userGroupQueryReader = $newDb->prepare("
            INSERT INTO user_user_groups 
            (user_user_group_id, user_group_id, user_id) 
            VALUES (NULL, ?, ?)
        ");

        $userGroupQueryReader->bind_param("ii", $roleReader, $userId);
        $userGroupQueryReader->execute();

        $role = 14; // Author role
        $userGroupQuery = $newDb->prepare("
            INSERT INTO user_user_groups 
            (user_user_group_id, user_group_id, user_id) 
            VALUES (NULL, ?, ?)
        ");
        $userGroupQuery->bind_param("ii", $role, $userId);
        $userGroupQuery->execute();

        return $userId;
    }
}

function authorCreatorFallback()
{
    // TODO: this is fallback if oldauthors column has no value
    // Fetch all articles from the old DB which are except status = 3
    $oldDbPublications = $oldDb->query("SELECT id, title, keywords, oldauthors FROM esite_article where status != 3");

    // Store old articles in an array
    $oldArticles = [];
    while ($row = $oldDbPublications->fetch_assoc()) {
        $oldArticles[] = [
            'id' => $row['id'],
            'title' => $row['title'],
            'oldauthors' => $row['oldauthors']
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

    try {
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

                echo "------------------------------------------------------------------------------------------\n";

                $oldArticleId = $bestMatch['id'];

                $oldDbArticleAuthors = $oldDb->query("SELECT authorid, articelid FROM esite_article_author where articleid == $oldArticleId");

                var_dump($oldDbArticleAuthors->fetch_assoc());
                die();

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

                        // check if issue is already created and update.
                        $checkControlledVocabQuery = "
                            SELECT assoc_id FROM controlled_vocabs
                            WHERE assoc_id = ?
                            LIMIT 1
                        ";

                        $checkStmt = $newDb->prepare($checkControlledVocabQuery);
                        $checkStmt->bind_param("i", $assocID);
                        $checkStmt->execute();
                        $checkStmt->store_result();

                        if ($checkStmt->num_rows > 0) {
                            echo "Skipping for symbolic.\n";
                        } else {
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


                                    // real keywords insertion here
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
    } catch (Exception $e) {

        echo "===================EXCEPTION===================\n";
        echo $e . "\n";
        echo "===================EXCEPTION===============\n";
    }

    echo "===============================================\n";
    echo "Total Match count: " . $totalMatches . "\n";
    echo "===============================================\n";
}
