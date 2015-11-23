<?php

class ThemeHouse_Warnings_Listener_TemplateModification extends ThemeHouse_Listener_TemplateModification
{

    public static function warningActionEdit(array $matches)
    {
        $modification = new ThemeHouse_Warnings_Listener_TemplateModification($matches[0]);

        return $modification->_warningActionEdit();
    }

    protected function _warningActionEdit()
    {
        if (XenForo_Application::$versionId < 1030000) {
            $this->_append('<xen:option value="groups_length" label="{xen:phrase th_added_to_selected_groups_for_time_period_warnings}:">
                <xen:radio name="groups_length_type_base">
                <xen:option value="permanent" selected="{$action.groups_length_type} == \'permanent\'">{xen:phrase permanent}</xen:option>
                <xen:option value="other" selected="{$action.groups_length_type} != \'permanent\'" label="{xen:phrase temporary}:">
                <xen:disabled>
                <xen:spinbox name="groups_length" value="{xen:if $action.groups_length, $action.groups_length, 1}" min="0" />
                <xen:select name="groups_length_type" value="{xen:if \'{$action.groups_length_type} == "permanent"\', \'months\', $action.groups_length_type}"
                inputclass="autoSize">
                <xen:option value="days">{xen:phrase days}</xen:option>
                <xen:option value="weeks">{xen:phrase weeks}</xen:option>
                <xen:option value="months">{xen:phrase months}</xen:option>
                <xen:option value="years">{xen:phrase years}</xen:option>
                </xen:select>
                </xen:disabled>
                </xen:option>
                </xen:radio>
                <xen:checkbox name="extra_user_group_ids_length">
                <xen:options source="$userGroupOptionsLength" />
                </xen:checkbox>
                </xen:option>');
        }

        return $this->_contents;
    }
}