<?php

/**
 * Simple unused variable checker for PHP files
 * Detects variables that are assigned but never used
 */
function checkUnusedVariables($file)
{
    $content = file_get_contents($file);
    $lines = explode("\n", $content);
    $errors = [];

    foreach ($lines as $lineNum => $line) {
        // Skip comments
        if (trim($line) === '' || strpos(trim($line), '//') === 0 || strpos(trim($line), '#') === 0) {
            continue;
        }

        // Look for variable assignments like $someVar = 'value';
        if (preg_match('/\$(\w+)\s*=/', $line, $matches)) {
            $varName = $matches[1];
            $varPattern = '/\$'.preg_quote($varName, '/').'\b/';

            // Count occurrences of this variable in the file
            $occurrences = preg_match_all($varPattern, $content);

            // If variable appears only once (the assignment), it's unused
            if ($occurrences === 1) {
                $errors[] = [
                    'line' => $lineNum + 1,
                    'variable' => $varName,
                    'content' => trim($line),
                ];
            }
        }
    }

    return $errors;
}

// Get files to check from git staged files (run from root dir)
exec('cd .. && git diff --cached --name-only --diff-filter=ACM | grep "\.php$" | grep "^www/"', $files);

// Remove 'www/' prefix from paths since we're working from www directory
$files = array_map(function ($file) {
    return str_replace('www/', '', $file);
}, $files);

// echo "Checking files: " . implode(', ', $files) . "\n"; // Debug disabled

$hasErrors = false;

foreach ($files as $file) {
    if (file_exists($file)) {
        $errors = checkUnusedVariables($file);

        if (! empty($errors)) {
            $hasErrors = true;
            echo "Unused variables found in $file:\n";

            foreach ($errors as $error) {
                echo "  Line {$error['line']}: Variable '\${$error['variable']}' is assigned but never used\n";
                echo "    {$error['content']}\n";
            }
            echo "\n";
        }
    }
}

if ($hasErrors) {
    echo "❌ Commit blocked: Please remove unused variables\n";
    exit(1);
} else {
    echo "✅ No unused variables detected\n";
    exit(0);
}
