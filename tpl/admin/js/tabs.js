// $Id$

/* idTabs ~ Sean Catchpole - Version 1.0 */

(function($){
	$.fn.idTabs = function()
	{
    //Setup Tabs
	var ul = $('ul', this); //Save scope
	var self = this;

	var list = $('li', ul).bind('click', function()
	{
		var elm = $(this);
		// we set selected_section to keep active tab opened after form submit
		// we do it for all forms to fix settings_dev situation: forms under tabs 
		if ($(self).hasClass('cm-track')) {
			$('input[name=selected_section]').val(this.id);
		}

		if (elm.hasClass('cm-js') == false) {
			return true;
		}

		/*if (hndl[$(ul).attr('id')]) {
			if (hndl[$(ul).attr('id')](elm.attr('id')) == false) {
				return false;
			}
		}*/


		var id = '#content_' + this.id;
		var aList = []; //save tabs
		var idList = []; //save possible elements
		$('li', ul).each(function()
		{
			if(this.id) {
				aList[aList.length] = this;
				idList[idList.length] = '#content_' + this.id;
			}
		});

		//Clear tabs, and hide all
		for (i in aList) {
			$(aList[i]).removeClass('cm-active');
		}

		for (i in idList) {
			$(idList[i]).hide();
		}

		//Select clicked tab and show content
		elm.addClass('cm-active');

		// Switch buttons block only if:
		// 1. Current tab is in form and this form has cm-toggle-button class on buttons block or current tab does not belong to any form
		// 2. Current tab lays on is first-level tab
		var id_obj = $(id);
		if (($('.cm-toggle-button', id_obj.parents('form')).length > 0 || id_obj.parents('form').length == 0) && id_obj.parents('.cm-tabs-content').length == 1) {
			if (id_obj.hasClass('cm-hide-save-button'))	{
				$('.cm-toggle-button').hide();
			} else {
				$('.cm-toggle-button').show();
			}
		}

		// Create tab content if it is not exist
		if (elm.hasClass('cm-ajax') && id_obj.length == 0) {
			$(self).after('<div id="' + id.substr(1) + '"></div>');
			id_obj = $(id);
			jQuery.ajaxRequest($('a', elm).attr('href'), {result_ids: id.substr(1), callback: [id_obj, 'show']});

			return false;
		} else {
			id_obj.show();
		}

		$.ceFloatingBar();

		return false; //Option for changing url
	});
	
    //Select default tab
	var test;
	if ((test = list.filter('.cm-active')).length) {
		test.trigger('click'); //Select tab with class 'cm-active'
	} else {
		list.filter(':first').trigger('click'); //Select first tab
	}

	$('li.cm-ajax.cm-js').each(function(){
		var self = $(this);
		
		// Check if the active content needs to be loaded
		if (self.hasClass('cm-active')) {
			content = $('#content_' + this.id).html().replace(/<!--.*?-->/, '').replace(/(^\s+|\s+$)/, '');
			if (content.length) {
				return true;
			}
		}
		
		if (!self.data('passed') && $('a', self).attr('href')) {
			self.data('passed', true);
			var id = 'content_' + this.id;
			// Check if the tab content block already exists
			if ($('#' + id).length == 0) {
				self.parents('.cm-j-tabs').eq(0).next().prepend('<div id="' + id + '"></div>');
			}
			$('#' + id).hide();
			jQuery.ajaxRequest($('a', self).attr('href'), {result_ids: id, hidden: true, repeat_on_error: true});
		}
	});

	return this; //Chainable
};

})(jQuery);
