<?php

class ThemeHouse_Warnings_Listener_ContainerAdminParams
{

    public static function containerAdminParams(array &$params, XenForo_Dependencies_Abstract $dependencies)
    {
        XenForo_Template_Helper_Core::$helperCallbacks['warninggroup'] = array(
            'ThemeHouse_Warnings_Template_Helper_WarningGroup',
            'helperWarningGroup'
        );
    }
}