<?php
if (false) {

    class XFCP_ThemeHouse_Warnings_Extend_XenForo_DataWriter_Warning extends XenForo_DataWriter_Warning
    {
    }
}

class ThemeHouse_Warnings_Extend_XenForo_DataWriter_Warning extends XFCP_ThemeHouse_Warnings_Extend_XenForo_DataWriter_Warning
{

    /**
     *
     * @see XenForo_DataWriter_Warning::_postSave()
     */
    protected function _postSave()
    {
        $visitor = XenForo_Visitor::getInstance();

        $GLOBALS['XenForo_DataWriter_Warning'] = $this;

        parent::_postSave();

        if (!empty($GLOBALS['XenForo_ControllerPublic_Member'])) {
            /* @var $controller XenForo_ControllerPublic_Member */
            $controller = $GLOBALS['XenForo_ControllerPublic_Member'];

            if ($this->isInsert()) {
                $contentType = $this->get('content_type');

                $xenOptions = XenForo_Application::get('options');

                if ($xenOptions->th_warnings_allowReplyToContent) {
                    $input = $controller->getInput()->filter(
                        array(
                            'reply_message' => XenForo_Input::STRING,
                            'lock_content' => XenForo_Input::BOOLEAN
                        ));

                    $content = $this->getExtraData(self::DATA_CONTENT);

                    if ($input['reply_message']) {
                        if ($contentType == 'post') {
                            /* @var $postModel XenForo_Model_Post */
                            $postModel = $this->getModelFromCache('XenForo_Model_Post');

                            /* @var $writer XenForo_DataWriter_DiscussionMessage_Post */
                            $writer = XenForo_DataWriter::create('XenForo_DataWriter_DiscussionMessage_Post');
                            $writer->set('user_id', $visitor['user_id']);
                            $writer->set('username', $visitor['username']);
                            $writer->set('message', $input['reply_message']);
                            $writer->set('message_state', $postModel->getPostInsertMessageState($content, $content));
                            $writer->set('thread_id', $content['thread_id']);
                            $writer->setExtraData(XenForo_DataWriter_DiscussionMessage_Post::DATA_FORUM, $content);
                            $writer->setOption(XenForo_DataWriter_DiscussionMessage_Post::OPTION_MAX_TAGGED_USERS, $visitor->hasPermission('general', 'maxTaggedUsers'));
                            $writer->save();
                        } elseif ($contentType == 'profile_post') {
                            /* @var $writer XenForo_DataWriter_ProfilePostComment */
                            $writer = XenForo_DataWriter::create('XenForo_DataWriter_ProfilePostComment');
                            $writer->set('user_id', $visitor['user_id']);
                            $writer->set('username', $visitor['username']);
                            $writer->set('message', $input['reply_message']);
                            $writer->set('profile_post_id', $content['profile_post_id']);
                            //$writer->setExtraData(XenForo_DataWriter_ProfilePostComment::DATA_PROFILE_USER, $user);
                            $writer->setExtraData(XenForo_DataWriter_ProfilePostComment::DATA_PROFILE_POST, $content);
                            $writer->setOption(XenForo_DataWriter_ProfilePostComment::OPTION_MAX_TAGGED_USERS, $visitor->hasPermission('general', 'maxTaggedUsers'));
                            $writer->save();
                        }
                    }

                    if ($contentType == 'post' && $input['lock_content']) {
                        /* @var $writer XenForo_DataWriter_Discussion_Thread */
                        $writer = XenForo_DataWriter::create('XenForo_DataWriter_Discussion_Thread');
                        $writer->setExistingData($content);
                        $writer->set('discussion_open', 0);
                        $writer->save();
                    }
                }
            }
        }
    }

    /**
     *
     * @see XenForo_DataWriter_Warning::_postDelete()
     */
    protected function _postDelete()
    {
        $GLOBALS['XenForo_DataWriter_Warning'] = $this;

        parent::_postDelete();
    }
}