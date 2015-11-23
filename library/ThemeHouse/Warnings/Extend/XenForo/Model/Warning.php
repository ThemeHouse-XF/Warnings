<?php
if (false) {

    class XFCP_ThemeHouse_Warnings_Extend_XenForo_Model_Warning extends XenForo_Model_Warning
    {
    }
}

class ThemeHouse_Warnings_Extend_XenForo_Model_Warning extends XFCP_ThemeHouse_Warnings_Extend_XenForo_Model_Warning
{

    protected static $_warningActionsCache = null;

    protected static $_userWarningActionTriggersCache = array();

    /**
     *
     * @see XenForo_Model_Warning::getWarningDefinitions()
     */
    public function getWarningDefinitions()
    {
        $warningDefinitions = parent::getWarningDefinitions();

        uasort($warningDefinitions, array(
            $this,
            '_sortByMaterializedOrder'
        ));

        return $warningDefinitions;
    }

    public function getWarningDefinitionReplyTextPhraseName($id)
    {
        return 'warning_definition_' . $id . '_reply_text';
    }

    protected function _sortByMaterializedOrder($x, $y)
    {
        if ($x['materialized_order'] == $y['materialized_order']) {
            return 0;
        } elseif ($x['materialized_order'] < $y['materialized_order']) {
            return -1;
        } else {
            return 1;
        }
    }

    /**
     * Fetches warning definitions in warning groups
     *
     * @param array $warningDefinitions
     *
     * @return array
     */
    public function arrangeWarningDefinitionsIntoGroups(array $warningDefinitions)
    {
        $warningGroups = array();
        foreach ($warningDefinitions as $warningDefinitionId => $warningDefinition) {
            $warningGroups[$warningDefinition['warning_group_id']][$warningDefinitionId] = $this->prepareWarningDefinition(
                $warningDefinition);
        }

        return $warningGroups;
    }

    public function getWarningDefinitionsInCanonicalOrder()
    {
        return $this->fetchAllKeyed(
            '
			SELECT warning_definition.*
			FROM xf_warning_definition AS warning_definition
            LEFT JOIN xf_warning_group AS warning_group ON (warning_definition.warning_group_id = warning_group.warning_group_id)
			ORDER BY warning_group.display_order ASC, warning_definition.display_order ASC, warning_definition.points_default ASC
		', 'warning_definition_id');
    }

    /**
     *
     * @see XenForo_Model_Warning::getWarningActions()
     */
    public function getWarningActions()
    {
        if (is_null(self::$_warningActionsCache)) {
            self::$_warningActionsCache = parent::getWarningActions();
        }

        return self::$_warningActionsCache;
    }

    /**
     *
     * @see XenForo_Model_Warning::_userWarningPointsIncreased()
     */
    protected function _userWarningPointsIncreased($userId, $newPoints, $oldPoints)
    {
        parent::_userWarningPointsIncreased($userId, $newPoints, $oldPoints);

        $actions = $this->getWarningActions();
        if (!$actions) {
            return;
        }

        $db = $this->_getDb();
        XenForo_Db::beginTransaction($db);

        foreach ($actions as $action) {
            if ($action['points'] <= $oldPoints && ($action['warnings'] || $action['warning_groups'])) {
                $this->triggerWarningAction($userId, $action);
            }
        }

        XenForo_Db::commit($db);
    }

    /**
     *
     * @see XenForo_Model_Warning::_userWarningPointsDecreased()
     */
    protected function _userWarningPointsDecreased($userId, $newPoints, $oldPoints)
    {
        parent::_userWarningPointsDecreased($userId, $newPoints, $oldPoints);

        $triggers = $this->getUserWarningActionTriggers($userId);
        if (!$triggers) {
            return;
        }

        $db = $this->_getDb();
        XenForo_Db::beginTransaction($db);

        foreach ($triggers as $trigger) {
            if ($trigger['trigger_points'] <= $newPoints && ($trigger['warnings'] || $trigger['warning_groups'])) {
                // points may have fallen below trigger
                $this->removeWarningActionTrigger($userId, $trigger);
            }
        }

        XenForo_Db::commit($db);
    }

    /**
     *
     * @see XenForo_Model_Warning::getUserWarningActionTriggers()
     */
    public function getUserWarningActionTriggers($userId)
    {
        if (!isset(self::$_userWarningActionTriggersCache[$userId])) {
            self::$_userWarningActionTriggersCache[$userId] = $this->fetchAllKeyed(
                '
        			SELECT action_trigger.*, action.warnings, action.warning_groups
        			FROM xf_warning_action_trigger AS action_trigger
                    LEFT JOIN xf_warning_action AS action
                        ON (action_trigger.warning_action_id = action.warning_action_id)
        			WHERE action_trigger.user_id = ?
        			ORDER BY action_trigger.trigger_points
    		    ', 'action_trigger_id', $userId);
        }

        return self::$_userWarningActionTriggersCache[$userId];
    }

    /**
     *
     * @see XenForo_Model_Warning::triggerWarningAction()
     */
    public function triggerWarningAction($userId, array $action)
    {
        if ($action['warnings'] || $action['warning_groups']) {
            $action['warnings'] = array_filter(explode(',', $action['warnings']));
            $action['warning_groups'] = array_filter(explode(',', $action['warning_groups']));

            $db = $this->_getDb();

            if (isset($GLOBALS['XenForo_DataWriter_Warning'])) {
                /* @var $dw = XenForo_DataWriter_Warning */
                $dw = $GLOBALS['XenForo_DataWriter_Warning'];

                $warningDefinitionId = $dw->get('warning_definition_id');

                if (!in_array($warningDefinitionId, $action['warnings'])) {
                    $warningGroupId = $this->getWarningGroupIdByWarningDefinitionId($warningDefinitionId);
                    if (!$warningGroupId || !in_array($warningGroupId, $action['warning_groups'])) {
                        return 0;
                    }
                }

                $whereClauses = array();
                if ($action['warnings']) {
                    $whereClauses[] = 'warning.warning_definition_id IN (' . $db->quote($action['warnings']) . ')';
                }
                if ($action['warning_groups']) {
                    $whereClauses[] = 'warning_definition.warning_group_id IN (' . $db->quote($action['warning_groups']) . ')';
                }

                $oldPoints = $db->fetchOne(
                    '
                        SELECT SUM(warning.points)
                        FROM xf_warning AS warning
                        LEFT JOIN xf_warning_definition AS warning_definition ON (warning.warning_definition_id = warning_definition.warning_definition_id)
                        WHERE warning.user_id = ? AND warning.is_expired = 0
                            ' .
                         ($whereClauses ? 'AND (' . implode(' OR ', $whereClauses) . ')' : '') . '
                            AND warning.warning_date < ' . XenForo_Application::$time . '
                    ', $userId);

                $newPoints = $oldPoints + $dw->get('points');

                if ($action['points'] <= $oldPoints) {
                    return 0; // already triggered - not necessarily true when an action is added though, but probably ok
                } elseif ($action['points'] > $newPoints) {
                    return 0; // no trigger yet
                }
            }
        }

        if (XenForo_Application::$versionId < 1030000) {
            $minUnbanDate = 0;
            $insertTrigger = true;

            $db = $this->_getDb();
            XenForo_Db::beginTransaction($db);

            switch ($action['action']) {
                case 'groups_length':
                    if (!$action['extra_user_group_ids']) {
                        $insertTrigger = false;
                        break;
                    }

                    if ($action['ban_length_type'] == 'permanent') {
                        /* @var $dw XenForo_DataWriter_User */
                        $dw = XenForo_DataWriter::create('XenForo_DataWriter_User');
                        $dw->setExistingData($userId);
                        $secondaryGroupIds = $dw->get('secondary_group_ids');
                        if ($secondaryGroupIds) {
                            $secondaryGroupIds = explode(',', $secondaryGroupIds);
                        } else {
                            $secondaryGroupIds = array();
                        }
                        $extraUserGroupIds = explode(',', $action['extra_user_group_ids']);
                        $secondaryGroupIds = array_merge($secondaryGroupIds, $extraUserGroupIds);
                        $dw->setSecondaryGroups($secondaryGroupIds);
                        $dw->save();
                        break;
                    }

                    $endDate = strtotime("+$action[ban_length] $action[ban_length_type]");

                    $db->insert('xf_warning_user_group',
                        array(
                            'warning_action_id' => $action['warning_action_id'],
                            'user_id' => $userId,
                            'warning_action_date' => XenForo_Application::$time,
                            'end_date' => $endDate,
                            'extra_user_group_ids' => $action['extra_user_group_ids']
                        ));
                    $warningUserGroupId = $this->_getDb()->lastInsertId();

                    if ($warningUserGroupId) {
                        $this->getModelFromCache('XenForo_Model_User')->addUserGroupChange($userId,
                            'warningUserGroup' . $warningUserGroupId, $action['extra_user_group_ids']);
                    }
                    break;
            }
        }

        // Note: this return value cannot be relied upon in XenForo 1.2.1 or below
        $actionTriggerId = parent::triggerWarningAction($userId, $action);

        if (XenForo_Application::$versionId < 1030000) {
            XenForo_Db::commit($db);
        }

        return $actionTriggerId;
    }

    /**
     *
     * @see XenForo_Model_Warning::removeWarningActionTrigger()
     */
    public function removeWarningActionTrigger($userId, array $trigger)
    {
        if ($trigger['warnings'] || $trigger['warning_groups']) {
            $trigger['warnings'] = array_filter(explode(',', $trigger['warnings']));
            $trigger['warning_groups'] = array_filter(explode(',', $trigger['warning_groups']));

            $db = $this->_getDb();

            if (isset($GLOBALS['XenForo_DataWriter_Warning'])) {
                /* @var $dw = XenForo_DataWriter_Warning */
                $dw = $GLOBALS['XenForo_DataWriter_Warning'];

                $warningDefinitionId = $dw->get('warning_definition_id');

                if (!in_array($warningDefinitionId, $trigger['warnings'])) {
                    $warningGroupId = $this->getWarningGroupIdByWarningDefinitionId($warningDefinitionId);
                    if (!$warningGroupId || !in_array($warningGroupId, $trigger['warning_groups'])) {
                        return;
                    }
                }

                $whereClauses = array();
                if ($trigger['warnings']) {
                    $whereClauses[] = 'warning.warning_definition_id IN (' . $db->quote($trigger['warnings']) . ')';
                }
                if ($trigger['warning_groups']) {
                    $whereClauses[] = 'warning_definition.warning_group_id IN (' . $db->quote(
                        $trigger['warning_groups']) . ')';
                }

                $newPoints = $db->fetchOne(
                    '
                        SELECT SUM(warning.points)
                        FROM xf_warning AS warning
                        LEFT JOIN xf_warning_definition AS warning_definition ON (warning.warning_definition_id = warning_definition.warning_definition_id)
                        WHERE warning.user_id = ? AND warning.is_expired = 0
                            ' .
                         ($whereClauses ? 'AND (' . implode(' OR ', $whereClauses) . ')' : '') . '
                    ', $userId);

                $oldPoints = $newPoints + $dw->get('points');

                if ($trigger['trigger_points'] <= $newPoints) {
                    // points have not yet fallen below trigger
                    return;
                }
            }
        }

        return parent::removeWarningActionTrigger($userId, $trigger);
    }

    public function getExpiredWarningUserGroups()
    {
        return $this->fetchAllKeyed('
			SELECT *
			FROM xf_warning_user_group
			WHERE end_date < ?
		', 'warning_user_group_id', XenForo_Application::$time);
    }

    /**
     *
     * @see XenForo_Model_Warning::processExpiredWarnings()
     */
    public function processExpiredWarnings()
    {
        parent::processExpiredWarnings();

        if (XenForo_Application::$versionId < 1030000) {
            foreach ($this->getExpiredWarningUserGroups() as $warningUserGroupId => $warningUserGroup) {
                $this->getModelFromCache('XenForo_Model_User')->removeUserGroupChange($warningUserGroup['user_id'],
                    'warningUserGroup' . $warningUserGroupId);
            }

            $this->_getDb()->delete('xf_warning_user_group',
                'end_date < ' . $this->_getDb()
                    ->quote(XenForo_Application::$time));
        }
    }

    /**
     * Rebuilds the 'materialized_order' field in the warning_definiton table,
     * based on the canonical display_order data in the warning and
     * warning_group tables.
     */
    public function rebuildWarningMaterializedOrder()
    {
        $warnings = $this->getWarningDefinitionsInCanonicalOrder();

        $db = $this->_getDb();
        $ungroupedWarnings = array();
        $updates = array();
        $i = 0;

        foreach ($warnings as $warningId => $warning) {
            if ($warning['warning_group_id']) {
                if (++ $i != $warning['materialized_order']) {
                    $updates[$warningId] = 'WHEN ' . $db->quote($warningId) . ' THEN ' . $db->quote($i);
                }
            } else {
                $ungroupedWarnings[$warningId] = $warning;
            }
        }

        foreach ($ungroupedWarnings as $warningId => $warning) {
            if (++ $i != $warning['materialized_order']) {
                $updates[$warningId] = 'WHEN ' . $db->quote($warningId) . ' THEN ' . $db->quote($i);
            }
        }

        if (!empty($updates)) {
            $db->query(
                '
				UPDATE xf_warning_definition SET materialized_order = CASE warning_definition_id
				' . implode(' ', $updates) . '
				END
				WHERE warning_definition_id IN(' . $db->quote(array_keys($updates)) . ')
			');
        }
    }

    // warning groups ---------------------------------------------------------


    /**
     * Fetches a single warning group, as defined by its unique warning group ID
     *
     * @param integer $warningGroupId
     *
     * @return array
     */
    public function getWarningGroupById($warningGroupId)
    {
        if (!$warningGroupId) {
            return array();
        }

        return $this->_getDb()->fetchRow('
			SELECT *
			FROM xf_warning_group
			WHERE warning_group_id = ?
		', $warningGroupId);
    }

    public function getAllWarningGroups()
    {
        return $this->fetchAllKeyed('
			SELECT *
			FROM xf_warning_group
			ORDER BY display_order
		', 'warning_group_id');
    }

    public function getWarningGroupIdByWarningDefinitionId($warningDefinitionId)
    {
        return $this->_getDb()->fetchOne(
            '
            SELECT warning_group_id
            FROM xf_warning_definition
            WHERE warning_definition_id = ?
        ', $warningDefinitionId);
    }

    public function getWarningGroupOptions($selectedGroupId)
    {
        $warningGroups = $this->getAllWarningGroups();
        $warningGroups = $this->prepareWarningGroups($warningGroups);

        $options = array();

        foreach ($warningGroups as $warningGroupId => $warningGroup) {
            $options[$warningGroupId] = $warningGroup['title'];
        }

        return $options;
    }

    public function mergeWarningsIntoGroups(array $warnings, array $warningGroups)
    {
        $merge = array();

        foreach ($warningGroups as $warningGroupId => $warningGroup) {
            if (isset($warnings[$warningGroupId])) {
                $merge[$warningGroupId] = $warnings[$warningGroupId];
                unset($warnings[$warningGroupId]);
            } else {
                $merge[$warningGroupId] = array();
            }
        }

        if (!empty($warnings)) {
            foreach ($warnings as $warningGroupId => $_warnings) {
                $merge[$warningGroupId] = $_warnings;
            }
        }

        return $merge;
    }

    public function getWarningGroupTitlePhraseName($warningGroupId)
    {
        return 'warning_group_' . $warningGroupId;
    }

    public function prepareWarningGroups(array $warningGroups)
    {
        return array_map(array(
            $this,
            'prepareWarningGroup'
        ), $warningGroups);
    }

    public function prepareWarningGroup(array $warningGroup)
    {
        $warningGroup['title'] = new XenForo_Phrase(
            $this->getWarningGroupTitlePhraseName($warningGroup['warning_group_id']));

        return $warningGroup;
    }
}