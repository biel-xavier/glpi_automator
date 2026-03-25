<?php

class PluginAutomatorAction extends CommonDBTM
{
    static $rightname = 'config';

    static function getTypeName($nb = 0)
    {
        return __('Automation Actions', 'automator');
    }
}
