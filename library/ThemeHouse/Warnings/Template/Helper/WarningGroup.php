<?php

class ThemeHouse_Warnings_Template_Helper_WarningGroup extends XenForo_Template_Helper_Core
{

    public static function helperWarningGroup($warningGroupId)
    {
        return self::_getPhraseText('warning_group_' . $warningGroupId);
    }
}