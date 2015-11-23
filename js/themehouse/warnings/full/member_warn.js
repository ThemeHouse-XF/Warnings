/** @param {jQuery} $ jQuery Object */
!function($, window, document, _undefined)
{	
	XenForo.WarningDefinitionRadioButton = function($control)
	{
		$form = $control.closest('form');
		
		$autoTitlePublicWarning = $form.data('autotitlepublicwarning');
		
		if ($autoTitlePublicWarning) {
			$control.click(handleClick);
		}
		
		function handleClick(e)
		{
			$control = $(e.target);
			
			$label = $control.closest('label');
			
			if ($control.val() > 0) {
				$title = $label.text();
			} else {
				$title = $('input[name=title]').val();
			}
			
			$('input[name=public_warning]').val($title);
		}

	};
	
	XenForo.CustomWarningTitle = function($control)
	{		
		$form = $control.closest('form');

		$autoTitlePublicWarning = $form.data('autotitlepublicwarning');
		
		if ($autoTitlePublicWarning) {
			$control.change(handleChange);
		}
		
		function handleChange(e)
		{
			$('input[name=public_warning]').val($control.val());
		}

	};
	
	XenForo.CustomWarningFormFillerControl = function($control)
	{
		$control.trigger('click');
		
		$form = $control.closest('form');
		
		$defaultContentAction = $form.data('defaultcontentaction');
		
		if ($defaultContentAction == 'delete') {
			$('input[name=content_action][value=delete_content]').trigger('click');
		} else if ($defaultContentAction == 'post') {
			$('input[name=content_action][value=public_warning]').trigger('click');
		}
	};
	
	XenForo.register('input[name=warning_definition_id]', 'XenForo.WarningDefinitionRadioButton');
	XenForo.register('input[name=title]', 'XenForo.CustomWarningTitle');
	XenForo.register('#customWarning', 'XenForo.CustomWarningFormFillerControl');
		
}
(jQuery, this, document);