<?php
session_start();
include('config.php');

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start output buffering to capture any output
ob_start();

echo "<h2>Import Debug Information</h2>";
echo "<pre>";

echo "=== BASIC CHECKS ===\n";
echo "Request Method: " . $_SERVER['REQUEST_METHOD'] . "\n";
echo "POST data:\n" . print_r($_POST, true) . "\n";
echo "FILES data:\n" . print_r($_FILES, true) . "\n";

// Check if this is even a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo "ERROR: Not a POST request\n";
    echo "</pre>";
    exit;
}

// Check if import button was pressed
if (!isset($_POST['import'])) {
    echo "ERROR: 'import' parameter not found in POST data\n";
    echo "Available POST keys: " . implode(', ', array_keys($_POST)) . "\n";
    echo "</pre>";
    exit;
}

echo "✓ Import button pressed\n";

// Check type
$type = $_POST['type'] ?? '';
if (empty($type)) {
    echo "ERROR: Import type not specified\n";
    echo "</pre>";
    exit;
}

echo "✓ Import type: $type\n";

// Check file upload
if (!isset($_FILES['importFile'])) {
    echo "ERROR: No file uploaded\n";
    echo "</pre>";
    exit;
}

$file_info = $_FILES['importFile'];
echo "✓ File upload detected\n";
echo "File details:\n";
echo "  - Name: " . $file_info['name'] . "\n";
echo "  - Size: " . $file_info['size'] . " bytes\n";
echo "  - Type: " . $file_info['type'] . "\n";
echo "  - Temp name: " . $file_info['tmp_name'] . "\n";
echo "  - Error code: " . $file_info['error'] . "\n";

// Check upload error
if ($file_info['error'] !== UPLOAD_ERR_OK) {
    $upload_errors = [
        UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize directive',
        UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE directive',
        UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
        UPLOAD_ERR_NO_FILE => 'No file was uploaded',
        UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder',
        UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
        UPLOAD_ERR_EXTENSION => 'File upload stopped by extension'
    ];
    echo "ERROR: Upload failed - " . ($upload_errors[$file_info['error']] ?? "Unknown error " . $file_info['error']) . "\n";
    echo "</pre>";
    exit;
}

echo "✓ File uploaded successfully\n";

// Check file extension
$ext = strtolower(pathinfo($file_info['name'], PATHINFO_EXTENSION));
echo "File extension: $ext\n";
if ($ext !== 'csv') {
    echo "ERROR: Invalid file extension. Expected: csv, Got: $ext\n";
    echo "</pre>";
    exit;
}

echo "✓ File extension is valid\n";

// Check if temp file exists
$filename = $file_info['tmp_name'];
if (!file_exists($filename)) {
    echo "ERROR: Temporary file does not exist: $filename\n";
    echo "</pre>";
    exit;
}

echo "✓ Temporary file exists\n";

// Try to open file
$file = fopen($filename, "r");
if (!$file) {
    echo "ERROR: Cannot open file for reading\n";
    echo "</pre>";
    exit;
}

echo "✓ File opened successfully\n";

// Read and display the entire file content for debugging
echo "\n=== FILE CONTENT ===\n";
$file_content = file_get_contents($filename);
echo "Raw file content:\n";
echo htmlspecialchars($file_content) . "\n";
echo "File size: " . strlen($file_content) . " characters\n";

// Reset file pointer
rewind($file);

// Read header
$header = fgetcsv($file);
if ($header === false) {
    echo "ERROR: Cannot read CSV header\n";
    fclose($file);
    echo "</pre>";
    exit;
}

echo "\n=== CSV PARSING ===\n";
echo "Header row: " . implode(" | ", $header) . "\n";
echo "Header count: " . count($header) . "\n";

// Read all data rows
$all_data = [];
$row_num = 1;
while (($data = fgetcsv($file, 1000, ",")) !== false) {
    $row_num++;
    echo "Row $row_num: " . implode(" | ", $data) . " (columns: " . count($data) . ")\n";
    $all_data[] = $data;
}

echo "Total data rows read: " . count($all_data) . "\n";

fclose($file);

if (empty($all_data)) {
    echo "ERROR: No data rows found in CSV\n";
    echo "</pre>";
    exit;
}

echo "\n=== DATABASE OPERATIONS ===\n";

// Function to get ID by name
function getIdByName($con, $table, $nameCol, $idCol, $name) {
    $allowedTables = ['professors', 'semester', 'buildings'];
    $allowedCols = ['name', 'id', 'building_name'];

    if (!in_array($table, $allowedTables) || !in_array($nameCol, $allowedCols) || !in_array($idCol, $allowedCols)) {
        echo "  ERROR: Invalid table or column name\n";
        return null;
    }

    $sql = "SELECT `$idCol` FROM `$table` WHERE `$nameCol` = ?";
    $stmt = $con->prepare($sql);

    if (!$stmt) {
        echo "  ERROR: Prepare failed: " . $con->error . "\n";
        return null;
    }

    $stmt->bind_param("s", $name);
    if (!$stmt->execute()) {
        echo "  ERROR: Execute failed: " . $stmt->error . "\n";
        $stmt->close();
        return null;
    }

    $result = $stmt->get_result();
    if (!$result) {
        echo "  ERROR: get_result failed: " . $stmt->error . "\n";
        $stmt->close();
        return null;
    }

    $row = $result->fetch_assoc();
    $stmt->close();
    
    $id = $row ? $row[$idCol] : null;
    echo "  Query: $sql with '$name' = ID: " . ($id ?: 'NOT FOUND') . "\n";
    return $id;
}

// Process based on type
$successCount = 0;
$errorCount = 0;

if ($type === 'courses') {
    echo "Processing courses...\n";
    
    if (count($header) < 11) {
        echo "ERROR: Expected at least 11 columns for courses, got " . count($header) . "\n";
        echo "</pre>";
        exit;
    }
    
    // First, let's check if the required reference data exists
    echo "\nChecking reference data...\n";
    
    // Check professors
    $professors_query = "SELECT id, name FROM professors";
    $professors_result = $con->query($professors_query);
    echo "Professors in database:\n";
    if ($professors_result && $professors_result->num_rows > 0) {
        while ($row = $professors_result->fetch_assoc()) {
            echo "  ID: {$row['id']}, Name: {$row['name']}\n";
        }
    } else {
        echo "  No professors found!\n";
    }
    
    // Check semesters
    $semesters_query = "SELECT id, name FROM semester";
    $semesters_result = $con->query($semesters_query);
    echo "Semesters in database:\n";
    if ($semesters_result && $semesters_result->num_rows > 0) {
        while ($row = $semesters_result->fetch_assoc()) {
            echo "  ID: {$row['id']}, Name: {$row['name']}\n";
        }
    } else {
        echo "  No semesters found!\n";
    }
    
    // Check buildings
    $buildings_query = "SELECT id, building_name FROM buildings";
    $buildings_result = $con->query($buildings_query);
    echo "Buildings in database:\n";
    if ($buildings_result && $buildings_result->num_rows > 0) {
        while ($row = $buildings_result->fetch_assoc()) {
            echo "  ID: {$row['id']}, Name: {$row['building_name']}\n";
        }
    } else {
        echo "  No buildings found!\n";
    }
    
    echo "\nProcessing course rows...\n";
    
    $query = "INSERT INTO courses (name, credits, lecture_hours, tutorial_hours, practical_hours, professor_id, semester_id, building_id, students_enrolled, requires_projector, course_type) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $con->prepare($query);
    
    if (!$stmt) {
        echo "ERROR: Database prepare failed: " . $con->error . "\n";
        echo "</pre>";
        exit;
    }
    
    foreach ($all_data as $index => $data) {
        $row_num = $index + 2; // +2 because we start from row 2 (after header)
        echo "\nProcessing row $row_num...\n";
        
        if (count($data) < 11) {
            echo "  SKIP: Insufficient columns (" . count($data) . "/11)\n";
            $errorCount++;
            continue;
        }
        
        if (empty(array_filter($data, function($v) { return trim($v) !== ''; }))) {
            echo "  SKIP: All empty values\n";
            $errorCount++;
            continue;
        }
        
        // Extract and sanitize data
        $name = htmlspecialchars(trim($data[0]), ENT_QUOTES, 'UTF-8');
        $credits = (int)$data[1];
        $lecture_hours = (int)$data[2];
        $tutorial_hours = (int)$data[3];
        $practical_hours = (int)$data[4];
        $professor_name = htmlspecialchars(trim($data[5]), ENT_QUOTES, 'UTF-8');
        $semester_name = htmlspecialchars(trim($data[6]), ENT_QUOTES, 'UTF-8');
        $building_name = htmlspecialchars(trim($data[7]), ENT_QUOTES, 'UTF-8');
        $students_enrolled = (int)$data[8];
        $requires_projector = (int)$data[9];
        $course_type = htmlspecialchars(trim($data[10]), ENT_QUOTES, 'UTF-8');
        
        echo "  Course: $name\n";
        echo "  Looking for professor: '$professor_name'\n";
        echo "  Looking for semester: '$semester_name'\n";
        echo "  Looking for building: '$building_name'\n";
        
        // Get foreign key IDs
        $professor_id = getIdByName($con, 'professors', 'name', 'id', $professor_name);
        $semester_id = getIdByName($con, 'semester', 'name', 'id', $semester_name);
        $building_id = getIdByName($con, 'buildings', 'building_name', 'id', $building_name);
        
        echo "  Resolved IDs - Professor: $professor_id, Semester: $semester_id, Building: $building_id\n";
        
        if (!$professor_id || !$semester_id || !$building_id) {
            echo "  SKIP: Missing required IDs\n";
            $errorCount++;
            continue;
        }
        
        // Bind and execute
        $stmt->bind_param("siiiiiiiiss", $name, $credits, $lecture_hours, $tutorial_hours, $practical_hours, $professor_id, $semester_id, $building_id, $students_enrolled, $requires_projector, $course_type);
        
        if ($stmt->execute()) {
            echo "  SUCCESS: Inserted course '$name'\n";
            $successCount++;
        } else {
            echo "  ERROR: Insert failed - " . $stmt->error . "\n";
            $errorCount++;
        }
    }
    
    $stmt->close();
}

echo "\n=== FINAL RESULTS ===\n";
echo "Success: $successCount\n";
echo "Errors: $errorCount\n";
echo "Total processed: " . ($successCount + $errorCount) . "\n";

echo "</pre>";

// If this was a real import attempt, redirect back
if (isset($_POST['import'])) {
    echo "<p><a href='index.php'>Go back to main page</a></p>";
    echo "<p>If you want to proceed with the actual import, the debug information above shows what will happen.</p>";
}
?>