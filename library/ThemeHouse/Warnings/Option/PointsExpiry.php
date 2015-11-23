<?php

class ThemeHouse_Warnings_Option_PointsExpiry
{

    /**
     * Verifies and prepares the points expiry option to the correct format.
     *
     * @param array $option Keys: base, default, type
     * @param XenForo_DataWriter $dw Calling DW
     * @param string $fieldName Name of field/option
     *
     * @return true
     */
    public static function verifyOption(array &$option, XenForo_DataWriter $dw, $fieldName)
    {
        if (!empty($option['base']) && $option['base'] == 'never') {
            $option = array(
                'type' => 'never'
            );
        } else {
            unset($option['base']);
        }

        return true;
    }
}