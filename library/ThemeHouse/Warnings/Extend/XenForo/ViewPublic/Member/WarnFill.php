<?php
if (false) {

    class XFCP_ThemeHouse_Warnings_Extend_XenForo_ViewPublic_Member_WarnFill extends XenForo_ViewPublic_Member_WarnFill
    {
    }
}

class ThemeHouse_Warnings_Extend_XenForo_ViewPublic_Member_WarnFill extends XFCP_ThemeHouse_Warnings_Extend_XenForo_ViewPublic_Member_WarnFill
{

    public function renderJson()
    {
        $response = parent::renderJson();

        $warning = $this->_params['warning'];

        $response['formValues']['textarea[name=reply_message]'] = $warning['replyMessage'];

        return $response;
    }
}