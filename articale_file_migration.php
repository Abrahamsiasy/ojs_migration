<?php
require 'vendor/autoload.php'; // Load Dompdf via Composer

use Dompdf\Dompdf;
use Dompdf\Options;

// Database connection
$oldDb = new mysqli("localhost", "root", "", "iraqijms_esite");

// Check connection
if ($oldDb->connect_error) {
    die("Connection failed: " . $oldDb->connect_error);
}

// Query to fetch rows from the table
$sql = "SELECT id, title, pdf FROM esite_article";
$result = $oldDb->query($sql);

// Check if rows exist
if ($result->num_rows > 0) {
    // Ensure the directory exists
    $pdfDirectory = __DIR__ . "/upload/pdf/";
    if (!is_dir($pdfDirectory)) {
        mkdir($pdfDirectory, 0777, true);
    }

    // Loop through each row and generate PDF
    while ($row = $result->fetch_assoc()) {
        $id = $row['id'];
        $title = $row['title'];
        $pdfPath = $pdfDirectory . basename($row['pdf']); // Use the same file name from the database

        // Check if the PDF already exists
        if (!file_exists($pdfPath)) {
            // Initialize Dompdf
            $options = new Options();
            $options->set('defaultFont', 'Courier');
            $dompdf = new Dompdf($options);

            // Prepare HTML content for the PDF
            $html = "
                <html>
                <head>
                    <title>PDF for Article ID {$id}</title>
                </head>
                <body>
                    <h1>{$title}</h1>
                    <p>This is the generated PDF for the article titled '{$title}'.</p>
                </body>
                </html>
            ";

            // Load HTML content into Dompdf
            $dompdf->loadHtml($html);

            // Set paper size and orientation
            $dompdf->setPaper('A4', 'portrait');

            // Render the PDF
            $dompdf->render();

            // Save the PDF to the specified path
            file_put_contents($pdfPath, $dompdf->output());

            echo "Generated PDF for article ID {$id}: {$pdfPath}\n";
        } else {
            echo "PDF already exists for article ID {$id}: {$pdfPath}\n";
        }
    }
} else {
    echo "No articles found in the database.\n";
}

// Close the database connection
$oldDb->close();
?>
