<?php
// update_system.php - One-time script to set up the profile image system

// Include database connection
include 'config/db.php';

// Check if profile_image column exists in the users table
$column_check = $conn->query("SHOW COLUMNS FROM users LIKE 'profile_image'");
if ($column_check->num_rows === 0) {
    // Add profile_image column
    echo "Adding profile_image column to users table...<br>";
    $conn->query("ALTER TABLE users ADD COLUMN profile_image VARCHAR(255) DEFAULT 'images/default-profile.png'");
    echo "Column added successfully.<br>";
} else {
    echo "Profile image column already exists.<br>";
}

// Create directories if they don't exist
$directories = ['images', 'uploads', 'uploads/profiles'];
foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        echo "Creating directory: $dir<br>";
        mkdir($dir, 0755, true);
        echo "Directory created.<br>";
    } else {
        echo "Directory $dir already exists.<br>";
    }
}

// Save the default profile image
$default_image_path = 'images/default-profile.png';
$default_svg_content = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200">
  <!-- Background Circle -->
  <circle cx="100" cy="100" r="100" fill="#2196F3"/>
  
  <!-- Person Silhouette -->
  <g fill="#FFFFFF">
    <!-- Head -->
    <circle cx="100" cy="70" r="40"/>
    
    <!-- Body -->
    <path d="M160,190 C160,150 130,125 100,125 C70,125 40,150 40,190 L160,190 Z"/>
  </g>
</svg>
SVG;

// Check if the default image exists
if (!file_exists($default_image_path)) {
    echo "Creating default profile image...<br>";
    
    // Convert SVG to PNG using GD Library
    $temp_svg_file = tempnam(sys_get_temp_dir(), 'svg_');
    file_put_contents($temp_svg_file, $default_svg_content);
    
    // If you have ImageMagick installed and enabled in PHP (uncomment to use)
    /*
    $imagick = new Imagick();
    $imagick->readImageBlob($default_svg_content);
    $imagick->setImageFormat('png');
    $imagick->resizeImage(200, 200, Imagick::FILTER_LANCZOS, 1);
    file_put_contents($default_image_path, $imagick->getImageBlob());
    */
    
    // Alternative approach using pre-rendered PNG for sites without ImageMagick
    // Create a simple blue circle with white silhouette using GD
    $size = 200;
    $im = imagecreatetruecolor($size, $size);
    
    // Colors
    $blue = imagecolorallocate($im, 33, 150, 243); // #2196F3
    $white = imagecolorallocate($im, 255, 255, 255);
    $transparent = imagecolorallocate($im, 0, 0, 0);
    
    // Make the background transparent
    imagefilledrectangle($im, 0, 0, $size, $size, $transparent);
    
    // Make the image transparent
    imagecolortransparent($im, $transparent);
    
    // Draw a blue circle for background
    imagefilledellipse($im, $size/2, $size/2, $size, $size, $blue);
    
    // Draw a white circle for head
    imagefilledellipse($im, $size/2, $size/2 - 30, 80, 80, $white);
    
    // Draw a white half ellipse for body
    imagefilledarc($im, $size/2, $size/2 + 40, 120, 120, 0, 180, $white, IMG_ARC_PIE);
    
    // Save the image
    imagepng($im, $default_image_path);
    imagedestroy($im);
    
    echo "Default profile image created.<br>";
} else {
    echo "Default profile image already exists.<br>";
}

// Update all users to use the default image if they don't have one set
echo "Updating users without profile images...<br>";
$conn->query("UPDATE users SET profile_image = 'images/default-profile.png' WHERE profile_image IS NULL OR profile_image = ''");
echo "Users updated.<br>";

echo "<br>System update completed. <a href='index.php'>Return to homepage</a>";
?>