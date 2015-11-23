<?php

class ThemeHouse_Warnings_Install_Controller extends ThemeHouse_Install
{

    protected $_resourceManagerUrl = 'http://xenforo.com/community/resources/warnings.2300/';

    protected function _preInstall()
    {
        $addOn = $this->getModelFromCache('XenForo_Model_AddOn')->getAddOnById('ThemeHouse_Warnings');

        if ($addOn && $addOn['version_id'] <= '1378250672') {
            throw new XenForo_Exception('Unable to upgrade from this version. Upgrade to 1.0.3 first.');
        }

        if (XenForo_Application::$versionId >= 1030000) {
            $this->_db->query(
                '
                UPDATE xf_warning_action
    			SET action = \'groups\'
    			WHERE action = \'groups_length\'
            ');

            if ($this->_isTableExists('xf_warning_user_group')) {
                $this->_db->query(
                    '
                        INSERT INTO xf_user_change_temp
                        (user_id, action_type, action_modifier, new_value, old_value, create_date, expiry_date)
                        SELECT user_id, \'groups\', CONCAT(\'warning_action_\', warning_action_id),
                        extra_user_group_ids, \'N;\', warning_action_date, end_date
                        FROM xf_warning_user_group
                    ');
                $this->_db->query(
                    '
                        DELETE FROM xf_user_group_change
                        WHERE change_key IN (
                            SELECT CONCAT(\'warningUserGroup\', warning_user_group_id)
                            FROM xf_warning_user_group
                        )
                    ');
                $this->_db->query('
                        DROP TABLE xf_warning_user_group
                    ');
            }
        }

        if (XenForo_Application::$versionId != XenForo_Application::getSimpleCacheData('th_warnings_xfVersionId')) {
            XenForo_Application::setSimpleCacheData('th_warnings_xfVersionId', XenForo_Application::$versionId);
        }
    }

    protected function _getTables()
    {
        $tables = array(
            'xf_warning_group' => array(
                'warning_group_id' => 'int(10) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY',
                'display_order' => 'int(10) NOT NULL DEFAULT 0'
            )
        );

        if (XenForo_Application::$versionId < 1030000) {
            $tables = array_merge($tables,
                array(
                    'xf_warning_user_group' => array(
                        'warning_user_group_id' => 'int(10) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY',
                        'warning_action_id' => 'int(10) unsigned NOT NULL',
                        'user_id' => 'int(10) unsigned NOT NULL',
                        'warning_action_date' => 'int(10) unsigned NOT NULL DEFAULT 0',
                        'end_date' => 'int(10) unsigned NOT NULL DEFAULT 0',
                        'extra_user_group_ids' => 'varbinary(255) NOT NULL'
                    )
                ));
        }

        return $tables;
    }

    protected function _getTableChanges()
    {
        return array(
            'xf_warning_definition' => array(
                'warning_group_id' => 'int(10) unsigned NOT NULL DEFAULT 0',
                'display_order' => 'int(10) NOT NULL DEFAULT 0',
                'materialized_order' => 'int(10) NOT NULL DEFAULT 0'
            ),
            'xf_warning_action' => array(
                'warnings' => 'varchar(255) NOT NULL DEFAULT \'\'',
                'warning_groups' => 'varchar(255) NOT NULL DEFAULT \'\''
            )
        );
    }

    protected function _getKeys()
    {
        if (XenForo_Application::$versionId < 1030000) {
            return array(
                'xf_warning_user_group' => array(
                    'warning_action_date' => array(
                        'warning_action_date'
                    ),
                    'end_date' => array(
                        'end_date'
                    )
                )
            );
        }
    }

    protected function _getEnumValues()
    {
        if (XenForo_Application::$versionId < 1030000) {
            return array(
                'xf_warning_action' => array(
                    'action' => array(
                        'add' => array(
                            'groups_length'
                        )
                    )
                ),
                'xf_warning_action_trigger' => array(
                    'action' => array(
                        'add' => array(
                            'groups_length'
                        )
                    )
                )
            );
        }

        return array();
    }

    protected function _postInstall()
    {
        $phrases = $this->_getWarningPhrases();

        /* @var $phraseModel XenForo_Model_Phrase */
        $phraseModel = $this->getModelFromCache('XenForo_Model_Phrase');

        foreach ($phrases as $title) {
            $phrase = $phraseModel->getPhraseInLanguageByTitle($title, 0);

            if (!$phrase) {
                $dw = XenForo_DataWriter::create('XenForo_DataWriter_Phrase', XenForo_DataWriter::ERROR_SILENT);
                $dw->set('language_id', 0);
                $dw->set('title', $title);
                $dw->save();
            }
        }
    }

    protected function _postUninstall()
    {
        $phrases = $this->_getWarningPhrases();

        /* @var $phraseModel XenForo_Model_Phrase */
        $phraseModel = $this->getModelFromCache('XenForo_Model_Phrase');

        foreach ($phrases as $title) {
            $phrase = $phraseModel->getPhraseInLanguageByTitle($title, 0);

            if ($phrase) {
                $dw = XenForo_DataWriter::create('XenForo_DataWriter_Phrase', XenForo_DataWriter::ERROR_SILENT);
                $dw->setExistingData($phrase);
                $dw->delete();
            }
        }
    }

    protected function _getWarningPhrases()
    {
        /* @var $warningModel XenForo_Model_Warning */
        $warningModel = $this->getModelFromCache('XenForo_Model_Warning');

        $phrases = array(
            $warningModel->getWarningDefinitionConversationTitlePhraseName(0),
            $warningModel->getWarningDefinitionConversationTextPhraseName(0)
        );

        if (method_exists($warningModel, 'getWarningDefinitionReplyTextPhraseName')) {
            $phrases[] = $warningModel->getWarningDefinitionReplyTextPhraseName(0);
        } else {
            $phrases[] = 'warning_definition_0_reply_text';
        }

        return $phrases;
    }
}