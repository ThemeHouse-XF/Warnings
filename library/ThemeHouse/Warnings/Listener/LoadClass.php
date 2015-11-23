<?php

class ThemeHouse_Warnings_Listener_LoadClass extends ThemeHouse_Listener_LoadClass
{

    protected function _getExtendedClasses()
    {
        return array(
            'ThemeHouse_Warnings' => array(
                'controller' => array(
                    'XenForo_ControllerAdmin_Warning',
                    'XenForo_ControllerPublic_Warning',
                    'XenForo_ControllerPublic_Member'
                ),
                'datawriter' => array(
                    'XenForo_DataWriter_Warning',
                    'XenForo_DataWriter_WarningDefinition',
                    'XenForo_DataWriter_WarningAction'
                ),
                'model' => array(
                    'XenForo_Model_Warning'
                ),
                'view' => array(
                    'XenForo_ViewPublic_Member_WarnFill'
                )
            )
        );
    }

    public static function loadClassController($class, array &$extend)
    {
        $extend = self::createAndRun('ThemeHouse_Warnings_Listener_LoadClass', $class, $extend, 'controller');
    }

    public static function loadClassDataWriter($class, array &$extend)
    {
        $extend = self::createAndRun('ThemeHouse_Warnings_Listener_LoadClass', $class, $extend, 'datawriter');
    }

    public static function loadClassModel($class, array &$extend)
    {
        $extend = self::createAndRun('ThemeHouse_Warnings_Listener_LoadClass', $class, $extend, 'model');
    }

    public static function loadClassView($class, array &$extend)
    {
        $extend = self::createAndRun('ThemeHouse_Warnings_Listener_LoadClass', $class, $extend, 'view');
    }
}