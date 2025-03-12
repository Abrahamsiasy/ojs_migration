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

// Sample data from old database (replace this with your actual query results)
$oldKeywords = [
    "Ethics, Medical Ethics, Relgion, Islam, Glorious Qura'n,  Holy Shari`ah ",
    "Cytomegalovirus, oncogenic viruses, mrc-5, Wafergen Smartchip",
    "Bones, chick- embryo, dexamethasone, histomorphometry.",
    "Inhaler, Asthma, COPD, pMDI, Turbuhaler, ",
    "Escherichia coli, autophagy, Thr300Ala genetic variant,PCR",
    "ultraportable ultrasound,emergency ,urology",
    "Nutritional status Dislipidemia, Obesity, Diet, Chronic diseases",
    "Nutritional status Dislipidemia, Obesity, Diet, Chronic diseases ",
    "Chimerism, transplantation, STR, engraftment",
    "Platelet reactivity, HbA1c measurement, Diabetic patients.",
    "chronic renal failure, hemodialysis , intraocular pressure.",
    "Diabetes in young, obesity and diabetes, type 2 diabetes",
    "Breast Self-examination,  Prevalence, Breast Cancer Knowledge.",
    "Ischemic Mitral Regurgitation, ST-elevation MI.",
    "Osteoporosis; Dual-energy X- ray Absorptiometry; Obesity; Body mass index.",
    "Gaucher disease, Macrophage, Renal function test",
    "Oral cancer, elective neck dissections, squamous cell carcinoma, clinical negative neck, neck dissections, diagnosis",
    "glomerular filtration rate, hypertension, kidney disease, metabolic syndrome, obesity.",
    "Phage, Phage cocktails, Acinetobacter, native endolysin",
    "Human error; Human Error Assessment and ",
    "Key words: (Virtual,  synchronous, asynchronous, conventional classroom)",
    "BABBLING, FACIAL, COOING",
    "Imipramine, Antidepressant, tobacco, cigarettes smoking",
    "artificial intelligence, latin america  ",
    "Arsenic, batch adsorption, cadmium, Iraqi kaolin, thallium",
    "Key words: Hodgkin lymphoma, Stage, Sex distribution, Risk Category",
    "Keywords: Vitamin D, breast cancer, CA15-3, Her2/neu"
];

// Initialize an array to store the cleaned keywords
$allKeywords = [];

// Loop through each entry
foreach ($oldKeywords as $line) {
    // Step 1: Remove common prefixes like "Key words:" or "Keywords:"

    // Step 1: Remove common prefixes like "Key words:" or "Keywords:"
    $line = preg_replace("/^Key(\\s*words|words)?:\\s*/i", "", $line);

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
}

// Remove duplicates and re-index the array
$uniqueKeywords = array_values(array_unique($allKeywords));

// Print the cleaned keywords
echo "<pre>";
print_r($uniqueKeywords);
echo "</pre>";

?>
