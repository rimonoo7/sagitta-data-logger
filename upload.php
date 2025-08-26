<?php
// upload.php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $upload_dir = "uploads/";
    
    // Create upload directory if it doesn't exist
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    // Get form data
    $latitude = $_POST['latitude'] ?? 'unknown';
    $longitude = $_POST['longitude'] ?? 'unknown';
    $timestamp = $_POST['timestamp'] ?? date('Y-m-d H:i:s');
    
    // Handle file upload
    if (isset($_FILES['csvfile'])) {
        $original_name = $_FILES['csvfile']['name'];
        $file_extension = pathinfo($original_name, PATHINFO_EXTENSION);
        
        // Create filename with location and timestamp
        $safe_lat = str_replace(['.', ' '], ['_', ''], $latitude);
        $safe_lon = str_replace(['.', ' '], ['_', ''], $longitude);
        $safe_time = str_replace([' ', ':'], ['_', '-'], $timestamp);
        
        $new_filename = "sagitta_{$safe_lat}_{$safe_lon}_{$safe_time}.{$file_extension}";
        $target_path = $upload_dir . $new_filename;
        
        if (move_uploaded_file($_FILES['csvfile']['tmp_name'], $target_path)) {
            // Save metadata to JSON file
            $metadata = [
                'filename' => $new_filename,
                'original_name' => $original_name,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'timestamp' => $timestamp,
                'upload_time' => date('Y-m-d H:i:s')
            ];
            
            // Read existing metadata or create new array
            $metadata_file = $upload_dir . 'metadata.json';
            $all_metadata = [];
            
            if (file_exists($metadata_file)) {
                $all_metadata = json_decode(file_get_contents($metadata_file), true);
            }
            
            // Add new metadata
            $all_metadata[] = $metadata;
            
            // Save updated metadata
            file_put_contents($metadata_file, json_encode($all_metadata, JSON_PRETTY_PRINT));
            
            echo "File uploaded successfully: " . $new_filename;
        } else {
            http_response_code(500);
            echo "Error uploading file";
        }
    } else {
        http_response_code(400);
        echo "No file uploaded";
    }
} else {
    http_response_code(405);
    echo "Method not allowed";
}
?>