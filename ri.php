<?php
// Database connection details
$oldDb = new mysqli("localhost", "root", "", "iraqijms_esite");
$newDb = new mysqli("localhost", "root", "", "ojs");

if ($oldDb->connect_error || $newDb->connect_error) {
    die("Connection failed: " . ($oldDb->connect_error ?? $newDb->connect_error));
}

// Function to map country to ISO 3166-1 alpha-2 code
function get_country_code($country) {
    $country_codes = [
        "Egypt" => "EG",
        "India" => "IN",
        "Iran" => "IR",
        "Iraq" => "IQ",
        "Jordan" => "JO",
        "Malaysia" => "MY",
        "Nigeria" => "NG",
        "Peru" => "PE",
        "Saudi Arabia" => "SA",
        "Sudan" => "SD",
        "Syria" => "SY",
        "United Kingdom" => "GB",
        "United States of America" => "US",
        "Yemen" => "YE"
    ];
    return $country_codes[$country] ?? "UNKNOWN";
}

// Get the last user_id from the `users` table in the new database
$result = $newDb->query("SELECT MAX(user_id) AS max_user_id FROM users");
$row = $result->fetch_assoc();
$lastUserId = $row['max_user_id'] ?? 0; // Default to 0 if the table is empty

$query = "SELECT * FROM esite_reviewer";  // Fetch reviewers from the old database
$result = $oldDb->query($query);

// Set up an HTML interface with a progress bar
echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Migration Progress</title>
    <style>
        .progress-bar-container {
            width: 100%;
            background-color: #f3f3f3;
            border: 1px solid #ccc;
            margin: 20px 0;
        }
        .progress-bar {
            height: 20px;
            background-color: #4caf50;
            width: 0%;
            text-align: center;
            color: white;
        }
        .status {
            font-size: 16px;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <h1>Database Migration</h1>
    <div class='progress-bar-container'>
        <div class='progress-bar' id='progress-bar'></div>
    </div>
    <div class='status' id='status'>Starting migration...</div>
    <script>
        function updateProgressBar(percent) {
            document.getElementById('progress-bar').style.width = percent + '%';
        }

        function updateStatus(message) {
            document.getElementById('status').innerText = message;
        }
    </script>
";

if ($result && $result->num_rows > 0) {
    $totalRows = $result->num_rows;
    $currentRow = 0;

    while ($row = $result->fetch_assoc()) {
        $userId = ++$lastUserId; // Increment user ID
        $username = strtolower($row['firstname']) . strtolower($row['lastname']) . $userId;
        // $password = "password_hash($row['xxpass'], PASSWORD_BCRYPT)"; // Hash the password
        $password = '$2y$10$yKZ03fgiwgMs.2eY1A3Jv.nfbC8rQKxbdclOk34fMZR05CqyboYum'; // Correctly assign the hash


        $email = $row['email'];
        $phone = $row['phone'] ?? $row['mobile'];
        $mailingAddress = $row['address'] ? "<p>{$row['address']}</p>" : null;
        $countryCode = get_country_code($row['country']);
        $dateRegistered = date("Y-m-d H:i:s");
        $lastLogin = $row['lastlogin'];
        $mustChangePassword = 1;
        $inline_help = 1;

        // Check if email already exists
        $emailCheckQuery = $newDb->prepare("SELECT user_id FROM users WHERE email = ?");
        $emailCheckQuery->bind_param("s", $email);
        $emailCheckQuery->execute();
        $emailCheckQuery->store_result();

        if ($emailCheckQuery->num_rows > 0) {
            // Email exists, add role if not already added
            $emailCheckQuery->bind_result($existingUserId);
            $emailCheckQuery->fetch();
            

            $role = 16; // Reviewer role
            $roleCheckQuery = $newDb->prepare("
                SELECT COUNT(*) FROM user_user_groups WHERE user_group_id = ? AND user_id = ?
            ");
            $roleCheckQuery->bind_param("ii", $role, $existingUserId);
            $roleCheckQuery->execute();
            $roleCheckQuery->bind_result($roleExists);
            $roleCheckQuery->fetch();
            $roleCheckQuery->close();

            if ($roleExists == 0) {
                $userGroupQuery = $newDb->prepare("
                    INSERT INTO user_user_groups (user_user_group_id, user_group_id, user_id) 
                    VALUES (NULL, ?, ?)
                ");
                $userGroupQuery->bind_param("ii", $role, $existingUserId);
                $userGroupQuery->execute();
                $userGroupQuery->close();
            }

            $currentRow++;
            $progress = ($currentRow / $totalRows) * 100;
            echo "<script>
                updateProgressBar($progress);
                updateStatus('User with email $email already exists. Role updated.');
            </script>";
            flush();
            ob_flush();
            continue;
        }

        // Insert into `users` table
        $usersQuery = $newDb->prepare("
            INSERT INTO users 
            (user_id, username, password, email, phone, mailing_address, country, date_registered, date_last_login, must_change_password, inline_help) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $usersQuery->bind_param(
            "isssssssssi",
            $userId,
            $username,
            $password,
            $email,
            $phone,
            $mailingAddress,
            $countryCode,
            $dateRegistered,
            $lastLogin,
            $mustChangePassword,
            $inline_help
        );
        $usersQuery->execute();

        // Insert into `user_settings` table
        $settings = [
            ['locale' => 'en', 'setting_name' => 'affiliation', 'setting_value' => $row['affilate']],
            ['locale' => 'en', 'setting_name' => 'familyName', 'setting_value' => $row['lastname']],
            ['locale' => 'en', 'setting_name' => 'givenName', 'setting_value' => $row['firstname']],
            ['locale' => '', 'setting_name' => 'orcid', 'setting_value' => ''], // ORCID not available
            ['locale' => 'en', 'setting_name' => 'preferredPublicName', 'setting_value' => $row['firstname']],
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

        // Insert role
        $role = 16; // Reviewer role
        $userGroupQuery = $newDb->prepare("
            INSERT INTO user_user_groups 
            (user_user_group_id, user_group_id, user_id) 
            VALUES (NULL, ?, ?)
        ");
        $userGroupQuery->bind_param("ii", $role, $userId);
        $userGroupQuery->execute();

        // Update the progress bar
        $currentRow++;
        $progress = ($currentRow / $totalRows) * 100;
        echo "<script>
            updateProgressBar($progress);
            updateStatus('Migrating: $currentRow of $totalRows');
        </script>";
        flush();
        ob_flush();
    }

    echo "<script>updateStatus('Migration completed successfully!');</script>";
} else {
    echo "<script>updateStatus('No data found in the old database.');</script>";
}

echo "</body></html>";

// SET FOREIGN_KEY_CHECKS = 0;

// TRUNCATE TABLE author_settings;
// TRUNCATE TABLE authors;

// TRUNCATE TABLE files;
// TRUNCATE TABLE issue_files;
// TRUNCATE TABLE issue_galley_settings;
// TRUNCATE TABLE issue_galleys;
// TRUNCATE TABLE issue_settings;
// TRUNCATE TABLE issues;

// TRUNCATE TABLE publication_settings;
// TRUNCATE TABLE publications;
// TRUNCATE TABLE publication_galleys;

// TRUNCATE TABLE submission_file_settings;
// TRUNCATE TABLE submission_files;
// TRUNCATE TABLE submissions;
// TRUNCATE TABLE submission_file_revisions;
// TRUNCATE TABLE submission_settings;


// TRUNCATE TABLE user_group_settings;
// TRUNCATE TABLE user_group_stage;
// TRUNCATE TABLE user_groups;
// TRUNCATE TABLE user_interests;
// TRUNCATE TABLE user_settings;
// TRUNCATE TABLE user_user_groups;
// TRUNCATE TABLE users;

// SET FOREIGN_KEY_CHECKS = 1;



// INSERT INTO users (user_id, username, password, email, url, phone, mailing_address, billing_address, country, locales, gossip, date_last_email, date_registered, date_validated, date_last_login, must_change_password, auth_id, auth_str, disabled, disabled_reason, inline_help) VALUES
// (1, 'ojs_admin', '$2y$10$VQIoZp5nHqal6yPhhlLP4O.uH7EJ0pcZ4hSnm9hnhDkHU.x2Ilt7e', 'abrahamsisayadem@gmail.com', NULL, NULL, NULL, NULL, NULL, '[]', NULL, NULL, '2024-11-20 15:35:49', NULL, '2024-12-04 12:35:31', NULL, NULL, NULL, 0, NULL, 1);


// INSERT INTO user_groups (user_group_id, context_id, role_id, is_default, show_title, permit_self_registration, permit_metadata_edit) VALUES
// (1, 0, 1, 1, 1, 0, 0),
// (2, 1, 16, 1, 1, 0, 1),
// (3, 1, 16, 1, 1, 0, 1),
// (4, 1, 16, 1, 1, 0, 1),
// (5, 1, 17, 1, 1, 0, 1),
// (6, 1, 17, 1, 1, 0, 0),
// (7, 1, 4097, 1, 1, 0, 0),
// (8, 1, 4097, 1, 1, 0, 0),
// (9, 1, 4097, 1, 1, 0, 0),
// (10, 1, 4097, 1, 1, 0, 0),
// (11, 1, 4097, 1, 1, 0, 0),
// (12, 1, 4097, 1, 1, 0, 0),
// (13, 1, 4097, 1, 1, 0, 0),
// (14, 1, 65536, 1, 1, 1, 0),
// (15, 1, 65536, 1, 1, 0, 0),
// (16, 1, 4096, 1, 1, 1, 0),
// (17, 1, 1048576, 1, 1, 1, 0),
// (18, 1, 2097152, 1, 1, 0, 0);



// INSERT INTO user_group_settings (user_group_setting_id, user_group_id, locale, setting_name, setting_value) VALUES
// (1, 1, 'en', 'name', 'Site Admin'),
// (2, 2, '', 'nameLocaleKey', 'default.groups.name.manager'),
// (3, 2, '', 'abbrevLocaleKey', 'default.groups.abbrev.manager'),
// (4, 2, 'en', 'abbrev', 'JM'),
// (5, 2, 'en', 'name', 'Journal manager'),
// (6, 3, '', 'nameLocaleKey', 'default.groups.name.editor'),
// (7, 3, '', 'abbrevLocaleKey', 'default.groups.abbrev.editor'),
// (8, 3, 'en', 'abbrev', 'JE'),
// (9, 3, 'en', 'name', 'Journal editor'),
// (10, 4, '', 'nameLocaleKey', 'default.groups.name.productionEditor'),
// (11, 4, '', 'abbrevLocaleKey', 'default.groups.abbrev.productionEditor'),
// (12, 4, 'en', 'abbrev', 'ProdE'),
// (13, 4, 'en', 'name', 'Production editor'),
// (14, 5, '', 'nameLocaleKey', 'default.groups.name.sectionEditor'),
// (15, 5, '', 'abbrevLocaleKey', 'default.groups.abbrev.sectionEditor'),
// (16, 5, 'en', 'abbrev', 'SecE'),
// (17, 5, 'en', 'name', 'Section editor'),
// (18, 6, '', 'nameLocaleKey', 'default.groups.name.guestEditor'),
// (19, 6, '', 'abbrevLocaleKey', 'default.groups.abbrev.guestEditor'),
// (20, 6, 'en', 'abbrev', 'GE'),
// (21, 6, 'en', 'name', 'Guest editor'),
// (22, 7, '', 'nameLocaleKey', 'default.groups.name.copyeditor'),
// (23, 7, '', 'abbrevLocaleKey', 'default.groups.abbrev.copyeditor'),
// (24, 7, 'en', 'abbrev', 'CE'),
// (25, 7, 'en', 'name', 'Copyeditor'),
// (26, 8, '', 'nameLocaleKey', 'default.groups.name.designer'),
// (27, 8, '', 'abbrevLocaleKey', 'default.groups.abbrev.designer'),
// (28, 8, 'en', 'abbrev', 'Design'),
// (29, 8, 'en', 'name', 'Designer'),
// (30, 9, '', 'nameLocaleKey', 'default.groups.name.funding'),
// (31, 9, '', 'abbrevLocaleKey', 'default.groups.abbrev.funding'),
// (32, 9, 'en', 'abbrev', 'FC'),
// (33, 9, 'en', 'name', 'Funding coordinator'),
// (34, 10, '', 'nameLocaleKey', 'default.groups.name.indexer'),
// (35, 10, '', 'abbrevLocaleKey', 'default.groups.abbrev.indexer'),
// (36, 10, 'en', 'abbrev', 'IND'),

// (37, 10, 'en', 'name', 'Indexer'),
// (38, 11, '', 'nameLocaleKey', 'default.groups.name.layoutEditor'),
// (39, 11, '', 'abbrevLocaleKey', 'default.groups.abbrev.layoutEditor'),
// (40, 11, 'en', 'abbrev', 'LE'),
// (41, 11, 'en', 'name', 'Layout Editor'),
// (42, 12, '', 'nameLocaleKey', 'default.groups.name.marketing'),
// (43, 12, '', 'abbrevLocaleKey', 'default.groups.abbrev.marketing'),
// (44, 12, 'en', 'abbrev', 'MS'),
// (45, 12, 'en', 'name', 'Marketing and sales coordinator'),
// (46, 13, '', 'nameLocaleKey', 'default.groups.name.proofreader'),
// (47, 13, '', 'abbrevLocaleKey', 'default.groups.abbrev.proofreader'),
// (48, 13, 'en', 'abbrev', 'PR'),
// (49, 13, 'en', 'name', 'Proofreader'),
// (50, 14, '', 'nameLocaleKey', 'default.groups.name.author'),
// (51, 14, '', 'abbrevLocaleKey', 'default.groups.abbrev.author'),
// (52, 14, 'en', 'abbrev', 'AU'),
// (53, 14, 'en', 'name', 'Author'),
// (54, 15, '', 'nameLocaleKey', 'default.groups.name.translator'),
// (55, 15, '', 'abbrevLocaleKey', 'default.groups.abbrev.translator'),
// (56, 15, 'en', 'abbrev', 'Trans'),
// (57, 15, 'en', 'name', 'Translator'),
// (58, 16, '', 'nameLocaleKey', 'default.groups.name.externalReviewer'),
// (59, 16, '', 'abbrevLocaleKey', 'default.groups.abbrev.externalReviewer'),
// (60, 16, 'en', 'abbrev', 'R'),
// (61, 16, 'en', 'name', 'Reviewer'),
// (62, 17, '', 'nameLocaleKey', 'default.groups.name.reader'),
// (63, 17, '', 'abbrevLocaleKey', 'default.groups.abbrev.reader'),
// (64, 17, 'en', 'abbrev', 'Read'),
// (65, 17, 'en', 'name', 'Reader'),
// (66, 18, '', 'nameLocaleKey', 'default.groups.name.subscriptionManager'),
// (67, 18, '', 'abbrevLocaleKey', 'default.groups.abbrev.subscriptionManager'),
// (68, 18, 'en', 'abbrev', 'SubM'),
// (69, 18, 'en', 'name', 'Subscription Manager');

// INSERT INTO user_group_stage (user_group_stage_id, context_id, user_group_id, stage_id) VALUES
// (1, 1, 3, 1),
// (2, 1, 3, 3),
// (3, 1, 3, 4),
// (4, 1, 3, 5),
// (5, 1, 4, 4),
// (6, 1, 4, 5),
// (7, 1, 5, 1),
// (8, 1, 5, 3),
// (9, 1, 5, 4),
// (10, 1, 5, 5),
// (11, 1, 6, 1),
// (12, 1, 6, 3),
// (13, 1, 6, 4),
// (14, 1, 6, 5),
// (15, 1, 7, 4),
// (16, 1, 8, 5),
// (17, 1, 9, 1),
// (18, 1, 9, 3),
// (19, 1, 10, 5),
// (20, 1, 11, 5),
// (21, 1, 12, 4),
// (22, 1, 13, 5),
// (23, 1, 14, 1),
// (24, 1, 14, 3),
// (25, 1, 14, 4),
// (26, 1, 14, 5),
// (27, 1, 15, 1),
// (28, 1, 15, 3),
// (29, 1, 15, 4),
// (30, 1, 15, 5),
// (31, 1, 16, 3);

// INSERT INTO user_settings (user_setting_id, user_id, locale, setting_name, setting_value) VALUES
// (1, 1, 'en', 'familyName', 'ojs_admin'),
// (2, 1, 'en', 'givenName', 'ojs_admin');


// INSERT INTO user_user_groups (user_user_group_id, user_group_id, user_id) VALUES
// (1, 1, 1),
// (2, 2, 1);

?>

// Close database connections
$oldDb->close();
$newDb->close();
?>
