<?php

include('../../../inc/includes.php');

Session::checkRight("plugin_automator", READ);

Html::header('Automator', $_SERVER['PHP_SELF'], "plugins", "automator");

echo '<div id="automator-root"></div>';
echo '<script>window.glpi_csrf_token = "' . Session::getNewCSRFToken() . '";</script>';

// Dynamic asset discovery
$assets_dir = GLPI_ROOT . '/marketplace/automator/public/automator/assets';
$js_file = 'index.js'; // Fallback
$css_file = 'index.css'; // Fallback

if (is_dir($assets_dir)) {
    $files = scandir($assets_dir);
    foreach ($files as $file) {
        if (preg_match('/^index.*\.js$/', $file)) {
            $js_file = $file;
        }
        if (preg_match('/^index.*\.css$/', $file)) {
            $css_file = $file;
        }
    }
}

global $CFG_GLPI;
$root_doc = $CFG_GLPI['root_doc'];
echo '<script type="module" src="' . $root_doc . '/marketplace/automator/public/automator/assets/' . $js_file . '"></script>';
echo '<link rel="stylesheet" href="' . $root_doc . '/marketplace/automator/public/automator/assets/' . $css_file . '">';

Html::footer();
