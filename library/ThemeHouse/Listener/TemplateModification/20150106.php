<?php

class ThemeHouse_Listener_TemplateModification extends ThemeHouse_Listener_Template
{

    /**
     *
     * @var ThemeHouse_Listener_TemplateModification
     */
    protected $_template = null;

    /**
     *
     * @param string $contents
     * @param XenForo_Template_Abstract $template
     */
    public function __construct(&$contents, XenForo_Template_Abstract $template = null)
    {
        $this->_template = $this;
        
        parent::__construct($contents, null);
    }

    /**
     *
     * @see XenForo_Template_Abstract::addRequiredExternal()
     */
    public function addRequiredExternal($type, $filename)
    {
        if ($type == 'css') {
            $filename = $filename . '.css';
        }
        $this->_prepend('<xen:require ' . $type . '="' . $filename . '" />');
    }

    /**
     *
     * @see XenForo_Template_Abstract::getParams()
     */
    public function getParams()
    {
        return array();
    }

    /**
     *
     * @see ThemeHouse_Listener_Template::_render()
     */
    protected function _render($templateName, $viewParams = null)
    {
        return '<xen:include template="' . $templateName . '" />';
    }
}