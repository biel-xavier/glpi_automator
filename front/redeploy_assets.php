<?php

include('../../../inc/includes.php');

Session::checkRight("config", UPDATE);

Html::header('Automator Asset Redeploy', $_SERVER['PHP_SELF'], "plugins", "automator");

echo "<h2>Automator Asset Redeploy</h2>";

$source = dirname(__DIR__) . '/dist';
$dest = GLPI_ROOT . '/public/automator';

if (!is_dir($source)) {
    echo "<div class='error'>Source directory not found: $source</div>";
} else {
    echo "<p>Copying from $source to $dest...</p>";

    // Use the existing function from hook.php if available, 
    // but since we are running as www-data here, we can just do it.

    function sync_assets($src, $dst)
    {
        // Delete destination if it exists to ensure clean slate
        if (is_dir($dst)) {
            $files = array_diff(scandir($dst), ['.', '..']);
            foreach ($files as $file) {
                $path = "$dst/$file";
                if (is_dir($path)) {
                    // Simple recursive delete for assets/ subfolders if any
                    $subfiles = array_diff(scandir($path), ['.', '..']);
                    foreach ($subfiles as $sfile) unlink("$path/$sfile");
                    rmdir($path);
                } else {
                    unlink($path);
                }
            }
        }

        $dir = opendir($src);
        @mkdir($dst, 0755, true);
        while (false !== ($file = readdir($dir))) {
            if (($file != '.') && ($file != '..')) {
                if (is_dir($src . '/' . $file)) {
                    sync_assets($src . '/' . $file, $dst . '/' . $file);
                } else {
                    if (copy($src . '/' . $file, $dst . '/' . $file)) {
                        echo "Copied: $file<br>";
                    } else {
                        echo "Failed to copy: $file<br>";
                    }
                }
            }
        }
        closedir($dir);
    }

    echo "<p>Cleaning old assets and deploying new ones...</p>";
    sync_assets($source, $dest);
    echo "<h3>Done!</h3>";
    echo "<a href='rule.php' class='vsubmit'>Go back to Rules</a>";
}

Html::footer();
