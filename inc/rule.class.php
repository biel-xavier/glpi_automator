<?php

use Glpi\Plugin\Automator\Action\ActionInterface;
use Glpi\Plugin\Automator\Action\AutoIncrement;

class PluginAutomatorRule extends CommonDBTM
{
    static $rightname = 'config';

    static function getTypeName($nb = 0)
    {
        return __('Automation Rules', 'automator');
    }

    /**
     * Hook to be called when an item is added
     * registry: $PLUGIN_HOOKS['item_add']['automator'] = ['PluginAutomatorRule' => 'item_add'];
     *
     * @param CommonDBTM $item
     */
    static function item_add(CommonDBTM $item)
    {
        $itemtype = get_class($item);
        Toolbox::logInFile('automator', "item_add hook triggered for $itemtype ID " . $item->getID() . "\n");

        // 1. Find active rules for this ItemType
        $rule = new self();
        $rules = $rule->find([
            'itemtype' => $itemtype,
            'is_active' => 1
        ]);

        Toolbox::logInFile('automator', "Found " . count($rules) . " rules for $itemtype\n");

        if (empty($rules)) {
            return;
        }

        // 2. Iterate and Execute
        foreach ($rules as $ruleData) {
            self::executeRule($ruleData['id'], $item);
        }
    }

    /**
     * Execute a specific rule
     *
     * @param int $ruleId
     * @param CommonDBTM $item
     */
    static function executeRule($ruleId, CommonDBTM $item)
    {
        global $DB;

        // Fetch Actions ordered by execution_order
        $iterator = $DB->request([
            'FROM' => 'glpi_plugin_automator_actions',
            'WHERE' => ['plugin_automator_rules_id' => $ruleId],
            'ORDER' => 'execution_order ASC'
        ]);

        foreach ($iterator as $actionData) {
            $actionType = $actionData['action_type'];
            $config = json_decode($actionData['configuration'], true) ?? [];

            // Instantiate and Execute
            $actionInstance = self::getActionInstance($actionType);
            if ($actionInstance) {
                $actionInstance->execute($item, $config);
            }
        }
    }

    /**
     * Factory for Action Instances
     *
     * @param string $type
     * @return ActionInterface|null
     */
    static function getActionInstance($type)
    {
        // Simple registry for now. Could be dynamic later.
        switch ($type) {
            case 'AUTO_INCREMENT':
                return new AutoIncrement();
            default:
                return null;
        }
    }
}
