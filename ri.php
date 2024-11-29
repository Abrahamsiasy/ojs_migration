<?php
// Database connection details
$oldDb = new mysqli("localhost", "root", "", "iraqijms_esite");
$newDb = new mysqli("localhost", "root", "", "ojs2");

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

// Close database connections
$oldDb->close();
$newDb->close();
?>
