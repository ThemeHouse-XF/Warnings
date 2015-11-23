<?php
if (false) {

    class XFCP_ThemeHouse_Warnings_Extend_XenForo_ControllerPublic_Member extends XenForo_ControllerPublic_Member
    {
    }
}

class ThemeHouse_Warnings_Extend_XenForo_ControllerPublic_Member extends XFCP_ThemeHouse_Warnings_Extend_XenForo_ControllerPublic_Member
{

    /**
     *
     * @see XenForo_ControllerPublic_Member::actionWarn()
     */
    public function actionWarn()
    {
        $xenOptions = XenForo_Application::get('options');

        /* @var $warningModel XenForo_Model_Warning */
        $warningModel = $this->getModelFromCache('XenForo_Model_Warning');

        $fill = $this->_input->filterSingle('fill', XenForo_Input::UINT);

        if ($fill) {
            $choice = $this->_input->filterSingle('choice', XenForo_Input::UINT);
            if (!$choice) {
                $userId = $this->_input->filterSingle('user_id', XenForo_Input::UINT);
                $user = $this->getHelper('UserProfile')->getUserOrError($userId);
                $visitor = XenForo_Visitor::getInstance();

                $contentInput = $this->_input->filter(
                    array(
                        'content_type' => XenForo_Input::STRING,
                        'content_id' => XenForo_Input::UINT
                    ));

                if (!$contentInput['content_type']) {
                    $contentInput['content_type'] = 'user';
                    $contentInput['content_id'] = $user['user_id'];
                }

                /* @var $warningModel XenForo_Model_Warning */
                $warningModel = $this->getModelFromCache('XenForo_Model_Warning');

                $warningHandler = $warningModel->getWarningHandler($contentInput['content_type']);
                if (!$warningHandler) {
                    return $this->responseNoPermission();
                }

                $content = $warningHandler->getContent($contentInput['content_id']);

                if (!$content || !$warningHandler->canView($content) ||
                     !$warningHandler->canWarn($user['user_id'], $content)) {
                    return $this->responseNoPermission();
                }

                $contentTitle = $warningHandler->getContentTitle($content);
                $contentDetails = $warningHandler->getContentDetails($content);

                $warning = array(
                    'warning_definition_id' => 0,
                    'points_default' => $xenOptions->th_customPointsDefault_warnings,
                    'expiry_type' => $xenOptions->th_customExpiry_warnings['type'],
                    'expiry_default' => !empty($xenOptions->th_customExpiry_warnings['default']) ? $xenOptions->th_customExpiry_warnings['default'] : '',
                    'extra_user_group_ids' => '',
                    'is_editable' => 1,
                    'title' => ''
                );

                $conversationTitle = new XenForo_Phrase(
                    $warningModel->getWarningDefinitionConversationTitlePhraseName(0));
                $conversationMessage = new XenForo_Phrase(
                    $warningModel->getWarningDefinitionConversationTextPhraseName(0));
                $replyMessage = new XenForo_Phrase($warningModel->getWarningDefinitionReplyTextPhraseName(0));

                $replace = array(
                    '{title}' => $contentTitle,
                    '{content}' => $contentDetails,
                    '{url}' => $warningHandler->getContentUrl($content, true),
                    '{name}' => $user['username'],
                    '{staff}' => $visitor['username']
                );
                $warning['conversationTitle'] = strtr((string) $conversationTitle, $replace);
                $warning['conversationMessage'] = strtr((string) $conversationMessage, $replace);
                $warning['replyMessage'] = strtr((string) $replyMessage, $replace);

                return $this->responseView('XenForo_ViewPublic_Member_WarnFill', '',
                    array(
                        'warning' => $warning
                    ));
            }
        }

        $GLOBALS['XenForo_ControllerPublic_Member'] = $this;

        $response = parent::actionWarn();

        if ($response instanceof XenForo_ControllerResponse_View) {
            if ($fill) {
                $userId = $this->_input->filterSingle('user_id', XenForo_Input::UINT);
                $user = $this->getHelper('UserProfile')->getUserOrError($userId);
                $visitor = XenForo_Visitor::getInstance();

                $contentInput = $this->_input->filter(
                    array(
                        'content_type' => XenForo_Input::STRING,
                        'content_id' => XenForo_Input::UINT
                    ));

                if (!$contentInput['content_type']) {
                    $contentInput['content_type'] = 'user';
                    $contentInput['content_id'] = $user['user_id'];
                }

                /* @var $warningModel XenForo_Model_Warning */
                $warningModel = $this->getModelFromCache('XenForo_Model_Warning');

                $warningHandler = $warningModel->getWarningHandler($contentInput['content_type']);
                if (!$warningHandler) {
                    return $this->responseNoPermission();
                }

                $content = $warningHandler->getContent($contentInput['content_id']);

                if (!$content || !$warningHandler->canView($content) ||
                     !$warningHandler->canWarn($user['user_id'], $content)) {
                    return $this->responseNoPermission();
                }

                $contentTitle = $warningHandler->getContentTitle($content);
                $contentDetails = $warningHandler->getContentDetails($content);

                $warningDefinitionId = $response->params['warning']['warning_definition_id'];

                $replyMessagePhraseName = $warningModel->getWarningDefinitionReplyTextPhraseName($warningDefinitionId);
                $replyMessage = new XenForo_Phrase($replyMessagePhraseName);

                // TODO should probably create blank phrases on installation?
                if ($replyMessage == $replyMessagePhraseName) {
                    $replyMessage = '';
                }

                $replace = array(
                    '{title}' => $contentTitle,
                    '{content}' => $contentDetails,
                    '{url}' => $warningHandler->getContentUrl($content, true),
                    '{name}' => $user['username'],
                    '{staff}' => $visitor['username']
                );
                $response->params['warning']['replyMessage'] = strtr((string) $replyMessage, $replace);
            } elseif (!empty($response->params['contentType'])) {
                $contentType = $response->params['contentType'];

                if ($contentType == 'post' || $contentType == 'profile_post') {
                    if ($xenOptions->th_warnings_allowReplyToContent) {
                        $response->params['canReplyToContent'] = true;

                        if ($contentType == 'post') {
                            $response->params['canLockContent'] = true;
                        }
                    }
                }
            }
        } elseif ($response instanceof XenForo_ControllerResponse_Redirect) {
            if ($this->_request->isPost()) {
                if ($xenOptions->th_warnings_sendEmail) {
                    $userId = $this->_input->filterSingle('user_id', XenForo_Input::UINT);
                    $user = $this->getHelper('UserProfile')->getUserOrError($userId);

                    $emailInput = $this->_input->filter(
                        array(
                            'conversation_title' => XenForo_Input::STRING,
                            'conversation_message' => XenForo_Input::STRING
                        ));

                    if ($emailInput['conversation_title'] && $emailInput['conversation_message']) {
                        $visitor = XenForo_Visitor::getInstance();

                        $bbCodeParserText = XenForo_BbCode_Parser::create(XenForo_BbCode_Formatter_Base::create('Text'));
                        $messageInfo['messageText'] = new XenForo_BbCode_TextWrapper($emailInput['conversation_message'],
                            $bbCodeParserText);

                        $bbCodeParserHtml = XenForo_BbCode_Parser::create(
                            XenForo_BbCode_Formatter_Base::create('HtmlEmail'));
                        $messageInfo['messageHtml'] = new XenForo_BbCode_TextWrapper($emailInput['conversation_message'],
                            $bbCodeParserHtml);

                        $mail = XenForo_Mail::create('th_warning_warnings',
                            array(
                                'title' => $emailInput['conversation_title'],
                                'message' => $messageInfo
                            ), $user['language_id']);

                        $mail->enableAllLanguagePreCache();
                        $mail->queue($user['email'], $user['username']);
                    }
                }
            }
        }

        return $response;
    }
}