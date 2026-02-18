<?php

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access directly to this file");
}

class PluginAutomatorProfile extends Profile
{
    /**
     * @param CommonGLPI $item
     * @param int        $withtemplate
     *
     * @return string
     */
    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        if ($item instanceof Profile) {
            return __('Automator', 'automator');
        }
        return '';
    }

    /**
     * @param CommonGLPI $item
     * @param int        $tabnum
     * @param int        $withtemplate
     *
     * @return bool
     */
    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {
        if ($item instanceof Profile) {
            $profile_obj = new self();
            $profile_obj->showForm($item->getID());
        }
        return true;
    }

    /**
     * Show the profile form
     *
     * @param int   $ID
     * @param array $options
     *
     * @return bool
     */
    public function showForm($ID, array $options = [])
    {
        global $DB;

        if (!Session::haveRight('profile', READ)) {
            return false;
        }

        echo "<div class='spaced'>";

        $profile = new Profile();
        if (!$profile->getFromDB($ID)) {
            return false;
        }

        $canedit = Session::haveRightsOr('profile', [CREATE, UPDATE, PURGE]);

        if ($canedit) {
            echo "<form method='post' action='" . $profile->getFormURL() . "'>";
        }

        // Define rights array
        $rights = [
            [
                'itemtype' => 'PluginAutomatorRule',
                'label'    => __('Automator Rules', 'automator'),
                'field'    => 'plugin_automator',
            ],
        ];

        $matrix_options = [
            'title' => __('Automator', 'automator'),
            'canedit' => $canedit,
        ];

        // Display rights matrix
        $profile->displayRightsChoiceMatrix($rights, $matrix_options);

        if ($canedit) {
            echo "<div class='center'>";
            echo Html::hidden('id', ['value' => $ID]);
            echo Html::submit(_sx('button', 'Save'), ['name' => 'update']);
            echo "</div>\n";
            Html::closeForm();
        }

        echo "</div>";

        return true;
    }
}
