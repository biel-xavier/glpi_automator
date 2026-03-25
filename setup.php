<?php

define('PLUGIN_AUTOMATOR_VERSION', '1.0.0');

/**
 * Init the hooks of the plugin.
 *
 * @return void
 */
function plugin_init_automator()
{
    global $PLUGIN_HOOKS;

    // Register autoloader for namespaced classes
    spl_autoload_register(function ($class) {
        $prefix = 'Glpi\\Plugin\\Automator\\';
        $base_dir = __DIR__ . '/inc/';

        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) {
            return;
        }

        $relative_class = substr($class, $len);
        $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

        if (file_exists($file)) {
            require_once $file;
        }
    });

    $PLUGIN_HOOKS['csrf_compliant']['automator'] = true;

    \Plugin::registerClass('PluginAutomatorProfile', ['addtabon' => ['Profile']]);

    $allTypes = ['Computer', 'Monitor', 'Printer', 'Software', 'User', 'Group', 'Ticket', 'Contract'];
    $PLUGIN_HOOKS['item_add']['automator'] = [];
    foreach ($allTypes as $type) {
        $PLUGIN_HOOKS['item_add']['automator'][$type] = 'plugin_automator_item_add';
    }

    // Add menu
    if (Session::haveRight('plugin_automator', READ)) {
        $PLUGIN_HOOKS['config_page']['automator'] = 'front/rule.php';
        $PLUGIN_HOOKS['menu_toadd']['automator'] = ['plugins' => 'PluginAutomatorRule'];
    }
}

/**
 * Global item_add hook for Automator
 * @param CommonDBTM $item
 */
function plugin_automator_item_add(CommonDBTM $item)
{
    PluginAutomatorRule::item_add($item);
}

/**
 * Get the name and the version of the plugin.
 *
 * @return array
 */
function plugin_version_automator()
{
    return [
        'name'           => 'Automator',
        'version'        => PLUGIN_AUTOMATOR_VERSION,
        'author'         => 'Gabriel Xavier',
        'license'        => 'GPLv2+',
        'homepage'       => 'https://github.com/gabrielxavierext/automator',
        'requirements'   => [
            'glpi' => [
                'min' => '11.0',
                'max' => '11.99',
            ]
        ]
    ];
}

/**
 * Check if the plugin differs from the version in the DB.
 *
 * @return bool
 */
function plugin_automator_check_prerequisites()
{
    if (version_compare(GLPI_VERSION, '10.0', 'lt')) {
        echo "This plugin requires GLPI >= 10.0";
        return false;
    }
    return true;
}

/**
 * Check if the plugin configuration is OK.
 *
 * @param bool $verbose
 * @return bool
 */
function plugin_automator_check_config($verbose = false)
{
    return true;
}

// Optional: Add options to the config page
// function plugin_automator_options()
