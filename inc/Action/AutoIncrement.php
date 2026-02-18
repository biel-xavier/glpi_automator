<?php

namespace Glpi\Plugin\Automator\Action;

use CommonDBTM;
use Toolbox;

class AutoIncrement implements ActionInterface
{
    public function execute(CommonDBTM $item, array $config): void
    {
        global $DB;

        $targetField = $config['field'] ?? null;
        $targetTable = $config['table'] ?? null;

        if (!$targetField || !$targetTable) {
            return;
        }

        // 2. Build Query to find Max Value
        // We use CAST or + 0 to ensure numeric sorting on VARCHAR fields
        $criteria = [
            'SELECT' => [
                new \QueryExpression("MAX(CAST($targetField AS UNSIGNED)) AS max_val")
            ],
            'FROM' => $targetTable
        ];

        // For now, let's keep it simple: global max + 1.
        // If we wanted entity-aware auto-increment for main objects:
        if ($targetTable === $item->getTable() && $item->isEntityAssign()) {
            $criteria['WHERE']['entities_id'] = $item->getEntityID();
        } elseif (str_contains($targetTable, 'glpi_plugin_fields_')) {
            // For plugin fields, we should probably filter by itemtype to avoid collisions
            // if the same table is used for multiple types (rare but possible in BOCKS)
            $criteria['WHERE']['itemtype'] = get_class($item);
        }

        $result = $DB->request($criteria)->current();
        $maxVal = $result['max_val'] ?? 0;

        $nextVal = (int)$maxVal + 1;

        // 4. Update the Target
        if ($targetTable === $item->getTable()) {
            // Main table update
            $item->update([
                'id' => $item->getID(),
                $targetField => $nextVal
            ]);
        } else {
            // Secondary table update (e.g. Plugin Fields)
            // We need to find the record linked to this item
            $iterator = $DB->request([
                'FROM' => $targetTable,
                'WHERE' => [
                    'items_id' => $item->getID(),
                    'itemtype' => get_class($item)
                ]
            ]);

            if ($row = $iterator->current()) {
                $DB->update($targetTable, [$targetField => $nextVal], ['id' => $row['id']]);
            } else {
                // If record doesn't exist, we might need to create it?
                // For Fields plugin, it usually creates records on item add, but let's be safe.
                $newRow = [
                    'items_id' => $item->getID(),
                    'itemtype' => get_class($item),
                    $targetField => $nextVal
                ];
                // Fields plugin tables often have entities_id too
                if ($item->isEntityAssign()) {
                    $newRow['entities_id'] = $item->getEntityID();
                }
                $DB->insert($targetTable, $newRow);
            }
        }

        Toolbox::logInFile('automator', "AutoIncrement: Updated table $targetTable field $targetField to $nextVal for item " . $item->getID());
    }
}
