<?php
if (false) {

    class XFCP_ThemeHouse_Warnings_Extend_XenForo_DataWriter_WarningDefinition extends XenForo_DataWriter_WarningDefinition
    {
    }
}

class ThemeHouse_Warnings_Extend_XenForo_DataWriter_WarningDefinition extends XFCP_ThemeHouse_Warnings_Extend_XenForo_DataWriter_WarningDefinition
{

    /**
     *
     * @see XenForo_DataWriter_WarningDefinition::_getFields()
     */
    protected function _getFields()
    {
        $fields = parent::_getFields();

        $fields['xf_warning_definition']['warning_group_id'] = array(
            'type' => self::TYPE_UINT,
            'default' => 0
        );
        $fields['xf_warning_definition']['display_order'] = array(
            'type' => self::TYPE_UINT_FORCED,
            'default' => 0
        );
        $fields['xf_warning_definition']['materialized_order'] = array(
            'type' => self::TYPE_UINT_FORCED,
            'default' => 0
        );

        return $fields;
    }

    /**
     *
     * @see XenForo_DataWriter_WarningDefinition::_preSave()
     */
    protected function _preSave()
    {
        if (isset($GLOBALS['XenForo_ControllerAdmin_Warning'])) {
            /* @var $controller XenForo_ControllerAdmin_Warning */
            $controller = $GLOBALS['XenForo_ControllerAdmin_Warning'];

            $input = $controller->getInput()->filter(
                array(
                    'display_order' => XenForo_Input::UINT,
                    'warning_group_id' => XenForo_Input::UINT
                ));

            $this->bulkSet($input);
        }

        parent::_preSave();
    }

    /**
     *
     * @see XenForo_DataWriter_WarningDefinition::_postSave()
     */
    protected function _postSave()
    {
        if ($this->isChanged('display_order') || $this->isChanged('warning_group_id') ||
             $this->isChanged('points_default')) {
            $this->_getWarningModel()->rebuildWarningMaterializedOrder();
        }

        if (isset($GLOBALS['XenForo_ControllerAdmin_Warning'])) {
            /* @var $controller XenForo_ControllerAdmin_Warning */
            $controller = $GLOBALS['XenForo_ControllerAdmin_Warning'];

            $id = $this->get('warning_definition_id');

            $phraseText = $controller->getInput()->filterSingle('replyText', XenForo_Input::STRING);

            $phraseName = $this->_getReplyTextPhraseName($id);

            $this->_insertOrUpdateMasterPhrase($phraseName, $phraseText);
        }


        parent::_postSave();
    }

    /**
     * Gets the name of the reply text phrase for this warning.
     *
     * @param string $id
     *
     * @return string
     */
    protected function _getReplyTextPhraseName($id)
    {
        return $this->_getWarningModel()->getWarningDefinitionReplyTextPhraseName($id);
    }
}