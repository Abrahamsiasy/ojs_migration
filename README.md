README: Integrating Another Journal into OJS
This project facilitates the integration of an existing journal's data into the Open Journal Systems (OJS) platform. It includes functionality for migrating articles, authors, keywords, issues, and reviewer data from a source database into the new OJS system.

Prerequisites:
To use this integration, you will need the following:

PHP 7.x or higher installed on your server.

MySQL database for both the source journal and OJS.

OJS system setup and configured (Target DB).

Database credentials for both the source journal and OJS databases.

Files in this Repository:
sync_main.php
This is the primary script that connects to both the source database (old journal) and the target database (OJS). It executes the migration for various components such as articles, authors, keywords, issues, and reviewers.

is.php
Contains the code for handling the issues in the journal (e.g., year, volume, and issue data). This file is responsible for ensuring that the issues from the source journal are correctly mapped to the OJS structure.

ri.php
Responsible for migrating reviewer data. It extracts the reviewers’ information from the source journal and ensures that the data is correctly placed in the OJS system.

publication_migrate.php
Manages the migration of publication-related data, including articles, metadata, and other related data from the old journal system to OJS.

article-authors.php
This file handles the migration of authors associated with articles. It maps authors from the source journal to OJS, ensuring that all author metadata is transferred properly.

article-keywords.php
Contains the functionality for migrating article keywords. It ensures that all keywords associated with articles are transferred to the OJS system accurately.

Installation:
Database Setup:

Ensure that you have access to both the old journal's database (iraqijms_esite) and the new OJS database (ojs_fresh).

Both databases should be hosted on the same MySQL server, but this script can also handle different server configurations.

Database Credentials:

Open the sync_main.php file.

Update the database credentials for both the old journal and OJS systems:

php
Copy
Edit
$oldDb = new mysqli("localhost", "root", "", "iraqijms_esite");
$newDb = new mysqli("localhost", "root", "", "ojs_fresh");
Replace the localhost, root, password, and database names with the actual credentials for your setup.

Libraries:

Ensure that you have all required libraries available in your environment (e.g., mysqli and PDO for database operations, as well as any custom classes/functions you might need).

Script Execution:

Run the migration script by navigating to the directory containing sync_main.php and accessing it via your browser or command line.

Functionality:
The main sync_main.php file connects to both the source and OJS databases and processes data migration:

Issues (is.php):

This file migrates the journal's issues, ensuring that the year, volume, and issue data are transferred correctly from the old system into the OJS system.

Reviewers (ri.php):

Migrates reviewer-related information, mapping reviewers from the old journal system to OJS's reviewer tables.

Publications (publication_migrate.php):

Handles the migration of article data, including metadata, publication details, and other related information.

Authors (article-authors.php):

Maps authors from the old journal to the OJS system, associating them with the correct articles.

Keywords (article-keywords.php):

Migrates article keywords, ensuring that the keywords are correctly mapped to the corresponding articles in the OJS system.

Error Handling:
If the script encounters errors during migration, the following checks are performed:

Database Connection Errors: If either of the database connections fails, an error message will be displayed, and the script will stop execution.

Data Mapping Issues: In case of any inconsistencies with data (missing required fields, invalid foreign keys, etc.), the migration for that record will be skipped, and a log will be generated for manual inspection.

Additional Notes:
Data Mapping: The script assumes that the source journal's data schema closely follows the structure required by OJS. If your schema differs significantly, adjustments may be required to handle custom fields or table structures.

Testing: Before running this script on a live environment, it is highly recommended to run it in a development or staging environment. Backup both the old journal's database and the OJS database before proceeding with the migration.

Log Files: The script may generate log files for successful and failed migration attempts. This helps you monitor and debug the process if necessary.

To Do:
Ensure proper mappings for any additional fields that might be needed.

Handle edge cases for missing or malformed data in the source journal.

License:
This code is distributed under the MIT License. Feel free to modify, distribute, and use it in your projects.

If you have any questions or run into issues, feel free to contact the support team or open an issue in the repository.