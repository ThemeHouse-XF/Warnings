<?php
if (false) {

    class XFCP_ThemeHouse_Warnings_Extend_XenForo_ControllerAdmin_Warning extends XenForo_ControllerAdmin_Warning
    {
    }
}

class ThemeHouse_Warnings_Extend_XenForo_ControllerAdmin_Warning extends XFCP_ThemeHouse_Warnings_Extend_XenForo_ControllerAdmin_Warning
{

    /**
     *
     * @see XenForo_ControllerAdmin_Warning::actionIndex()
     */
    public function actionIndex()
    {
        $response = parent::actionIndex();

        if ($response instanceof XenForo_ControllerResponse_View) {
            $warningModel = $this->_getWarningModel();

            $warningGroups = $warningModel->getAllWarningGroups();

            $warningDefinitions = $response->params['warnings'];

            $warningGroups = $warningModel->mergeWarningsIntoGroups(
                $warningModel->arrangeWarningDefinitionsIntoGroups($warningDefinitions), $warningGroups);

            $response->params['warningGroups'] = $warningGroups;
        }

        return $response;
    }

    /**
     *
     * @see XenForo_ControllerAdmin_Warning::_getWarningAddEditResponse()
     */
    protected function _getWarningAddEditResponse(array $warning)
    {
        $response = parent::_getWarningAddEditResponse($warning);

        if ($response instanceof XenForo_ControllerResponse_View) {
            $warningModel = $this->_getWarningModel();

            if (!isset($warning['warning_group_id'])) {
                $warning['warning_group_id'] = 0;
            }

            $warningGroupOptions = $warningModel->getWarningGroupOptions($warning['warning_group_id']);

            $response->params['warningGroupOptions'] = $warningGroupOptions;
        }

        return $response;
    }

    public function actionEdit()
    {
        $warningDefinitionId = $this->_input->filterSingle('warning_definition_id', XenForo_Input::UINT);

        $phraseModel = $this->_getPhraseModel();
        $warningModel = $this->_getWarningModel();

        if (!$warningDefinitionId) {
            $masterValues = $this->_getWarningModel()->getWarningDefinitionMasterPhraseValues(0);

            $masterValues['replyText'] = $phraseModel->getMasterPhraseValue(
                $warningModel->getWarningDefinitionReplyTextPhraseName(0));

            $viewParams = array(
                'masterConversationTitle' => $masterValues['conversationTitle'],
                'masterConversationText' => $masterValues['conversationText'],
                'masterReplyText' => $masterValues['replyText']
            );
            return $this->responseView('ThemeHouse_Warnings_ViewAdmin_Warning_Custom_Edit',
                'th_warning_custom_edit_warnings', $viewParams);
        }

        $response = parent::actionEdit();

        if ($response instanceof XenForo_ControllerResponse_View) {
            $response->params['masterReplyText'] = $phraseModel->getMasterPhraseValue(
                $warningModel->getWarningDefinitionReplyTextPhraseName($warningDefinitionId));
        }

        return $response;
    }

    public function actionCustomSave()
    {
        $dwInput = $this->_input->filter(
            array(
                'points_default' => XenForo_Input::UINT,
                'expiry_type' => XenForo_Input::STRING,
                'expiry_default' => XenForo_Input::UINT,
                'expiry_type_base' => XenForo_Input::STRING
            ));
        $phrases = $this->_input->filter(
            array(
                'conversationTitle' => XenForo_Input::STRING,
                'conversationText' => XenForo_Input::STRING,
                'replyText' => XenForo_Input::STRING
            ));

        $dw = XenForo_DataWriter::create('XenForo_DataWriter_Option');
        $dw->setExistingData('th_customPointsDefault_warnings');
        $dw->set('option_value', $dwInput['points_default']);
        $dw->save();

        $dw = XenForo_DataWriter::create('XenForo_DataWriter_Option');
        $dw->setExistingData('th_customExpiry_warnings');
        $dw->set('option_value',
            array(
                'type' => $dwInput['expiry_type'],
                'default' => $dwInput['expiry_default'],
                'base' => $dwInput['expiry_type_base']
            ));
        $dw->save();

        $phraseData = array(
            'conversationTitle' => $this->_getWarningModel()->getWarningDefinitionConversationTitlePhraseName(0),
            'conversationText' => $this->_getWarningModel()->getWarningDefinitionConversationTextPhraseName(0),
            'replyText' => $this->_getWarningModel()->getWarningDefinitionReplyTextPhraseName(0)
        );

        foreach ($phraseData as $phraseKey => $phraseName) {
            $phraseText = $phrases[$phraseKey];
            $this->_getPhraseModel()->insertOrUpdateMasterPhrase($phraseName, $phraseText);
        }

        return $this->responseRedirect(XenForo_ControllerResponse_Redirect::SUCCESS,
            XenForo_Link::buildAdminLink('warnings') . '#_warning-custom');
    }

    /**
     *
     * @see XenForo_ControllerAdmin_Warning::actionSave()
     */
    public function actionSave()
    {
        $GLOBALS['XenForo_ControllerAdmin_Warning'] = $this;

        return parent::actionSave();
    }

    /**
     *
     * @see XenForo_ControllerAdmin_Warning::_getActionAddEditResponse()
     */
    protected function _getActionAddEditResponse(array $action)
    {
        if (!isset($action['warnings'])) {
            $action['warnings'] = '';
        }

        if (!isset($action['warning_groups'])) {
            $action['warning_groups'] = '';
        }

        $response = parent::_getActionAddEditResponse($action);

        if ($response instanceof XenForo_ControllerResponse_View) {
            /* @var $warningModel XenForo_Model_Warning */
            $warningModel = $this->_getWarningModel();

            $response->params['action']['warnings'] = explode(',', $response->params['action']['warnings']);
            $response->params['action']['warning_groups'] = explode(',', $response->params['action']['warning_groups']);

            $response->params['action']['groups_length_type'] = 'permanent';
            $response->params['action']['groups_length'] = 1;
            $response->params['action']['extra_user_group_ids_length'] = array();

            if (isset($response->params['action']['action'])) {
                if ($response->params['action']['action'] == 'groups_length') {
                    $response->params['action']['groups_length_type'] = $response->params['action']['ban_length_type'];
                    $response->params['action']['groups_length'] = $response->params['action']['ban_length'];
                    $response->params['action']['extra_user_group_ids_length'] = $response->params['action']['extra_user_group_ids'];
                    $response->params['action']['ban_length_type'] = 'permanent';
                    $response->params['action']['ban_length'] = 1;
                    $response->params['action']['extra_user_group_ids'] = array();
                    foreach ($response->params['userGroupOptions'] as &$userGroupOption) {
                        $userGroupOption['selected'] = false;
                    }
                }
            }

            $response->params['userGroupOptionsLength'] = $this->getModelFromCache('XenForo_Model_UserGroup')->getUserGroupOptions(
                $response->params['action']['extra_user_group_ids_length']);

            $response->params['warnings'] = $warningModel->prepareWarningDefinitions(
                $warningModel->getWarningDefinitions());

            $response->params['warningGroups'] = $warningModel->prepareWarningGroups(
                $warningModel->getAllWarningGroups());
        }

        return $response;
    }

    /**
     *
     * @see XenForo_ControllerAdmin_Warning::actionActionSave()
     */
    public function actionActionSave()
    {
        $GLOBALS['XenForo_ControllerAdmin_Warning'] = $this;

        return parent::actionActionSave();
    }

    public function actionGroups()
    {
        $warningGroups = $this->_getWarningModel()->getAllWarningGroups();

        $viewParams = array(
            'warningGroups' => $this->_getWarningModel()->prepareWarningGroups($warningGroups)
        );

        return $this->responseView('ThemeHouse_Warnings_ViewAdmin_Warning_Group_List',
            'th_warning_group_list_warnings', $viewParams);
    }

    protected function _getWarningGroupAddEditResponse(array $warningGroup)
    {
        if (!empty($warningGroup['warning_group_id'])) {
            $phraseModel = $this->getModelFromCache('XenForo_Model_Phrase');
            $masterTitle = $phraseModel->getMasterPhraseValue(
                $this->_getWarningModel()
                    ->getWarningGroupTitlePhraseName($warningGroup['warning_group_id']));
        } else {
            $masterTitle = '';
        }

        $viewParams = array(
            'warningGroup' => $warningGroup,
            'masterTitle' => $masterTitle
        );

        return $this->responseView('ThemeHouse_Warnings_ViewAdmin_Warning_Group_Edit',
            'th_warning_group_edit_warnings', $viewParams);
    }

    public function actionAddGroup()
    {
        return $this->_getWarningGroupAddEditResponse(array(
            'display_order' => 1
        ));
    }

    public function actionEditGroup()
    {
        $warningGroupId = $this->_input->filterSingle('warning_group_id', XenForo_Input::UINT);
        $warningGroup = $this->_getWarningGroupOrError($warningGroupId);

        return $this->_getWarningGroupAddEditResponse($warningGroup);
    }

    public function actionSaveGroup()
    {
        $this->_assertPostOnly();

        $warningGroupId = $this->_input->filterSingle('warning_group_id', XenForo_Input::UINT);

        $input = $this->_input->filter(
            array(
                'title' => XenForo_Input::STRING,
                'display_order' => XenForo_Input::UINT
            ));

        $dw = XenForo_DataWriter::create('ThemeHouse_Warnings_DataWriter_WarningGroup');
        if ($warningGroupId) {
            $dw->setExistingData($warningGroupId);
        }
        $dw->set('display_order', $input['display_order']);
        $dw->setExtraData(ThemeHouse_Warnings_DataWriter_WarningGroup::DATA_TITLE, $input['title']);
        $dw->save();

        return $this->responseRedirect(XenForo_ControllerResponse_Redirect::SUCCESS,
            XenForo_Link::buildAdminLink('warnings') . $this->getLastHash('group_' . $dw->get('warning_group_id')));
    }

    public function actionDeleteGroup()
    {
        $warningGroupId = $this->_input->filterSingle('warning_group_id', XenForo_Input::UINT);

        if ($this->isConfirmedPost()) {
            $dw = XenForo_DataWriter::create('ThemeHouse_Warnings_DataWriter_WarningGroup');
            $dw->setExistingData($warningGroupId);
            $dw->delete();

            return $this->responseRedirect(XenForo_ControllerResponse_Redirect::SUCCESS,
                XenForo_Link::buildAdminLink('warnings'));
        } else {
            $viewParams = array(
                'warningGroup' => $this->_getWarningGroupOrError($warningGroupId)
            );

            return $this->responseView('ThemeHouse_Warnings_ViewAdmin_Warning_Group_Delete',
                'th_warning_group_delete_warnings', $viewParams);
        }
    }

    /**
     * Gets a valid warning group or throws an exception.
     *
     * @param integer $warningGroupId
     *
     * @return array
     */
    protected function _getWarningGroupOrError($warningGroupId)
    {
        $info = $this->_getWarningModel()->getWarningGroupById($warningGroupId);
        if (!$info) {
            throw $this->responseException(
                $this->responseError(new XenForo_Phrase('th_requested_warning_group_not_found_warnings'), 404));
        }

        return $this->_getWarningModel()->prepareWarningGroup($info);
    }

    /**
     * Returns the phrase model
     *
     * @return XenForo_Model_Phrase
     */
    protected function _getPhraseModel()
    {
        return $this->getModelFromCache('XenForo_Model_Phrase');
    }
}