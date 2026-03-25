<?php

namespace Glpi\Plugin\Automator\Action;

use CommonDBTM;

interface ActionInterface
{
    /**
     * Execute the action on the item
     *
     * @param CommonDBTM $item The item triggering the rule (usually newly added)
     * @param array $config Configuration for this action
     * @return void
     */
    public function execute(CommonDBTM $item, array $config): void;
}
