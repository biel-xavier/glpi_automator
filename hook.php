<?php

/**
 * Install the plugin.
 *
 * @return bool
 */
function plugin_automator_install()
{
    global $DB;
    $migration = new Migration(100);

    // 1. Rules Table
    if (!$DB->tableExists('glpi_plugin_automator_rules')) {
        $query = "CREATE TABLE `glpi_plugin_automator_rules` (
         `id` int(11) NOT NULL AUTO_INCREMENT,
         `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
         `is_active` tinyint(1) NOT NULL DEFAULT '1',
         `itemtype` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
         `date_mod` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
         `date_creation` timestamp DEFAULT CURRENT_TIMESTAMP,
         PRIMARY KEY (`id`),
         KEY `is_active` (`is_active`),
         KEY `itemtype` (`itemtype`)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        $migration->addPostQuery($query);
    }

    // 2. Actions Table
    if (!$DB->tableExists('glpi_plugin_automator_actions')) {
        $query = "CREATE TABLE `glpi_plugin_automator_actions` (
         `id` int(11) NOT NULL AUTO_INCREMENT,
         `plugin_automator_rules_id` int(11) NOT NULL,
         `action_type` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
         `configuration` longtext COLLATE utf8mb4_unicode_ci COMMENT 'JSON Payload',
         `execution_order` int(11) NOT NULL DEFAULT '0',
         `date_mod` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
         `date_creation` timestamp DEFAULT CURRENT_TIMESTAMP,
         PRIMARY KEY (`id`),
         KEY `plugin_automator_rules_id` (`plugin_automator_rules_id`)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        $migration->addPostQuery($query);
    }

    // 3. Profile Rights
    if (!$DB->fieldExists('glpi_profiles', 'plugin_automator')) {
        $migration->addField('glpi_profiles', 'plugin_automator', 'char(10) DEFAULT NULL');
    }

    $migration->executeMigration();

    // Deploy Frontend Assets
    plugin_automator_install_assets();

    return true;
}

/**
 * Copy frontend assets from dist to GLPI_ROOT/public/automator
 */
function plugin_automator_install_assets()
{
    $source = __DIR__ . '/dist';
    $dest = GLPI_ROOT . '/marketplace/automator/public/automator';

    if (!is_dir($source)) {
        \Toolbox::logInFile('php-errors', "Automator Plugin: dist directory not found. Run 'npm run build' first.");
        return;
    }

    if (!is_dir($dest)) {
        if (!mkdir($dest, 0755, true)) {
            \Toolbox::logInFile('php-errors', "Automator Plugin: Failed to create public/automator directory.");
            return;
        }
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($iterator as $item) {
        $fullPath = $item->getPathname();
        $relativePath = substr($fullPath, strlen($source) + 1);
        $destPath = $dest . DIRECTORY_SEPARATOR . $relativePath;

        if ($item->isDir()) {
            if (!is_dir($destPath)) {
                mkdir($destPath);
            }
        } else {
            copy($item, $destPath);
        }
    }
}

/**
 * Uninstall the plugin.
 *
 * @return bool
 */
function plugin_automator_uninstall()
{
    global $DB;

    $tables = [
        'glpi_plugin_automator_actions',
        'glpi_plugin_automator_rules'
    ];

    foreach ($tables as $table) {
        if ($DB->tableExists($table)) {
            $DB->dropTable($table);
        }
    }

    return true;
}
