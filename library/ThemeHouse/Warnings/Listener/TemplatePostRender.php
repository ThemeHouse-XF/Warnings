<?php

class ThemeHouse_Warnings_Listener_TemplatePostRender extends ThemeHouse_Listener_TemplatePostRender
{
    protected function _getTemplates()
    {
        return array(
            'member_warn',
            'warning_action_edit',
            'warning_edit',
            'warning_list',
            'PAGE_CONTAINER'
        );
    }

    public static function templatePostRender($templateName, &$content, array &$containerData, XenForo_Template_Abstract $template)
    {
        $templatePostRender = new ThemeHouse_Warnings_Listener_TemplatePostRender($templateName, $content, $containerData, $template);
        list($content, $containerData) = $templatePostRender->run();
    }

    protected function _memberWarn()
    {
        $this->_template->addRequiredExternal('js', 'js/themehouse/warnings/member_warn.js');
    }

    protected function _warningActionEdit()
    {
        $pattern = '#(<dl class="ctrlUnit">\s*<dt>' . new XenForo_Phrase('action_to_take') . '.*)(</ul>\s*</dd>)#Us';
        $replacement = '${1}' . $this->_escapeDollars($this->_render('th_warning_action_edit_user_groups_warnings')) . '${2}';
        $this->_patternReplace($pattern, $replacement);
        $pattern = '#<dl class="ctrlUnit submitUnit">#';
        $this->_prependTemplateAtPattern($pattern, 'th_warning_action_edit_warnings');
    }

    protected function _warningEdit()
    {
        $pattern = '#<h3 class="textHeading">#';
        $this->_prependTemplateAtPattern($pattern, 'th_warning_edit_warnings', null, $this->_contents, 1);
    }

    protected function _warningList()
    {
        $viewParams = $this->_fetchViewParams();
        $this->_appendTemplate('th_topctrl_warnings', $viewParams, $this->_containerData['topctrl']);
    }

    protected function _pageContainer()
    {
        if ($this->_template instanceof XenForo_Template_Admin) {
            $codeSnippet =  '<div id="content">';
            $templateName = 'th_please_reupgrade_warnings';
            $this->_appendTemplateAfterCodeSnippet($codeSnippet, $templateName);
        }
    }

}