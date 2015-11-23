<?php

/**
 * Data writer for warning groups.
 */
class ThemeHouse_Warnings_DataWriter_WarningGroup extends XenForo_DataWriter
{

    /**
     * Constant for extra data that holds the value for the phrase
     * that is the title of this warning group.
     *
     * This value is required on inserts.
     *
     * @var string
     */
    const DATA_TITLE = 'phraseTitle';

    /**
     * Title of the phrase that will be created when a call to set the
     * existing data fails (when the data doesn't exist).
     *
     * @var string
     */
    protected $_existingDataErrorPhrase = 'th_requested_warning_group_not_found_warnings';

    /**
     * Gets the fields that are defined for the table.
     * See parent for explanation.
     *
     * @return array
     */
    protected function _getFields()
    {
        return array(
            'xf_warning_group' => array(
                'warning_group_id' => array(
                    'type' => self::TYPE_UINT,
                    'autoIncrement' => true
                ),
                'display_order' => array(
                    'type' => self::TYPE_UINT_FORCED,
                    'default' => 0
                )
            )
        );
    }

    /**
     * Gets the actual existing data out of data that was passed in.
     * See parent for explanation.
     *
     * @param mixed
     *
     * @return array false
     */
    protected function _getExistingData($data)
    {
        if (!$id = $this->_getExistingPrimaryKey($data, 'warning_group_id')) {
            return false;
        }

        return array(
            'xf_warning_group' => $this->_getWarningModel()->getWarningGroupById($id)
        );
    }

    /**
     * Gets SQL condition to update the existing record.
     *
     * @return string
     */
    protected function _getUpdateCondition($tableName)
    {
        return 'warning_group_id = ' . $this->_db->quote($this->getExisting('warning_group_id'));
    }

    protected function _preSave()
    {
        $titlePhrase = $this->getExtraData(self::DATA_TITLE);
        if ($titlePhrase !== null && strlen($titlePhrase) == 0) {
            $this->error(new XenForo_Phrase('please_enter_valid_title'), 'title');
        }
    }

    protected function _postSave()
    {
        $titlePhrase = $this->getExtraData(self::DATA_TITLE);
        if ($titlePhrase !== null) {
            $this->_insertOrUpdateMasterPhrase($this->_getTitlePhraseName($this->get('warning_group_id')), $titlePhrase);
        }

        if ($this->isChanged('display_order')) {
            $this->_getWarningModel()->rebuildWarningMaterializedOrder();
        }
    }

    protected function _postDelete()
    {
        $warningGroupId = $this->get('warning_group_id');

        $this->_deleteMasterPhrase($this->_getTitlePhraseName($warningGroupId));

        $this->_db->update('xf_warning_definition', array(
            'warning_group_id' => 0
        ), 'warning_group_id = ' . $this->_db->quote($warningGroupId));

        $this->_getWarningModel()->rebuildWarningMaterializedOrder();
    }

    /**
     * Gets the name of the title phrase for this warning.
     *
     * @param integer $warningId
     *
     * @return string
     */
    protected function _getTitlePhraseName($warningGroupId)
    {
        return $this->_getWarningModel()->getWarningGroupTitlePhraseName($warningGroupId);
    }

    /**
     *
     * @return XenForo_Model_Warning
     */
    protected function _getWarningModel()
    {
        return $this->getModelFromCache('XenForo_Model_Warning');
    }
}