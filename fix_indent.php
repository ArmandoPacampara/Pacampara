<?php
$file = 'app/view/pages/Checkout.php';
$content = file_get_contents($file);

// Convert all tabs to spaces (2 spaces per tab)
$content = str_replace("\t", "  ", $content);

// Fix the specific broken line (missing newline and proper spacing)
$content = preg_replace('/(<\/div>)\s+(<div class="progress-container">)/', "$1\n\n    $2", $content);

// Fix steps indentation (currently has 10 spaces, should be 8)
$content = str_replace('          <div class="step"', '        <div class="step"', $content);

// Remove trailing whitespace from each line
$lines = explode("\n", $content);
$lines = array_map('rtrim', $lines);
$content = implode("\n", $lines);

file_put_contents($file, $content);
echo "Indentation fixed!\n";
?>

