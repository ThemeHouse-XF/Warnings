<?php
if (false) {

    class XFCP_ThemeHouse_Warnings_Extend_XenForo_DataWriter_WarningAction extends XenForo_DataWriter_WarningAction
    {
    }
}

class ThemeHouse_Warnings_Extend_XenForo_DataWriter_WarningAction extends XFCP_ThemeHouse_Warnings_Extend_XenForo_DataWriter_WarningAction
{

    /**
     *
     * @see XenForo_DataWriter_WarningAction::_getFields()
     */
    protected function _getFields()
    {
        $fields = parent::_getFields();

        if (XenForo_Application::$versionId < 1030000) {
            $fields['xf_warning_action']['action']['allowedValues'][] = 'groups_length';
        }

        $fields['xf_warning_action']['warnings'] = array(
            'type' => self::TYPE_UNKNOWN,
            'default' => '',
            'verification' => array(
                '$this',
                '_verifyWarnings'
            )
        );
        $fields['xf_warning_action']['warning_groups'] = array(
            'type' => self::TYPE_UNKNOWN,
            'default' => '',
            'verification' => array(
                '$this',
                '_verifyWarningGroups'
            )
        );

        return $fields;
    }

    /**
     * Pre-save handling.
     */
    protected function _preSave()
    {
        parent::_preSave();

        if (isset($GLOBALS['XenForo_ControllerAdmin_Warning'])) {
            /* @var $controller XenForo_ControllerAdmin_Warning */
            $controller = $GLOBALS['XenForo_ControllerAdmin_Warning'];

            $input = $controller->getInput()->filter(array(
                'warnings' => XenForo_Input::ARRAY_SIMPLE,
                'warning_groups' => XenForo_Input::ARRAY_SIMPLE,
            ));

            $this->bulkSet($input);

            if (XenForo_Application::$versionId < 1030000) {
                $input = $controller->getInput()->filter(array(
                    'action' => XenForo_Input::STRING,
                    'groups_length_type_base' => XenForo_Input::STRING,
                    'groups_length_type' => XenForo_Input::STRING,
                    'groups_length' => XenForo_Input::UINT,
                    'extra_user_group_ids_length' => XenForo_Input::ARRAY_SIMPLE,
                ));

                if ($input['action'] == 'groups_length') {
                    if ($input['groups_length_type_base'] == 'permanent') {
                        $this->set('ban_length_type', 'permanent');
                    } else {
                        $this->set('ban_length_type', $input['groups_length_type']);
                    }
                    $this->set('ban_length', $input['groups_length']);
                    $this->set('extra_user_group_ids', $input['extra_user_group_ids_length']);
                }
            }
        }
    }

    /**
     * Verifies the warnings.
     *
     * @param array|string $warnings Array or comma-delimited list
     *
     * @return boolean
     */
    protected function _verifyWarnings(&$warnings, XenForo_DataWriter $dw, $fieldName = false)
    {
        if (!is_array($warnings)) {
            if ($warnings === '') {
                return true;
            }
            $warnings = preg_split('#,\s*#', $warnings);
        }

        if (!$warnings) {
            $warnings = '';
            return true;
        }

        $warnings = array_map('intval', $warnings);
        $warnings = array_unique($warnings);
        sort($warnings, SORT_NUMERIC);
        $warnings = implode(',', $warnings);

        return true;
    }

    /**
     * Verifies the warning groups.
     *
     * @param array|string $warningGroups Array or comma-delimited list
     *
     * @return boolean
     */
    protected function _verifyWarningGroups(&$warningGroups, XenForo_DataWriter $dw, $fieldName = false)
    {
        if (!is_array($warningGroups)) {
            if ($warningGroups === '') {
                return true;
            }
            $warningGroups = preg_split('#,\s*#', $warningGroups);
        }

        if (!$warningGroups) {
            $warningGroups = '';
            return true;
        }

        $warningGroups = array_map('intval', $warningGroups);
        $warningGroups = array_unique($warningGroups);
        sort($warningGroups, SORT_NUMERIC);
        $warningGroups = implode(',', $warningGroups);

        return true;
    }
}