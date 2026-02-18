<?php

include('../../../inc/includes.php');

header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $debugInfo = "POST to {$_GET['action']}\n";
    $debugInfo .= "Content-Type: " . ($_SERVER['CONTENT_TYPE'] ?? 'n/a') . "\n";
    $debugInfo .= "X-Requested-With: " . ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? 'n/a') . "\n";
    $debugInfo .= "X-Glpi-Csrf-Token (header): " . ($_SERVER['HTTP_X_GLPI_CSRF_TOKEN'] ?? 'n/a') . "\n";
    $debugInfo .= "POST keys: " . implode(', ', array_keys($_POST)) . "\n";
    $debugInfo .= "Token in POST: " . (isset($_POST['_glpi_csrf_token']) ? 'YES' : 'NO') . "\n";
    Toolbox::logInFile('automator-debug', $debugInfo . "-------------------\n");
}

$action = $_GET['action'] ?? '';

// Check Rights
if (in_array($action, ['save_rule', 'delete_rule'])) {
    Session::checkRight("plugin_automator", UPDATE);
} else {
    Session::checkRight("plugin_automator", READ);
}

switch ($action) {
    case 'get_itemtypes':
        echo json_encode(['types' => ['Computer', 'Monitor', 'Printer', 'Software', 'User', 'Group', 'Ticket', 'Contract']]);
        break;

    case 'get_fields':
        $itemtype = $_GET['itemtype'] ?? '';
        if (class_exists($itemtype)) {
            $item = new $itemtype();
            $table = $item->getTable();
            global $DB;

            // Main table fields
            $fields = $DB->listFields($table);
            $allFields = [];
            foreach ($fields as $f) {
                $allFields[] = [
                    'table' => $table,
                    'field' => $f['Field'],
                    'label' => $f['Field'] . " ($table)"
                ];
            }

            // Secondary tables (Plugin Fields)
            // Pattern: glpi_plugin_fields_<container>_<itemtype_plural>s or similar
            $tables = $DB->listTables();
            foreach ($tables as $secTable) {
                // $DB->listTables() may return an array of arrays in some GLPI versions
                if (is_array($secTable)) {
                    $secTable = reset($secTable);
                }

                // Check if this table is for this itemtype
                // Flexible check: contains the itemtype name + starts with glpi_plugin_fields_
                if (strpos($secTable, 'glpi_plugin_fields_') !== 0) continue;

                $lowerType = strtolower($itemtype);
                if (str_contains(strtolower($secTable), $lowerType)) {
                    $secFields = $DB->listFields($secTable);
                    foreach ($secFields as $sf) {
                        if (in_array($sf['Field'], ['id', 'items_id', 'itemtype', 'entities_id', 'is_recursive'])) continue;
                        $allFields[] = [
                            'table' => $secTable,
                            'field' => $sf['Field'],
                            'label' => $sf['Field'] . " ($secTable)"
                        ];
                    }
                }
            }

            echo json_encode(['fields' => $allFields]);
        } else {
            echo json_encode(['error' => 'Invalid ItemType']);
        }
        break;

    case 'get_rules':
        global $DB;
        $rules = [];
        $iterator = $DB->request(['FROM' => 'glpi_plugin_automator_rules']);
        foreach ($iterator as $rule) {
            // Fetch actions for this rule
            $actionsIter = $DB->request([
                'FROM' => 'glpi_plugin_automator_actions',
                'WHERE' => ['plugin_automator_rules_id' => $rule['id']],
                'ORDER' => 'execution_order ASC'
            ]);
            $rule['actions'] = [];
            foreach ($actionsIter as $action) {
                $action['configuration'] = json_decode($action['configuration'], true) ?? [];
                $rule['actions'][] = $action;
            }
            $rules[] = $rule;
        }
        echo json_encode(['rules' => $rules]);
        break;

    case 'save_rule':
        // Handle POST request to save rule and actions
        $input = null;
        if (isset($_POST['data'])) {
            $input = json_decode($_POST['data'], true);
        } else {
            $input = json_decode(file_get_contents('php://input'), true);
        }

        if (!$input) {
            echo json_encode(['success' => false, 'message' => 'Invalid JSON']);
            exit;
        }

        $rule = new PluginAutomatorRule();
        $ruleId = 0;

        if (isset($input['id']) && $input['id'] > 0) {
            $rule->update([
                'id' => $input['id'],
                'name' => $input['name'],
                'itemtype' => $input['itemtype'],
                'is_active' => $input['is_active']
            ]);
            $ruleId = $input['id'];
        } else {
            $ruleId = $rule->add([
                'name' => $input['name'],
                'itemtype' => $input['itemtype'],
                'is_active' => $input['is_active']
            ]);
        }

        if ($ruleId) {
            // Save Actions
            // First, delete existing actions (simple replacement strategy)
            $actionObj = new PluginAutomatorAction();
            $actionObj->deleteByCriteria(['plugin_automator_rules_id' => $ruleId]);

            foreach ($input['actions'] as $idx => $actionData) {
                $actionObj->add([
                    'plugin_automator_rules_id' => $ruleId,
                    'action_type' => $actionData['action_type'],
                    'configuration' => json_encode($actionData['configuration']),
                    'execution_order' => $idx
                ]);
            }
            echo json_encode(['success' => true, 'id' => $ruleId]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to save rule']);
        }
        break;

    case 'delete_rule':
        $id = $_POST['id'] ?? $_GET['id'] ?? 0;
        if ($id) {
            $rule = new PluginAutomatorRule();
            $rule->delete(['id' => $id]);
            echo json_encode(['success' => true]);
        }
        break;

    default:
        echo json_encode(['error' => 'Unknown action']);
}
