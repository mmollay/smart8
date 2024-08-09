<?php
// Define the path to the change_log.txt file
$filePath = '../change_log.txt';

// Check if the file exists
if (file_exists($filePath)) {
	// Open the file and read its content
	$content = file_get_contents($filePath);

	// Start of the HTML structure
	echo '<div align="center">
            <div style="max-width:800px">';

	// Split the content by lines
	$lines = explode("\n", $content);

	// Variable to keep track of the current version being processed
	$currentVersion = '';

	foreach ($lines as $line) {
		// Skip empty lines
		if (trim($line) === '') {
			continue;
		}

		// Check if the line is a version header
		if (strpos($line, 'Version') === 0) {
			// If there's a current version being processed, close its tags first
			if (!empty($currentVersion)) {
				echo '</ul></div>';
			}
			// Update the current version
			$currentVersion = $line;
			// Output the version header
			echo "<div class=\"message ui\">
                    <div align=\"left\"><b>$currentVersion</b></div>
                    <ul class=\"list\">";
		} else {
			// For regular lines, add them as list items
			echo "<li>$line</li>";
		}
	}

	// Close the last version's tags if any version was processed
	if (!empty($currentVersion)) {
		echo '</ul></div>';
	}

	// End of the HTML structure
	echo '</div></div>';
} else {
	// If the file doesn't exist, output an error message
	echo "<html><body><p>The file 'change_log.txt' was not found.</p></body></html>";
}
?>