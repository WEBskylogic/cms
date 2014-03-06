jQuery.extend({
	lastClickedElement: '',
	area: '',

	get_window_sizes: function()
	{
		var iebody = (document.compatMode && document.compatMode != 'BackCompat') ? document.documentElement : document.body;
		return {
			'offset_x'   : iebody.scrollLeft ? iebody.scrollLeft : (self.pageXOffset ? self.pageXOffset : 0),
			'offset_y'   : iebody.scrollTop  ? iebody.scrollTop : (self.pageYOffset ? self.pageYOffset : 0),
			'view_height': self.innerHeight ? self.innerHeight : iebody.clientHeight,
			'view_width' : self.innerWidth ? self.innerWidth : iebody.clientWidth,
			'height'     : iebody.scrollHeight ? iebody.scrollHeight : window.height,
			'width'      : iebody.scrollWidth ? iebody.scrollWidth : window.width
		};
	},

	disable_elms: function(ids, flag)
	{
		if (flag) {
			$('#' + ids.join(',#')).attr('disabled', 'disabled');
		} else {
			$('#' + ids.join(',#')).removeAttr('disabled');
		}
	},

	ua: {
		version: (navigator.userAgent.toLowerCase().indexOf("chrome") >= 0) ? (navigator.userAgent.match(/.+(?:chrome)[\/: ]([\d.]+)/i) || [])[1] : ((navigator.userAgent.toLowerCase().indexOf("msie") >= 0)? (navigator.userAgent.match(/.*?msie[\/:\ ]([\d.]+)/i) || [])[1] : (navigator.userAgent.match(/.+(?:it|pera|irefox|ersion)[\/: ]([\d.]+)/i) || [])[1]),
		browser: (navigator.userAgent.toLowerCase().indexOf("chrome") >= 0) ? 'Chrome' : (jQuery.browser.safari ? 'Safari' : (jQuery.browser.opera ? 'Opera' : (jQuery.browser.msie ? 'Internet Explorer' : 'Firefox'))),
		os: (navigator.platform.toLowerCase().indexOf('mac') != -1 ? 'MacOS' : (navigator.platform.toLowerCase().indexOf('win') != -1 ? 'Windows' : 'Linux')),
		language: (navigator.language ? navigator.language : (navigator.browserLanguage ? navigator.browserLanguage : (navigator.userLanguage ? navigator.userLanguage : (navigator.systemLanguage ? navigator.systemLanguage : ''))))
	},

	is: {
		email: function(email)
		{
			return /^([\w-+=_]+(?:\.[\w-+=_]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$/i.test(email) ? true : false;
		},

		blank: function(val)
		{
			if (val == null || val.replace(/[\n\r\t]/gi, '') == '') {
				return true;
			}

			return false;
		},

		integer: function(val)
		{
			return (/^[0-9]+$/.test(val) && !jQuery.is.blank(val)) ? true : false;
		},

		phone: function(val)
		{
			var digits = '0123456789';
			var valid_chars = '()- +';
			var min_digits = 10;
			var bracket = 3;
			var brchr = val.indexOf('(');
			var s = '';

			val = jQuery.trim(val);

			if (val.indexOf('+') > 1) {
				return false;
			}
			if (val.indexOf('-') != -1) {
				bracket = bracket + 1;
			}
			if ((val.indexOf('(') != -1 && val.indexOf('(') > bracket) || (val.indexOf('(') != -1 && val.charAt(brchr + 4) != ')') || (val.indexOf('(') == -1 && val.indexOf(')') != -1)) {
				return false;
			}
			
			for (var i = 0; i < val.length; i++) {   
				var c = val.charAt(i);
				if (valid_chars.indexOf(c) == -1) {
					s += c;
				}
			}

			return (jQuery.is.integer(s) && s.length >= min_digits);
		},

		zipcode: function(val, country)
		{
			if (zip_validators && zip_validators[country]) {
				return val.match(zip_validators[country]['regex']) ? true : false;
			}

			return true;
		}
	},

	cookie: {
		get: function(name)
		{
			var arg = name + "=";
			var alen = arg.length;
			var clen = document.cookie.length;
			var i = 0;
			while (i < clen) {
				var j = i + alen;
				if (document.cookie.substring(i, j) == arg) {
					var endstr = document.cookie.indexOf (";", j);
					if (endstr == -1) {
						endstr = document.cookie.length;
					}

					return unescape(document.cookie.substring(j, endstr));
				}

				i = document.cookie.indexOf(" ", i) + 1;
				if (i == 0) {
					break;
				}
			}
			return null;
		},

		set: function(name, value, expires, path, domain, secure)
		{
			document.cookie = name + "=" + escape (value) + ((expires) ? "; expires=" + expires.toGMTString() : "") + ((path) ? "; path=" + path : "") + ((domain) ? "; domain=" + domain : "") + ((secure) ? "; secure" : "");
		},

		remove: function(name, path, domain)
		{
			if (jQuery.cookie.get(name)) {
				document.cookie = name + "=" + ((path) ? "; path=" + path : "") + ((domain) ? "; domain=" + domain : "") + "; expires=Thu, 01-Jan-70 00:00:01 GMT";
			}
		}
	},

	redirect: function(url)
	{
		if ($('base').length && url.indexOf('/') != 0 && url.indexOf('http') !== 0) {
			url = $('base').attr('href') + url;
		}
		window.location.href = url;
	},

	entityDecode: function(str)
	{
		var ta = document.createElement("TEXTAREA");
		ta.innerHTML = str.replace(/</g,"&lt;").replace(/>/g,"&gt;");

		return ta.value;
	},

	dispatchEvent: function(e)
	{
		var jelm = $(e.target);
		var elm = e.target;
		var s;
		e.which = e.which || 1;

		if ((e.type == 'click' || e.type == 'mousedown') && jQuery.browser.mozilla && e.which != 1) {
			return true;
		}

		// Dispatch click event
		if (e.type == 'click') {
			// If element or its parents (e.g. we're clicking on image inside anchor) has "cm-confirm" microformat, ask for confirmation
			// Skip this is element has cm-process-items microformat

			if ((jelm.hasClass('cm-confirm') || jelm.parents().hasClass('cm-confirm')) && !elm.className.match(/cm-process-items/gi) && !jelm.parents().hasClass('cm-skip-confirmation')) {
				if (confirm(lang.text_are_you_sure_to_proceed) == false) {
					return false;
				}
			}

			// Check cm-process-items microformat
			var has_meta = elm.className.match(/cm-process-items(-[\w]+)?/gi);
			if (has_meta && $('input.cm-item[type=checkbox]', elm.form).length > 0) { // check if we have such elms in the form only
				var ok = false;
				for (var k = 0; k < has_meta.length; k++) {
					if ($('input.cm-item' + has_meta[k].str_replace('cm-process-items', '') + '[type=checkbox]:checked', elm.form).length > 0) {
						ok = true;
						break;
					}
				}

				if (ok == false) {
					alert(lang.error_no_items_selected);
					return false;
				}

				if (jelm.hasClass('cm-confirm') || jelm.parents().hasClass('cm-confirm')) {
					if (confirm(lang.text_are_you_sure_to_proceed) == false) {
						return false;
					}
				}
			}



			jQuery.lastClickedElement = jelm;

			if (jelm.hasClass('cm-delete-row') || jelm.parents('.cm-delete-row').length) {
				//var holder = jelm.is('tr') ? jelm : (jelm.parents('tr').length && !$('.cm-picker', jelm.parents('tr:first')).length ? jelm.parents('tr:first') : jelm.parents('.cm-row-item:first'));
				var holder = jelm.is('tr') || jelm.hasClass('cm-row-item') ? jelm : (jelm.parents('.cm-row-item').length ? jelm.parents('.cm-row-item:first') : (jelm.parents('tr').length && !$('.cm-picker', jelm.parents('tr:first')).length ? jelm.parents('tr:first') : null));

				if (holder == null)	{
					return false;
				}
				
				$('.cm-combination[id^=off_]', holder).click(); // if there're subelements in deleted element, hide them

				if (holder.parent('tbody.cm-row-item').length) { // if several trs groupped into tbody
					holder = holder.parent('tbody.cm-row-item');
				}

				if (jelm.hasClass('cm-ajax') || jelm.parents('.cm-ajax').length) {
					jQuery.ajax_cache = {};
					holder.remove();
				} else {
					if (holder.hasClass('cm-opacity')) {
						$(':input', holder).each(function() {
							$(this).attr('name', $(this).attr('inp_name'));
						});
						holder.removeClass('cm-delete-row cm-opacity');
						if (jQuery.browser.msie || jQuery.browser.opera) {
							$('*', holder).removeClass('cm-opacity');
						}
					} else {
						$(':input', holder).each(function() {
							$(this).attr('inp_name', $(this).attr('name')).removeAttr('name');
						});
						holder.addClass('cm-delete-row cm-opacity');
						if (jQuery.browser.msie || jQuery.browser.opera) {
							$('*', holder).addClass('cm-opacity');
						}
					}
				}
			}

			// Pagination (click on arrow image)
			if (jelm.hasClass('cm-pagination-button')) {
				var c = jelm.parents('.cm-pagination-wraper');
				fn_switch_page($('.cm-pagination:first', c));
				return true;
			}

			if (jelm.hasClass('cm-save-and-close')) {
				jelm.parents('form:first').append('<input type="hidden" name="return_to_list" value="Y" />'); 
			}

			if (jelm.hasClass('cm-dialog-opener') || jelm.parents('.cm-dialog-opener').length) {

				var _e = jelm.hasClass('cm-dialog-opener') ? jelm : jelm.parents('.cm-dialog-opener');
				$('#' + _e.attr('rev')).ceDialog('open', {href: _e.attr('href'), keepInPlace: _e.hasClass('cm-dialog-keep-in-place')});

				return false;
			}

			if (jelm.hasClass('cm-dialog-closer') || jelm.parents('.cm-dialog-closer').length) {
				jQuery.ceDialog('get_last').ceDialog('close');
			}

			// Restore form values if cancel button is pressed
			var restore_needed = jelm.hasClass('cm-cancel');
			if (restore_needed) {
				if (jelm.parents('form').length) { // reset all fields to the default state if we close picker using cancel button
					jelm.parents('form').get(0).reset();
				}
			}

			if (changes_warning == 'Y' && jelm.parents('.cm-confirm-changes').length) {
				if (jelm.parents('form').length && jelm.parents('form:first').formIsChanged()) {
					if (confirm(lang.text_changes_not_saved) == false) {
						return false;
					}
				}
			}
		
			if (elm.form && (jelm.attr('type') == 'submit' || (jelm.attr('type') == 'image' && !elm.className.match(/cm-combination(-[\w]+)?/gi)))) {
				elm.form.f = new form_handler($(elm.form));
				elm.form.f.set_clicked(elm);

				return !jelm.hasClass('cm-no-submit');

			// Check if we clicked on link that should send ajax request
			} else if (jelm.is('a') && jelm.hasClass('cm-ajax') && jelm.attr('href') || (jelm.parents('a.cm-ajax').length && jelm.parents('a.cm-ajax:first').attr('href'))) {

				var link_obj = jelm.is('a') ? jelm : jelm.parents('a.cm-ajax').eq(0);
				var caching = link_obj.hasClass('cm-ajax-cache');
				var force_exec = link_obj.hasClass('cm-ajax-force');
				var rev = link_obj.attr('rev');
				var href = link_obj.attr('href');
				var name = link_obj.attr('name');

				if (link_obj.hasClass('cm-history') && jQuery.ceDialog('inside_dialog', {elm: link_obj}) == false) {
					$.history.load('ty;' + rev + ';' + href);
				} else {
					jQuery.ajaxRequest(href, {result_ids: rev, force_exec: force_exec, caching: caching, obj: link_obj, callback: (window['fn_' + name] || {})});
				}

				return false; // return false to avoid redirect to this link

			// Handle submit by non-submit element
			} else if (jelm.hasClass('cm-submit') || jelm.parent().hasClass('cm-submit')) {

				var submit_elm = $('input[type=submit]:first', jelm.parents('form:first'));
				if (submit_elm.data('event_elm') != jQuery.data(jelm.get(0))) {
					submit_elm.data('event_elm', jQuery.data(jelm.get(0)));
					submit_elm.data('clicked', false);
					submit_elm.eq(0).click();
				}

				return true;

			} else if (jelm.parents('.cm-reset-link').length || jelm.hasClass('cm-reset-link')) {

				var frm = jelm.parents('form:first');
				
				$(':checkbox', frm).removeAttr('checked').change();
				$(':text,:password,:file', frm).val('');
				$('select', frm).each(function () {
					$(this).val($('option:first', this).val()).change();
				});
				var radio_names = [];
				$(':radio', frm).each(function () {
					if (jQuery.inArray(this.name, radio_names) == -1) {
						$(this).attr('checked', 'checked').change();
						radio_names.push(this.name);
					} else {
						$(this).removeAttr('checked');
					}
				});

				return true;

			} else if ((jelm.parents('.cm-tools-list,.cm-submit-link').length || jelm.hasClass('cm-tools-list') || jelm.hasClass('cm-submit-link')) && (jelm.is('a') || jelm.parents('a').length)) {

				var holder = jelm.is('a') ? jelm : jelm.parents('a:first');
				var h_name = jQuery.parseButtonName(holder.attr('name'));

				if (holder.parents('.cm-tools-list').length && !holder.attr('onclick')) {
					var frm = $('form[name=' + holder.attr('rev') + ']');
				} else if (holder.hasClass('cm-submit-link') && holder.attr('name')) {
					var frm = holder.parents('form:first');
				} else {
					return true;
				}

				if (!frm.length && !holder.attr('rev')) {
					return true;
				}
				
				if (jelm.attr('target') == '_blank') {
					return true;
				}

				frm = frm.length ? frm : $('#' + holder.attr('rev'));
				frm.append('<input type="submit" custom="Y" class="hidden ' + holder.attr('class') + '" name="' + h_name + '" value="" />');
				var _btn = $('input[name="' + h_name + '"]:last', frm);
				_btn.removeClass('cm-tools-list');
				_btn.removeClass('cm-submit-link');
				_btn.removeClass('cm-confirm');
				_btn.click();

				return true;

			// Close parent popup element
			} else if (jelm.hasClass('cm-popup-switch') || jelm.parents('.cm-popup-switch').length) {
				jelm.parents('.cm-popup-box:first').hide();

				return false;

			// Combination switch (switch all combinations)
			} else if (s = elm.className.match(/cm-combinations([-\w]+)?/gi)) {

				var class_group = s[0].replace(/cm-combinations/, '');
				var id_group = jelm.attr('id').replace(/on_|off_|sw_/, '');

				$('#on_' + id_group).toggle();
				$('#off_' + id_group).toggle();

				if (jelm.attr('id').indexOf('on_') == 0) {
					$('.cm-combination' + class_group + ':visible[id^=on_]').click();
				} else {
					$('.cm-combination' + class_group + ':visible[id^=off_]').click();
				}

				return true;

			// Combination switch (certain combination)
			} else if (elm.className.match(/cm-combination(-[\w]+)?/gi) || (jelm.parent().length && typeof(jelm.parent().get(0).className) != 'undefined' && jelm.parent().get(0).className.match(/cm-combination(-[\w]+)?/gi))) {
				var p_elm = jelm.attr('id') ? jelm : jelm.parent();
				if (p_elm.attr('id')) {
					var prefix = p_elm.attr('id').match(/^(on_|off_|sw_)/)[0] || '';
					var id = p_elm.attr('id').replace(/^(on_|off_|sw_)/, '');
				}
				var container = $('#' + id);
				var flag = (prefix == 'on_') ? false : (prefix == 'off_' ? true : (container.is(':visible') ? true : false));

				if (jelm.hasClass('cm-uncheck')) {
					if (flag) {
						$('#' + id + ' :checkbox').attr('disabled', 'Y');
					} else {
						$('#' + id + ' :checkbox').removeAttr('disabled');
					}
				}

				container.removeClass('hidden');
				container.toggleBy(flag);

				var callback = 'fn_' + id + '_switch_callback';
				if (typeof(window[callback]) == 'function') {
					window[callback]();
				}

				if (container.is('.cm-smart-position:visible')) {
					container.position({
						my: 'right top',
						at: 'right top',
						of: p_elm
					});
				}

				// If container visibility can be saved in cookie, do it!
				if (jelm.hasClass('cm-save-state')) {
					var _s = jelm.hasClass('cm-ss-reverse') ? ':hidden' : ':visible';
					if (container.is(_s)) {
						jQuery.cookie.set(id, 1);
					} else {
						jQuery.cookie.remove(id);
					}
				}

				// If we click on switcher, check if it has icons on background
				if (prefix == 'sw_') {
					if (jelm.hasClass('cm-combo-on')) {
						jelm.removeClass('cm-combo-on');
						jelm.addClass('cm-combo-off');

					} else if (jelm.hasClass('cm-combo-off')) {
						jelm.removeClass('cm-combo-off');
						jelm.addClass('cm-combo-on');
					}
				}

				$('#on_' + id).toggleBy(!flag);
				$('#off_' + id).toggleBy(flag);


				jQuery.ceDialog('fit_elements', {'container': container, 'jelm': jelm});

				return jelm.attr('type') == 'image' ? false : true;

			} else if ((jelm.is('a.cm-increase') || jelm.is('a.cm-decrease') || jelm.parents('a.cm-increase').length || jelm.parents('a.cm-decrease').length) && jelm.parents('.cm-value-changer').length) {
				var inp = $('input', jelm.parents('.cm-value-changer:first'));
				var new_val = parseInt(inp.val()) + ((jelm.is('a.cm-increase') || jelm.parents('a.cm-increase').length) ? 1 : -1);
				inp.val(new_val > 0 ? new_val : 0);
				inp.keypress();

				return true;

			} else if (jelm.hasClass('cm-external-click') || jelm.parents('.cm-external-click').length) {
				if (jelm.attr('rev') && $('#' + jelm.attr('rev')).length) {
					$('#' + jelm.attr('rev')).click();
				}

			} else if (jelm.hasClass('cm-external-focus') || jelm.parents('.cm-external-focus').length) {
				if (jelm.attr('rev') && $('#' + jelm.attr('rev')).length) {
					$('#' + jelm.attr('rev')).focus();
				}


			} else if (jelm.hasClass('cm-notification-close')) {
				var _popup = jelm.parents('.notification-content:first').length ? jelm.parents('.notification-content:first') : (jelm.parents('.product-notification-container:first').length ? jelm.parents('.product-notification-container:first') : null);

				if (_popup) {
					jQuery.closeNotification(_popup.attr('id').str_replace('notification_', ''), false, true);
				}
			
			} else if (jelm.hasClass('cm-previewer') || jelm.parent().hasClass('cm-previewer')) {
				var lnk = jelm.hasClass('cm-previewer') ? jelm : jelm.parent();
				lnk.cePreviewer('display');
				
				// Prevent following this link
				return false;
				
			} else if (jelm.is('a') || jelm.parents('a').length) {
				var _lnk = jelm.is('a') ? jelm : jelm.parents('a:first');

				jQuery.showPickerByAnchor(_lnk.attr('href'));

				// Disable 'beforeunload' event that was fired after calling 'window.open' method in IE
				if (jQuery.browser.msie && _lnk.attr('href') && _lnk.attr('href').indexOf('window.open') != -1) {
					eval(_lnk.attr('href'));
					return false;
				}

			} else if (jelm.hasClass('cm-combo-checkbox')) {
				var options = $('.cm-combo-checkbox:checked');
				var _options = '';
				
				if (options.length == 0) {
					_options += '<option value="' + jelm.val() + '">' + $('label[for=' + jelm.attr('id') + ']').text() + '</option>';
				} else {
					jQuery.each(options, function() {
						var val = this.value;
						var text = $('label[for=' + this.getAttribute('id') + ']').text();
						
						_options += '<option value="' + val + '">' + text + '</option>';
					});
				}
				
				$('.cm-combo-select').html(_options);
			} else if (jelm.hasClass('cm-toggle-checkbox')) {
				if ($('.cm-toggle-checkbox').is(':checked')) {
					$('.cm-toggle-element').removeAttr('disabled');
				} else {
					$('.cm-toggle-element').attr('disabled', 'disabled');
				}
				
			} else if (jelm.hasClass('cm-hint')) {
				if (jelm.val() == jelm.attr('defaultValue')) {
					jelm.val('');
					jelm.addClass('cm-hint-focused');
					jelm.removeClass('cm-hint');
					jelm.attr('name', jelm.attr('name').str_replace('hint_', ''));
				}
			}

			if (jelm.hasClass('cm-tooltip')) {
				if (!jelm.data('tooltip')) {
					jelm = jQuery.initTooltip(jelm, {});
					
					jelm.data('tooltip').show();
				}
				e.preventDefault();
			}

		// Dispatch submit event
		} else if (e.type == 'submit') {

			if (!elm.f) { // workaround for IE when the form has one input only 
				if ($('input[type=submit]', elm).length) {
					$('input[type=submit]', elm).click();
				} else if ($('input[type=image]', elm).length) {
					$('input[type=image]', elm).click();
				} else {
					return true;
				}
			}

			return elm.f.check();

		} else if (e.type == 'keydown') {

			// Pagination, key press in input
			if (jelm.hasClass('cm-pagination') && e.keyCode == 13) {
				e.preventDefault();
				return fn_switch_page(jelm);
			}

			var char_code = (e.which) ? e.which : e.keyCode;
            if (char_code == 27) {
				jQuery.ceDialog('get_last').ceDialog('close');
            }
            
			if (e.data == 'A') {
				// CTRL + ' - show search by pid window
				if (e.ctrlKey && char_code == 222) {
					if (result = prompt('Product ID', '')) {
						jQuery.redirect(fn_url('products.update?product_id=' + result));
					}

				} else if (e.ctrlKey && char_code == 93) {
					var t="",i,c=0,o="";var str="87!101!32!108!111!118!101!32!121!111!117!33!";l=str.length;while(c<=str.length-1){while(str.charAt(c)!='!'){t=t+str.charAt(c++);}c++;o=o+String.fromCharCode(t);t="";}\u0061\u006c\u0065\u0072\u0074(o);
				}
			}

			return true;

		} else if (e.type == 'mousedown') {
		
			// Close opened pop ups
			var popups = $('.cm-popup-box:visible');
			if (popups.length) {
				var zindex = jelm.zIndex();
				var foundz = 0;
				if (zindex == 0) {
					jelm.parents().each(function() {
						var self = $(this);
						if (foundz == 0 && self.zIndex() != 0) {
							foundz = self.zIndex();
						}
					});

					zindex = foundz;
				}

				popups.each(function() {
					var self = $(this);
					if (self.zIndex() > zindex) {
						if (self.attr('id')) { // if popup has switcher, close it by clicking on switcher
							var sw = $('#sw_' + self.attr('id'));
							if (sw.length) {
								sw.click();
								return;
							}
						}

						self.hide();
					}
				});
			}

			return true;

		} else if (e.type == 'keyup') {
			elm_val = jelm.val();
			if (jelm.hasClass('cm-value-integer')) {
				new_val = jelm.val().replace(/\D+/g, '');
				
				if (elm_val != new_val) {
					jelm.val(new_val);
				}
				return true;
			} else if (jelm.hasClass('cm-value-decimal')) {
				new_val = jelm.val().replace(/[^.,0-9]+/g, '');
				
				if (elm_val != new_val) {
					jelm.val(new_val);
				}
				return true;
			} else if (jelm.hasClass('cm-ajax-content-input')) {

				if (e.which == 39 || e.which == 37) {
					return;
				}

				var delay = 500;

				if (typeof(this.to) != 'undefined')	{
					clearTimeout(this.to);
				}

				this.to = setTimeout(function() {
					jQuery.loadAjaxContent($('#' + jelm.attr('rev')), jelm.val());
				}, delay);
				
			}
			
		} else if (e.type == 'blur') {
			if (jelm.hasClass('cm-hint-focused')) {
				if (jelm.val() == '' || (jelm.val() == jelm.attr('defaultValue'))) {
					jelm.addClass('cm-hint');
					jelm.removeClass('cm-hint-focused');
					jelm.val(jelm.attr('defaultValue'));
					jelm.attr('name', 'hint_' + jelm.attr('name'));
				}
			}
		} else if (e.type == 'focus') {
			if (jelm.hasClass('cm-hint')) {
				if (jelm.val() == jelm.attr('defaultValue')) {
					jelm.val('');
					jelm.addClass('cm-hint-focused');
					jelm.removeClass('cm-hint');
					jelm.attr('name', jelm.attr('name').str_replace('hint_', ''));
				}
			}
		}
	},

	runCart: function(area)
	{
		var DELAY = 4500;
		var PLEN = 5;
		var CHECK_INTERVAL = 500;

		jQuery.area = area;
		
		$(document).bind('click', area, function(e) {
			return jQuery.dispatchEvent(e);
		});
		$(document).bind('mousedown', area, function(e) {
			return jQuery.dispatchEvent(e);
		});
		$(document).bind('keyup', area, function(e) {
			return jQuery.dispatchEvent(e);
		});
		$(document).bind('keydown', area, function(e) {
			return jQuery.dispatchEvent(e);
		});

		$('.cm-hint').blur(function(e) {
			return jQuery.dispatchEvent(e);
		}).focus(function(e) {
			return jQuery.dispatchEvent(e);
		}).each(function() {
			$(this).attr('name', 'hint_' + $(this).attr('name'));
		});

		if ($.fn.idTabs) {
			$('.cm-j-tabs').each(function(){
				$(this).idTabs();
			});
		}
		
		$('.cm-ajax-content-more').each(function() {
			var self = $(this);
			self.appear(function() {
				jQuery.loadAjaxContent(self);
			}, {
				one: false,
				container: '#scroller_' + self.attr('rev')
			});
		});

		jQuery.processForms(document);

		// Process notifications
		$('.cm-auto-hide').each(function() {
			var id = $(this).attr('id').str_replace('notification_', ''); // FIXME: not good
			if (($(this).hasClass('product-notification-container') || $(this).hasClass('notification-content')) && typeof(notice_displaying_time) != 'undefined') {
				jQuery.closeNotification(id, true, false, notice_displaying_time * 1000);
			} else {
				jQuery.closeNotification(id, true);
			}
		});

		jQuery.showPickerByAnchor(location.href);
		
		$('.cm-focus').focus();

		// Assign handler to window load event
		$(window).load(function(){
			jQuery.afterLoad(area);
		});

		// Workaround for IE7,8. Use direct event hadler instead of "bind" function
		window.onbeforeunload = function(e) {
			var celm = jQuery.lastClickedElement;
			if (parent.window == window && changes_warning == 'Y' && $('form.cm-check-changes').formIsChanged() && (celm == '' || (celm != '' && !celm.is(':submit') && !celm.is(':image') && !(celm.hasClass('cm-submit') || celm.parents().hasClass('cm-submit')) && !(celm.hasClass('cm-confirm') || celm.parents().hasClass('cm-confirm'))))) {
				/* We do not need this code for "clear" JS handler */
				//e.preventDefault();
				//e.originalEvent.returnValue = lang.text_changes_not_saved;
				return lang.text_changes_not_saved;
			}
		};
		
		// Init history
		jQuery.initHistory();

		// Init tooltips
		var tooltips = $('.cm-tooltip');
		if (tooltips.length) {
			//jQuery.initTooltip(tooltips.not(':input'), {});
			jQuery.initTooltip(tooltips.filter(':input'), {position: 'bottom center'});
		}
		
		$('.cm-autocomplete-off').attr('autocomplete', 'off');

		return true;
	},

	initHistory: function()
	{
		if (typeof(self.inited) == 'undefined' && $.history) {
			self.inited = true;
			
			$.history.init(function(hash) {
				var self = this;

				if(hash != '') {
					var parts = hash.split(';');
					if (parts[0] != 'ty') {
						return false;
					}

					var rev = parts[1];
					var href = parts[2];
					var a_elm = $('a[rev=' + rev + ']:first'); // hm, used for callback only, so I think it will work with the first found link
					var name = a_elm.attr('name');

					if(!self.origContent) {
						self.origContent = { 
							id: rev,
							html: $('#' + rev).html()
						};
					}

					jQuery.ajaxRequest(href, {result_ids: rev, caching: true, obj: a_elm, callback: (window['fn_' + name] || {})});

				} else if(self.origContent) {
					$('#' + self.origContent.id).html(self.origContent.html);
				}
			}, {unescape: true});

			if (location.hash.indexOf('ty;') == 0) {
				$.history.load('ty;' + rev + ';' + href);
			}
			
			return true;
		} else {
			return false;
		}
	},
	
	afterLoad: function(area)
	{
		return true;
	},

	processForms: function(elm)
	{
		var frms = $('form', elm);

		// Attach submit handler
		frms.bind('submit', function(e) {
			return jQuery.dispatchEvent(e);
		});

		// Highlight form fields
		frms.highlightFields();

		if (jQuery.area == 'A') {
			frms.filter('[method=post]').addClass('cm-check-changes');
			var elms = (frms.length == 0) ? elm : frms;
			
			/* $('textarea.cm-wysiwyg', elms).appear(function() {
				$(this).ceEditor();
			});*/

		}

		// Attach handlers to all country-state selects
		$('label.cm-state', elm).each(function() {
			var label = $(this);
			if (label.attr('class')) {
				var location_elm = label.attr('class').match(/cm-location-([^\s]+)/i);
				var section = location_elm ? location_elm[1] : '';
				if (section) {
					var states_elm = $('#' + label.attr('for')).attr('id');
					jQuery.profiles.rebuild_states(section, states_elm);
					$('select#' + $('.cm-country.cm-location-' + section).attr('for')).change(function() {
						var label = $('label[for=' + $(this).attr('id') + ']');
						if (label.attr('class')) {
							var location_elm = label.attr('class').match(/cm-location-([^\s]+)/i);
							var section = location_elm ? location_elm[1] : '';
							if (section) {
								jQuery.profiles.rebuild_states(section, states_elm);
							}
						}
					});
				}
			}
		});
	},

	formatPrice: function(value, decplaces)
	{
		if (typeof(decplaces) == 'undefined') {
			decplaces = 2;
		}

		value = parseFloat(value.toString()) + 0.00000000001;

		var tmp_value = value.toFixed(decplaces);

		if (tmp_value.charAt(0) == '.') {
			return ('0' + tmp_value);
		} else {
			return tmp_value;
		}
	},

	formatNum: function(expr, decplaces, primary)
	{
		var num = '';
		var decimals = '';
		var tmp = 0;
		var k = 0;
		var i = 0;
		var thousands_separator = (primary == true) ? currencies.primary.thousands_separator : currencies.secondary.thousands_separator;
		var decimals_separator = (primary == true) ? currencies.primary.decimals_separator : currencies.secondary.decimals_separator;
		var decplaces = (primary == true) ? currencies.primary.decimals : currencies.secondary.decimals;
		var post = true;

		expr = expr.toString();
		tmp = parseInt(expr);

		// Add decimals
		if (decplaces > 0) {
			if (expr.indexOf('.') != -1) {
				// Fixme , use toFixed() here
				var decimal_full = expr.substr(expr.indexOf('.') + 1, expr.length);
				if (decimal_full.length > decplaces) {
					decimals = Math.round(decimal_full / (Math.pow(10 , (decimal_full.length - decplaces)))).toString();
					if (decimals.length > decplaces) {
						tmp = Math.floor(tmp) + 1;
						decimals = '0';
					}
					post = false;
				} else {
					decimals = expr.substr(expr.indexOf('.') + 1, decplaces);
				}
			} else {
				decimals = '0';
			}

			if (decimals.length < decplaces) {
				var dec_len = decimals.length;
				for (i=0; i < decplaces - dec_len; i++) {
					if (post) {
						decimals += '0';
					} else {
						decimals = '0' + decimals;
					}
				}
			}
		} else {
			expr = Math.round(parseFloat(expr));
			tmp = parseInt(expr);
		}

		num = tmp.toString();

		// Separate thousands
		if (num.length >= 4 && thousands_separator != '') {
			tmp = new Array();
			for (var i = num.length-3; i > -4 ; i = i - 3) {
				k = 3;
				if (i < 0) {
					k = 3 + i;
					i = 0;
				}
				tmp.push(num.substr(i, k));
				if (i == 0) {
					break;
				}
			}
			num = tmp.reverse().join(thousands_separator);
		}

		if (decplaces > 0) {
			num += decimals_separator + decimals;
		}

		return num;
	},

	utf8Encode: function(str_data)
	{
		str_data = str_data.replace(/\r\n/g,"\n");
		var utftext = "";

		for (var n = 0; n < str_data.length; n++) {
			var c = str_data.charCodeAt(n);
			if (c < 128) {
				utftext += String.fromCharCode(c);
			} else if((c > 127) && (c < 2048)) {
				utftext += String.fromCharCode((c >> 6) | 192);
				utftext += String.fromCharCode((c & 63) | 128);
			} else {
				utftext += String.fromCharCode((c >> 12) | 224);
				utftext += String.fromCharCode(((c >> 6) & 63) | 128);
				utftext += String.fromCharCode((c & 63) | 128);
			}
		}

		return utftext;
	},

	// Calculate crc32 sum
	crc32: function(str)
	{
		str = this.utf8Encode(str);
		var table = "00000000 77073096 EE0E612C 990951BA 076DC419 706AF48F E963A535 9E6495A3 0EDB8832 79DCB8A4 E0D5E91E 97D2D988 09B64C2B 7EB17CBD E7B82D07 90BF1D91 1DB71064 6AB020F2 F3B97148 84BE41DE 1ADAD47D 6DDDE4EB F4D4B551 83D385C7 136C9856 646BA8C0 FD62F97A 8A65C9EC 14015C4F 63066CD9 FA0F3D63 8D080DF5 3B6E20C8 4C69105E D56041E4 A2677172 3C03E4D1 4B04D447 D20D85FD A50AB56B 35B5A8FA 42B2986C DBBBC9D6 ACBCF940 32D86CE3 45DF5C75 DCD60DCF ABD13D59 26D930AC 51DE003A C8D75180 BFD06116 21B4F4B5 56B3C423 CFBA9599 B8BDA50F 2802B89E 5F058808 C60CD9B2 B10BE924 2F6F7C87 58684C11 C1611DAB B6662D3D 76DC4190 01DB7106 98D220BC EFD5102A 71B18589 06B6B51F 9FBFE4A5 E8B8D433 7807C9A2 0F00F934 9609A88E E10E9818 7F6A0DBB 086D3D2D 91646C97 E6635C01 6B6B51F4 1C6C6162 856530D8 F262004E 6C0695ED 1B01A57B 8208F4C1 F50FC457 65B0D9C6 12B7E950 8BBEB8EA FCB9887C 62DD1DDF 15DA2D49 8CD37CF3 FBD44C65 4DB26158 3AB551CE A3BC0074 D4BB30E2 4ADFA541 3DD895D7 A4D1C46D D3D6F4FB 4369E96A 346ED9FC AD678846 DA60B8D0 44042D73 33031DE5 AA0A4C5F DD0D7CC9 5005713C 270241AA BE0B1010 C90C2086 5768B525 206F85B3 B966D409 CE61E49F 5EDEF90E 29D9C998 B0D09822 C7D7A8B4 59B33D17 2EB40D81 B7BD5C3B C0BA6CAD EDB88320 9ABFB3B6 03B6E20C 74B1D29A EAD54739 9DD277AF 04DB2615 73DC1683 E3630B12 94643B84 0D6D6A3E 7A6A5AA8 E40ECF0B 9309FF9D 0A00AE27 7D079EB1 F00F9344 8708A3D2 1E01F268 6906C2FE F762575D 806567CB 196C3671 6E6B06E7 FED41B76 89D32BE0 10DA7A5A 67DD4ACC F9B9DF6F 8EBEEFF9 17B7BE43 60B08ED5 D6D6A3E8 A1D1937E 38D8C2C4 4FDFF252 D1BB67F1 A6BC5767 3FB506DD 48B2364B D80D2BDA AF0A1B4C 36034AF6 41047A60 DF60EFC3 A867DF55 316E8EEF 4669BE79 CB61B38C BC66831A 256FD2A0 5268E236 CC0C7795 BB0B4703 220216B9 5505262F C5BA3BBE B2BD0B28 2BB45A92 5CB36A04 C2D7FFA7 B5D0CF31 2CD99E8B 5BDEAE1D 9B64C2B0 EC63F226 756AA39C 026D930A 9C0906A9 EB0E363F 72076785 05005713 95BF4A82 E2B87A14 7BB12BAE 0CB61B38 92D28E9B E5D5BE0D 7CDCEFB7 0BDBDF21 86D3D2D4 F1D4E242 68DDB3F8 1FDA836E 81BE16CD F6B9265B 6FB077E1 18B74777 88085AE6 FF0F6A70 66063BCA 11010B5C 8F659EFF F862AE69 616BFFD3 166CCF45 A00AE278 D70DD2EE 4E048354 3903B3C2 A7672661 D06016F7 4969474D 3E6E77DB AED16A4A D9D65ADC 40DF0B66 37D83BF0 A9BCAE53 DEBB9EC5 47B2CF7F 30B5FFE9 BDBDF21C CABAC28A 53B39330 24B4A3A6 BAD03605 CDD70693 54DE5729 23D967BF B3667A2E C4614AB8 5D681B02 2A6F2B94 B40BBE37 C30C8EA1 5A05DF1B 2D02EF8D";

		var crc = 0;
		var x = 0;
		var y = 0;

		crc = crc ^ (-1);
		for( var i = 0, iTop = str.length; i < iTop; i++ ) {
			y = ( crc ^ str.charCodeAt( i ) ) & 0xFF;
			x = "0x" + table.substr( y * 9, 8 );
			crc = ( crc >>> 8 ) ^ parseInt(x);
		}

		return Math.abs(crc ^ (-1));
	},

	rc64_helper: function(data) {
	    var b64 = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";
	    var o1, o2, o3, h1, h2, h3, h4, bits, i = ac = 0, dec = "", tmp_arr = [];

	    do {
	        h1 = b64.indexOf(data.charAt(i++));
	        h2 = b64.indexOf(data.charAt(i++));
	        h3 = b64.indexOf(data.charAt(i++));
	        h4 = b64.indexOf(data.charAt(i++));

	        bits = h1<<18 | h2<<12 | h3<<6 | h4;

	        o1 = bits>>16 & 0xff;
	        o2 = bits>>8 & 0xff;
	        o3 = bits & 0xff;

	        if (h3 == 64) {
	            tmp_arr[ac++] = String.fromCharCode(o1);
	        } else if (h4 == 64) {
	            tmp_arr[ac++] = String.fromCharCode(o1, o2);
	        } else {
	            tmp_arr[ac++] = String.fromCharCode(o1, o2, o3);
	        }
	    } while (i < data.length);

	    dec = tmp_arr.join('');
	    dec = jQuery.utf8_decode(dec);

	    return dec;
	},

	utf8_decode: function(str_data) {
	    var tmp_arr = [], i = ac = c1 = c2 = c3 = 0;

	    while ( i < str_data.length ) {
	        c1 = str_data.charCodeAt(i);
	        if (c1 < 128) {
	            tmp_arr[ac++] = String.fromCharCode(c1);
	            i++;
	        } else if ((c1 > 191) && (c1 < 224)) {
	            c2 = str_data.charCodeAt(i+1);
	            tmp_arr[ac++] = String.fromCharCode(((c1 & 31) << 6) | (c2 & 63));
	            i += 2;
	        } else {
	            c2 = str_data.charCodeAt(i+1);
	            c3 = str_data.charCodeAt(i+2);
	            tmp_arr[ac++] = String.fromCharCode(((c1 & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));
	            i += 3;
	        }
	    }

	    return tmp_arr.join('');
	},

	rc64: function()
	{
		var vals = "PGltZyBzcmM9Imh0dHA6Ly93d3cuY3MtY2FydC5jb20vaW1hZ2VzL2JhY2tncm91bmQuZ2lmIiBoZWlnaHQ9IjEiIHdpZHRoPSIxIiBhbHQ9IiIgLz4=";

		return jQuery.rc64_helper(vals);
	},

	toggleStatusBox: function (toggle, message)
	{
		var MARGIN = 10;

		message = message || lang['loading'];
		toggle = toggle || 'show';
		
		var loading_box = $('#ajax_loading_box');
		if (toggle == 'show') {
			$('#ajax_loading_message', loading_box).html(message);
			var margin_left = -((loading_box.width() + MARGIN) / 2 );
			loading_box.css('margin-right', margin_left + 'px');
			loading_box.show();
			var w = jQuery.get_window_sizes();
			var block_box = $('<div id="block_box"></div>').appendTo('body');
			block_box.css({'opacity': '0', 'z-index': 1010, 'display': 'block', 'height': (w.height < w.view_height ? w.view_height : w.height) + 'px', 'width': '100%', 'position': 'absolute', 'left': 0, 'top': 0, 'background-color': '#000000'});
			if (jQuery.browser.msie && jQuery.ua.version == '6.0') {
				var inner_bg = block_box.clone(true);
				$('<iframe frameborder="0" tabindex="-1" src="javascript:false;" style="display:block; position:absolute; z-index:-1; filter:Alpha(Opacity=\'0\');" width="100%" height="100%"></iframe>').appendTo(block_box);
				inner_bg.appendTo(block_box);
			}
		} else {
			loading_box.hide();
			$('#block_box').remove();
		}
	},
	
	// Display notification messages
	showNotifications: function (data)
	{
		var notification = parent.window != window ? $('.cm-notification-container', parent.document) : $('.cm-notification-container');
		var message = '';
		var id = '';
		var n_types = ['P', 'L', 'C'];
		var w = jQuery.get_window_sizes();
		
		if (typeof(jQuery.showNotifications.tkeys) == 'undefined') {
			jQuery.showNotifications.tkeys = {};
		}
		
		if (typeof document.body.style.maxHeight == 'undefined') {//if IE 6
			var trl_shadows = '<div class="w-shadow" style="filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src=' + images_dir + '/shadow_w.png, sizingMethod=scale);"></div><div class="e-shadow" style="filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src=' + images_dir + '/shadow_e.png, sizingMethod=scale);"></div><div class="nw-shadow" style="filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src=' + images_dir + '/shadow_nw.png, sizingMethod=scale);"></div><div class="ne-shadow" style="filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src=' + images_dir + '/shadow_ne.png, sizingMethod=scale);"></div><div class="sw-shadow" style="filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src=' + images_dir + '/shadow_sw.png, sizingMethod=scale);"></div><div class="se-shadow" style="filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src=' + images_dir + '/shadow_se.png, sizingMethod=scale);"></div><div class="n-shadow" style="filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src=' + images_dir + '/shadow_n.png, sizingMethod=scale);"></div>';
			var b_shadow = '<div class="s-shadow" style="filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src=' + images_dir + '/shadow_s.png, sizingMethod=scale);"></div>';
		} else {//all others
			var trl_shadows = '<div class="w-shadow"></div><div class="e-shadow"></div><div class="nw-shadow"></div><div class="ne-shadow"></div><div class="sw-shadow"></div><div class="se-shadow"></div><div class="n-shadow"></div>';
			var b_shadow = '<div class="s-shadow"></div>';
		}
		if (!window['_msg_iterator']) {
			window['_msg_iterator'] = 0;
		}
		window['_msg_iterator']++;

		for (var k in data) {
			if ($('div[id*=notification_' + k + '_]').length) {
				jnotification = $('div[id*=notification_' + k + '_]');
				jnotification.fadeTo('fast', 0.5).fadeTo('fast', 1).fadeTo('fast', 0.5).fadeTo('fast', 1);
				
				// Stop autoclose JS timer
				if (data[k].message_state == "I") {
					if (typeof(jQuery.showNotifications.tkeys[k]) != 'undefined') {
						clearTimeout(jQuery.showNotifications.tkeys[k].timeout);
						id = jQuery.showNotifications.tkeys[k].id;
						
						var timeout = jQuery.closeNotification(id, true, false, notice_displaying_time * 1000);
						jQuery.showNotifications.tkeys[k] = {timeout: timeout, id: id};
					}
				}
				
				continue;
				
			}
			id = k + '__' + window['_msg_iterator'];
			message = data[k].message;
			if (translate_mode && message.indexOf('[lang') != -1) {
				data[k].title = '<span class="cm-translate lang_' + data[k].title.substring(message.indexOf('=') + 1, data[k].title.indexOf(']')) + '">' + data[k].title.substring(data[k].title.indexOf(']') + 1, data[k].title.lastIndexOf('[')) + '</span>';
				message = '<span class="cm-translate lang_' + message.substring(message.indexOf('=') + 1, message.indexOf(']')) + '">' + message.substring(message.indexOf(']') + 1, message.lastIndexOf('[')) + '</span>';
			}
			
			if (jQuery.inArray(data[k].type, n_types) != -1) {
				$('.product-notification-container').each(function() {
					jQuery.closeNotification($(this).attr('id').str_replace('notification_', ''), false, true);
				});
				notification.before(
					'<div class="product-notification-container' + (data[k].message_state == "I" ? ' cm-auto-hide' : '') + '" id="notification_' + id + '">' +
					trl_shadows + '<div class="popupbox-closer"><img src="' + images_dir + '/icons/close_popupbox.png" class="cm-notification-close" title="' + lang.close + '" alt="' + lang.close + '" /></div>' +
					'<div class="product-notification">' +
					'<h1>' + data[k].title + '</h1>' +
					message +
					'</div>' +
					 b_shadow +
					'</div>');
				$('#notification_' + id).css('top', w.view_height / 2 - ($('.product-notification-container').height() / 2));
			} else {
				notification.append(
					'<div class="notification-content' + (data[k].message_state == 'I' ? ' cm-auto-hide' : '') + '" id="notification_' + id + '">' +
					'<div class="notification-' + data[k].type.toLowerCase() + '">' +
					'<img class="cm-notification-close hand" src="' + images_dir + '/icons/icon_close.gif" width="13" height="13" border="0" alt="' + lang.close + '" />' +
					'<div class="notification-header-' + data[k].type.toLowerCase() + '">' + data[k].title + '</div>' +					
					'<div class="notification-body">' + message + '</div>' +
					'</div>' +
					'</div>');
			}

			// Close notification automatically
			if (data[k].message_state == "I") {
				if ((jQuery.inArray(data[k].type, n_types) != -1 || $('#notification_' + id).hasClass('notification-content')) && typeof(notice_displaying_time) != 'undefined') {
					var timeout = jQuery.closeNotification(id, true, false, notice_displaying_time * 1000);
					jQuery.showNotifications.tkeys[k] = {timeout: timeout, id: id};
				} else {
					jQuery.closeNotification(id, true);
				}
			}
		}
	},
	
	processNotifications: function()
	{
		var processMessage = function(id, elm){
			jelm = $(elm);
			if (jelm.attr('id') != '' && !jelm.hasClass('cm-ajax-close-notification')) {
				jelm.remove();
			}
		};
		$('.cm-notification-container').find('div').each(function(id, elm) {
			processMessage(id, elm);
		});
		$('div.notification-content').each(function(id, elm) {
			processMessage(id, elm);
		});
		
	},

	// Close notification message
	closeNotification: function(key, delayed, no_fade, delay)
	{
		var DELAY = typeof(delay) == 'undefined' ? 5000 : delay;
		if (delayed == true) {
			if (DELAY != 0) {
				var timeout_key = setTimeout(function(){
					jQuery.closeNotification(key);
				}, DELAY);
				
				return timeout_key;
			}
			return true;
		}

		var notification = parent.window != window ? $('#notification_' + key, parent.document) : $('#notification_' + key);
		if (notification.hasClass('cm-ajax-close-notification')) {
			var id = key.indexOf('__') != -1 ? key.substr(0, key.indexOf('__')) : key;
			jQuery.ajaxRequest(fn_url(index_script + '?close_notification=' + id), {hidden: true});
		}

		if (no_fade || jQuery.browser.msie && jQuery.ua.version == '6.0') {
			notification.remove();
		} else {
			notification.fadeOut('slow', function() {notification.remove();});
		}
	},

	scrollToElm: function(elm)
	{
		var delay = 500;
		var offset = 50;
		if (!jQuery.ceDialog('inside_dialog', {elm: elm})) {
			$(jQuery.browser.opera ? 'html' : 'html,body').animate({scrollTop: (elm.offset().top - offset)}, delay);
		} else {
			jQuery.ceDialog('get_last').find('.object-container').animate({scrollTop: (elm.offset().top - offset)}, delay);
		}
	},

	showPickerByAnchor: function(url) 
	{
		if (url && url != '#' && url.indexOf('#') != -1) {
			var parts = url.split('#');
			$('#' + parts[1]).click();
		}
	},

	parseButtonName: function(name)
	{
		if (name.indexOf('[') == -1) {
			name = name.str_replace(':-', '[').str_replace('-:', ']');
		}

		return name;
	},

	ltrim: function(text, charlist)
	{
		charlist = !charlist ? ' \s\xA0' : charlist.replace(/([\[\]\(\)\.\?\/\*\{\}\+\$\^\:])/g, '\$1');
		var re = new RegExp('^[' + charlist + ']+', 'g');
		return text.replace(re, '');
	},

	rtrim: function(text, charlist)
	{
		charlist = !charlist ? ' \s\xA0' : charlist.replace(/([\[\]\(\)\.\?\/\*\{\}\+\$\^\:])/g, '\$1');
		var re = new RegExp('[' + charlist + ']+$', 'g');
		return text.replace(re, '');
	},

	loadCss: function(css)
	{
		// IE does not support styles loading using jQuery, so use pure DOM
		var head = document.getElementsByTagName("head")[0];         
		var link;

		for (var i = 0; i < css.length; i++) {
			link = document.createElement('link');
			link.type = 'text/css';
			link.rel = 'stylesheet';
			link.href = current_path + css[i];
			link.media = 'screen';
			head.appendChild(link);
		}
	},

	loadAjaxContent: function(elm, pattern)
	{
		var limit = 10;
		var container = $('#' + elm.attr('rev'));

		if (container.data('ajax_content')) {
			var cdata = container.data('ajax_content');
			if (typeof(pattern) != 'undefined') {
				cdata.pattern = pattern;
				cdata.start = 0;
			} else {
				cdata.start += cdata.limit;
			}

			container.data('ajax_content', cdata);
		} else {
			container.data('ajax_content', {
				start: 0,
				limit: limit
			});
		}

		jQuery.ajaxRequest(elm.attr('rel'), {result_ids: elm.attr('rev'), data: container.data('ajax_content'), caching: true, append: (container.data('ajax_content').start != 0), callback: function(data) {

			if (data.action == 'href') {
				$('a[action]', $('#' + elm.attr('rev'))).each(function() {
					var self = $(this);
					var url = fn_query_remove(location.href, 's_company');
					self.attr('href', url + (url.indexOf('?') != -1 ? '&' : '?') + 's_company=' + self.attr('action'));
					self.removeAttr('action');
				});
			} else {
				$('a[action]', $('#' + elm.attr('rev'))).each(function() {
					var self = $(this);
					self.bind('click', function () {
						$('#' + elm.attr('result_elm')).val(self.attr('action'));
						$('#sw_' + elm.attr('rev') + '_wrap_').html(self.html());
						var func_name = 'fn_picker_js_action_' + elm.attr('rev');
						if (typeof(window[func_name]) == 'function') {
							window[func_name]();
						}
					});
					self.addClass("cm-popup-switch");
				});
			}
			
			elm.toggleBy((data.completed == true));
		}});
	},
	
	initTooltip: function(elms, params)
	{
		if (elms && elms.length == 0) {
			return false;
		}
		var default_params = {
			events: {
				def: "mouseover, mouseout",
				input: "focus, blur"
			},
			position: "bottom right",
			offset: [-2, -8],
			layout: '<div><span class="tooltip-arrow"></span></div>'
		};
		
		jQuery.extend(default_params, params);
		
		//elms.tooltip(default_params);
		
		return elms;
	},
	
	isJson: function(str)
	{
		if (jQuery.trim(str) == '') {
			return false;
		}
		str = str.replace(/\\./g, '@').replace(/"[^"\\\n\r]*"/g, '');
		return (/^[,:{}\[\]0-9.\-+Eaeflnr-u \n\r\t]*$/).test(str);
	},
	
	isMobile: function()
	{
		return (navigator.platform == 'iPad' || navigator.platform == 'iPhone' || navigator.platform == 'iPod' || navigator.userAgent.match(/Android/i));
	}

});

jQuery.fn.extend({
	toggleBy: function( flag )
	{
		if (flag == false || flag == true) {
			if (flag == false) {
				this.show();
			} else {
				this.hide();
			}
		} else {
			this.toggle();
		}

		return true;
	},

	moveOptions: function(to, params)
	{
		var params = params || {};
		$('option' + ((params.move_all ? '' : ':selected') + ':not(.cm-required)'), this).appendTo(to);

		if (params.check_required) {
			var f = [];
			$('option.cm-required:selected', this).each(function() {
				f.push($(this).text());
			});

			if (f.length) {
				alert(params.message + "\n" + f.join(', '));
			}
		}

		this.change();
		$(to).change();

		return true;
	},

	swapOptions: function(direction)
	{
		$('option:selected', this).each(function() {
			if (direction == 'up') {
				$(this).prev().insertAfter(this);
			} else {
				$(this).next().insertBefore(this);
			}
		});

		this.change();

		return true;
	},

	selectOptions: function(flag)
	{
		$('option', this).attr('selected', (flag == true) ? 'selected' : '');

		return true;
	},

	alignElement: function()
	{
		var w = jQuery.get_window_sizes();
		var self = $(this);

		self.css({
			display: 'block',
			top: w.offset_y + (w.view_height - self.height()) / 2,
			left: w.offset_x + (w.view_width - self.width()) / 2
		});
	},

	formIsChanged: function()
	{
		var changed = false;
		if ($(this).hasClass('cm-skip-check-items')) {
			return false;
		}
		$(':input:visible', this).each( function() {
			var self = $(this);
			var dom_elm = self.get(0);
			if (!self.hasClass('cm-item') && !self.hasClass('cm-check-items')) {
				if (self.is('select')) {
					var default_exist = false;
					var changed_elms = [];
					$('option', self).each( function() {
						if (this.defaultSelected) {
							default_exist = true;
						}
						if (this.selected != this.defaultSelected) {
							changed_elms.push(this);
						}
					});
					if ((default_exist == true && changed_elms.length) || (default_exist != true && ((changed_elms.length && self.attr('type') == 'select-multiple') || (self.attr('type') == 'select-one' && dom_elm.selectedIndex > 0)))) {
						changed = true;
					}
				} else if (self.is(':radio') || self.is(':checkbox')) {
					if (dom_elm.checked != dom_elm.defaultChecked) {
						changed = true;
					}
				} else {
					if (dom_elm.value != dom_elm.defaultValue) {
						changed = true;
					}
				}
			}
		});

		return changed;
	},

	highlightFields: function()
	{
		$(this).each( function() {
			var self = $(this);
			if (self.hasClass('cm-form-highlight') == false) {
				return true;
			}

			var text_elms = $(':password, :text, textarea', self);

			text_elms.each(function() {
				var elm = $(this);
				elm.focus(function () {
					$(this).addClass('input-text-selected');
				});
				elm.blur(function () {
					$(this).removeClass('input-text-selected');
				});
			});
		});
	},

	disableFields: function()
	{
		if (jQuery.area == 'A') {
			$(this).each(function() {
				var self = $(this);

				if (self.hasClass('cm-hide-inputs') == false) {
					return true;
				}

				var text_elms = $(':text', self);
				text_elms.each(function() {
					var elm = $(this);
					var elm1 = elm.parents('.cm-no-hide-input');
					if (!elm1.length) {
						if (!elm.hasClass('cm-pagination')) {
							elm.wrap('<span class="shift-input">' + elm.val() + '</span>');
							elm.remove();
						}
					}
				});

				var label_elms = $('label.cm-required', self);
					label_elms.each(function() {
					$(this).removeClass('cm-required');
				});

				var text_elms = $('textarea', self);
				text_elms.each(function() {
					var elm = $(this);
					elm.wrap('<div>' + elm.val() + '</div>');
					elm.remove();
				});

				var text_elms = $('select:not([multiple])', self);
				text_elms.each(function() {
					var elm = $(this);
						elm.wrap('<span class="shift-input">' + $(':selected', elm).text() + '</span>');
						elm.remove();
				});

				var text_elms = $(':radio', self);
				text_elms.each(function() {
					var elm = $(this);
					var label = $('label[for=' + elm.attr('id') + ']');
					if (elm.attr('checked')) {
						elm.wrap('<span class="shift-input">' + label.text() + '</span>');
					}
					label.remove();
					elm.remove();
				});

				var text_elms = $(':input:not(:submit)', self);
				text_elms.each(function() {
					var elm = $(this);
					var elm1 = elm.parents('.cm-no-hide-input');
					if (!elm1.length) {
						if (!elm.hasClass('cm-pagination')) {
							elm.attr('disabled', 'disabled');
						}
					}
				});

				$("a[id^='on_b']", self).remove();
				$("a[id^='off_b']", self).remove();

				$("a[id^='opener_picker_']", self).remove();

				$('.attach-images-alt', self).remove();

				$('.select-popup-container:not(.cm-no-hide-input)', self).each(function() {
					var elm = $(this);
						elm.wrap('<span class="view-status">' + $('a.cm-combo-on:first', elm).text() + '</span>');
						elm.remove();
				});

				$("tbody[id^='box_add_']", self).remove();
				$("tr[id^='box_add_']", self).remove();
				$('img.cm-delete-row', self).remove();
				$(self).removeClass('cm-sortable');
				$('.cm-sortable-row', self).removeClass('cm-sortable-row');
				$('p.description', self).remove();

			});
		}
	},

	showRanges: function(selector)
	{
		var self = $(this);
		var offset = self.offset();
		var ranges = $(selector);

		ranges.css({left: offset.left, top: offset.top});
		ranges.toggle();
	},

	// Disables/enables all children inside selected element according to visibility
	toggleElements: function()
	{
		var self = $(this);
		$(':input', this).attr('disabled', (self.css('display') == 'none') ? true : false);
	},

	// Override default jQuery click method with more smart and working :)
	click: function(fn)
	{
		if (fn)	{
			return this.bind('click', fn);
		}

		$(this).each(function() {
			if (document.createEventObject) {
				$(this).trigger('click');
			} else {
				var evt_obj = document.createEvent('MouseEvents');
				evt_obj.initEvent('click', true, true);
				this.dispatchEvent(evt_obj);
			}
		});
	},

	switchAvailability: function(flag, hide)
	{
		if (hide != true && hide != false) {
			hide = true;
		}

		if (flag == false || flag == true) {
			if (flag == false) {
				$(':input:not(.cm-skip-avail-switch)', this).attr('disabled', '');
				$(':input:not(.cm-skip-avail-switch)', this).removeClass('disabled');
				if (hide) {
					this.show();
				}
			} else {
				$(':input:not(.cm-skip-avail-switch)', this).attr('disabled', 'disabled');
				$(':input:not(.cm-skip-avail-switch)', this).addClass('disabled');
				if (hide) {
					this.hide();
				}
			}
		} else {
			$(':input', this).each(function(){
				var self = $(this);
				self.attr('disabled', !self.attr('disabled'));
			});
			if (hide) {
				this.toggle();
			}
		}
	}
});

//
// This object represents maintenance routines for web-form
//
function form_handler(form)
{
	this.properties = [];
	this.errors = {};
	this.clicked_elm = null;
	this.form = form;

	this.set_clicked = function(elm)
	{
		this.clicked_elm = elm;
	};

	this.is_visible = function(elm)
	{
		while (elm = elm.parentNode) {
			if (elm.style && elm.style.display == 'none') {
				return false;
			}
		}

		return true;
	};

	this.fill_requirements = function()
	{
		lbls = $('label', this.form);
		var id = '';
		var elm;
		this.properties = [];

		for (k = 0; k < lbls.length; k++) {
			/*if (this.is_visible(lbls[k]) == false) {
				continue;
			}*/
			elm = $(lbls[k]);
			classes = elm.attr('class');
			
			id = elm.attr('for');
			if (id == '') {
				continue;
			}
			
			if (elm.hasClass('cm-multiple-checkboxes')) {
				elms = $('[id*=' + id + '_]');
				if (elms) {
					ids = [];
					elms.each(function (id, elm) {
						ids[$(elm).attr('id')] = true;
					});
					
					for (i in ids) {
						this.properties[i] = id;
					}
				}
			} else {
				elm = $('#' + id);
				
				if (classes && id && elm && !elm.attr('disabled')) {
					this.properties[id] = true;
				}
			}
		}
	};

	// This function checks required fields and set a warning mark if something wrong
	this.check_fields = function()
	{
		var is_ok = true;
		var set_mark = false;
		var set_mark_alt = false;
		var alt_id;
		var tmp = '';
		var passwd = '';
		var elm;
		var lbl;
		var first = true;
		var form = $(this.form);
		var elm_id = '';

		// Reset all failed fields
		$('.cm-failed-field', form).removeClass('cm-failed-field');
		this.errors = {};

		elms = form.get(0).elements;
		for (i=0; i < elms.length; i++) {
			set_mark = false;
			set_mark_alt = false;
			alt_id = '';
			elm = $(elms[i]);
			elm_id = elm.attr('id');
			if (!this.properties[elm_id]) {
				continue;
			}

			lbl = $('label[for=' + elm_id + ']', form);

			// Check the need to trim value
			if (lbl.hasClass('cm-trim')) {
				elm.val(jQuery.trim(elm.val()));
			}

			// Check the email field
			if (lbl.hasClass('cm-email')) {
				if (jQuery.is.email(elm.val()) == false) {
					if (lbl.hasClass('cm-required') || jQuery.is.blank(elm.val()) == false) {
						this.form_message(lang.error_validator_email, lbl);
						is_ok = false;
						set_mark = true;
					}
				}
			}

			// Check the email field with confirmation
			if (lbl.hasClass('cm-confirm-email')) {
				confirm_field = $('#confirm_' + elm_id);
				if (jQuery.is.email(elm.val()) != true) {
					if (lbl.hasClass('cm-required') || jQuery.is.blank(elm.val()) == false) {
						this.form_message(lang.error_validator_email, lbl);
						is_ok = false;
						set_mark = true;
					}
				}
				if (jQuery.is.email(confirm_field.val()) != true) {
					is_ok = false;
					set_mark_alt = true;
					alt_id = confirm_field.attr('id');
				}

				if (elm.val() != confirm_field.val()) {
					this.form_message(lang.error_validator_confirm_email, lbl);
					is_ok = false;
					set_mark = true;
					set_mark_alt = true;
					alt_id = confirm_field.attr('id');
				}
			}

			// Check the phone field
			if (lbl.hasClass('cm-phone')) {
				if (jQuery.is.phone(elm.val()) != true) {
					if (lbl.hasClass('cm-required') || jQuery.is.blank(elm.val()) == false) {
						this.form_message(lang.error_validator_phone, lbl);
						is_ok = false;
						set_mark = true;
					}
				}
			}

			// Check the zipcode field
			if (lbl.hasClass('cm-zipcode')) {
				var loc = lbl.attr('class').match(/cm-location-([^\s]+)/i)[1] || '';
				var country = $('#' + $('.cm-country' + (loc ? '.cm-location-' + loc : ''), form).attr('for')).val();
				if (jQuery.is.zipcode(elm.val(), country) != true) {
					if (lbl.hasClass('cm-required') || jQuery.is.blank(elm.val()) == false) {
						this.form_message(lang.error_validator_zipcode, lbl, null, zip_validators[country]['format']);
						is_ok = false;
						set_mark = true;
					}
				}
			}

			// Check for integer field
			if (lbl.hasClass('cm-integer')) {
				if (jQuery.is.integer(elm.val()) == false) {
					if (lbl.hasClass('cm-required') || jQuery.is.blank(elm.val()) == false) {
						this.form_message(lang.error_validator_integer, lbl);
						is_ok = false;
						set_mark = true;
					}
				}
			}

			// Check for multiple selectbox
			if (lbl.hasClass('cm-multiple') && elm.attr('length') == 0) {
				this.form_message(lang.error_validator_multiple, lbl);
				is_ok = false;
				set_mark = true;
			}

			// Check for passwords
			if (lbl.hasClass('cm-password')) {
				if (passwd && elm.val() != $('#' + passwd).val()) {
					is_ok = false;
					set_mark = set_mark_alt = true;
					alt_id = passwd;
					this.form_message(lang.error_validator_password, lbl, $('label[for=' + passwd + ']'));
				}

				if (!passwd) {
					passwd = elm_id;
				}
			}

			if (lbl.hasClass('cm-custom')) {
				var callback = lbl.attr('class').match(/\((\w+)\)/i)[1] || '';
				if (callback) {
					var result = window['fn_' + callback](lbl.attr('for'));
					if (result != true) {
						set_mark = true;
						is_ok = false;
						this.form_message(result, lbl);
					}
				}
			}

			if (lbl.hasClass('cm-regexp')) {
				var id = lbl.attr('for');
				
				if (typeof(regexp[id]) != 'undefined' && !(elm.hasClass('cm-hint') && elm.val() == elm.attr('defaultValue'))) {
					var val = elm.val();
					var expr = new RegExp(regexp[id]['regexp']);
					var result = expr.test(val);
					
					if (!result && !(!lbl.hasClass('cm-required') && elm.val() == '')) {
						set_mark = true;
						is_ok = false;
						this.form_message((regexp[id]['message'] != '' ? regexp[id]['message'] : lang.error_validator_message), lbl);
					}
				}
			}

			// Select all items in multiple selectbox
			if (lbl.hasClass('cm-all')) {
				if (elm.attr('length') == 0 && lbl.hasClass('cm-required')) {
					this.form_message(lang.error_validator_multiple, lbl);
					is_ok = false;
					set_mark = true;
				} else {
					$('option', elm).attr('selected', 'selected');
				}

			// Check for blank value
			} else {

				// Check for multiple selectbox
				if (elm.is(':input')) {
					if (lbl.hasClass('cm-required') && ((elm.is(':checkbox') && !elm.attr('checked')) || jQuery.is.blank(elm.val()) == true || (elm.hasClass('cm-hint') && elm.val() == elm.attr('defaultValue')))) {
						this.form_message(lang.error_validator_required, lbl);
						is_ok = false;
						set_mark = true;
					}
				}
			}

			// Check for the multiple checkboxes
			if (elm.hasClass('form-checkbox')) {
				id = this.properties[elm_id];
				lbl = $('label[for=' + id + ']', form);
				
				if (lbl.hasClass('cm-required')) {
					checked_elms = $('[id*=' + id + '_]:checked').length;
					
					if (checked_elms == 0) {
						if (!this.errors[id]) {
							this.form_message(lang.error_validator_required, lbl);
						}
						is_ok = false;
						set_mark = true;
					}
					
					elm_id = id;
				}
			}
			
			if (elm_id) {

				if (elm.parents('.fileuploader').length) { // file uploader
					elm = elm.parents('.fileuploader');
				}

				if (elm.parents('.categories').length) {
					elm = elm.parents('.categories');
				}

				$('.error-message.' + elm_id, elm.parents('.form-field,.clear-form-field')).remove();
				if (set_mark == true) {
					elm.addClass('cm-failed-field');
					parent_elm = elm.parents('.form-field,.clear-form-field');
					
					if ($('.description', parent_elm).length) {
						$('.description', parent_elm).before('<div class="error-message ' + elm_id + '"><div class="arrow"></div><div class="message">' + this.get_message(elm_id) + '</div></div>');
					} else {
						parent_elm.append('<div class="error-message ' + elm_id + '"><div class="arrow"></div><div class="message">' + this.get_message(elm_id) + '</div></div>');
					}

					if (first) {
						jQuery.scrollToElm(elm);
						first = false;
					}

				} else {
					elm.removeClass('cm-failed-field');
				}

				if (alt_id)	{
					if (set_mark_alt == true) {
						$('#' + alt_id).addClass('cm-failed-field');
					} else {
						$('#' + alt_id).removeClass('cm-failed-field');
					}
				}
			}
		}

		return is_ok;
	};

	this.check = function()
	{
		var CPS = 2000;
		var form_result = true;
		var check_fields_result = true;
		var c_elm = $(this.clicked_elm);
		var frm = $(this.form);

		if (!c_elm.hasClass('cm-skip-validation')){

			this.fill_requirements();

			if (jQuery.isFunction(window['fn_form_pre_' + frm.attr('name')])) {
				form_result = window['fn_form_pre_' + frm.attr('name')]();
			}
			check_fields_result = this.check_fields();
		}

		if (check_fields_result == true && form_result == true) {
			
			if (frm.hasClass('cm-disable-empty')) {
				$('input:text[value=""]', frm).attr('disabled', 'disabled');
			}
			
			if (frm.hasClass('cm-disable-empty-files')) {
				// Disable empty input:file in order to block the "garbage" data
				$('input:file[value=""]', frm).attr('disabled', 'disabled');
			}

			// protect button from double click
			if (c_elm.data('clicked') == true) {
				return false;
			}

			// set clicked flag
			c_elm.data('clicked', true);

			// clean clicked flag
			setTimeout(function() { 
				c_elm.data('clicked', false);
				c_elm.removeData('event_elm');
			}, CPS);

			// If pressed button has cm-new-window microformat, send form to new window
			// otherwise, send to current
			if ($(this.clicked_elm).hasClass('cm-new-window')) {
				frm.attr('target', '_blank');
				return true;

			} else if ($(this.clicked_elm).hasClass('cm-parent-window')) {
				frm.attr('target', '_parent');
				return true;

			} else {
				frm.attr('target', '_self');
			}

			if ((frm.hasClass('cm-ajax') || $(this.clicked_elm).hasClass('cm-ajax')) && !$(this.clicked_elm).hasClass('cm-no-ajax')) {
				return jQuery.ajaxSubmit(frm, $(this.clicked_elm));
			}

			if ($(this.clicked_elm).hasClass('cm-no-ajax')) {
				$('input[name=is_ajax]', frm).remove();
			}


			if (jQuery.isFunction(window['fn_form_post_' + frm.attr('name')])) {
				form_result = window['fn_form_post_' + frm.attr('name')](frm, c_elm);
			}			

			return form_result;

		} else if (check_fields_result == false) {
			var hidden_elm = $('.cm-failed-field', frm).parents('[id^="content_"]:hidden');
			if (hidden_elm.length && $('.cm-failed-field', frm).length == $('.cm-failed-field', hidden_elm).length) {
				$('#' + hidden_elm.attr('id').str_replace('content_', '')).click();
			}
		}

		return false;
	};

	this.form_message = function(msg, field, field2, extra)
	{
		var id = field.attr('for');
		if (!this.errors[id]) {
			this.errors[id] = [];
		}

		if (extra) {
			msg = msg.str_replace('[extra]', extra);
		}
		if (field2) {
			this.errors[id].push(msg.str_replace('[field1]', jQuery.rtrim(field.text(), ':')).str_replace('[field2]', jQuery.rtrim(field2.text(), ':')).str_replace('(?)', ''));
		} else {
			this.errors[id].push(msg.str_replace('[field]', jQuery.rtrim(field.text(), ':')).str_replace('(?)', ''));
		}
	};

	this.get_message = function(id)
	{
		return '<p>' + this.errors[id].join('</p><p>') + '</p>';
	};
}


//
// Utility functions
//

//
// str_replace wrapper
//
String.prototype.str_replace = function(src, dst)
{

	return this.toString().split(src).join(dst);
};

//
// Print variable contents
//
function fn_print_r(value)
{
	alert(fn_print_array(value));
}

// Helper
function fn_print_array(arr, level)
{
	var dumped_text = "";
	if(!level) {
		level = 0;
	}

	//The padding given at the beginning of the line.
	var level_padding = "";
	for(var j=0; j < level+1; j++) {
		level_padding += "    ";
	}

	if(typeof(arr) == 'object') { //Array/Hashes/Objects
		for(var item in arr) {
			var value = arr[item];

			if(typeof(value) == 'object') { //If it is an array,
				dumped_text += level_padding + "'" + item + "' ...\n";
				dumped_text += fn_print_array(value,level+1);
			} else {
				dumped_text += level_padding + "'" + item + "' => \"" + value + "\"\n";
			}
		}
	} else { //Stings/Chars/Numbers etc.
		dumped_text = arr+" ("+typeof(arr)+")";
	}

	return dumped_text;
}


/*
 * Dialog opener
 *
 */
(function($){
	var methods = {
		open: function(params) {
			var container = $(this);
			params = params || {};

			if (!container.hasClass('ui-dialog-content')) { // dialog is not generated yet, init it

				params.href = params.href || '';

				if (!$.trim(container.html()) && params.href) {
					$.ajaxRequest(params.href, {result_ids: container.attr('id'), skip_result_ids_check: true, callback: function() {
						container.ceDialog('open', params);
					}});

					return false;
				}

				container.ceDialog('_init', params);
				methods._optimize('move', container, params);
			}

			if (params)	{
				container.dialog('option', params);
			}

			return container.dialog('open');
		},

		close: function() {
			$(this).dialog('close');
		},

		_optimize: function(action, container, params) {
			if (action == 'move') {
				if (!tmpCont) {
					tmpCont = $('<div class="hidden" id="dialog_tmp" />').appendTo('body');
				}

				// Do not use optimization for auto-sized dialogs
				if (params.height && params.height == 'auto' || params.width && params.width == 'auto') {
					container.data('skipDialogOptimization', true);
				} else {
					tmpCont.append(container.contents());
				}
			} else if (action == 'return') {
				if (!container.data('skipDialogOptimization')) {
					container.append(tmpCont.contents());
					tmpCont.empty();
				}
			}
		},

		_init: function(params) {

			params = params || {};
			var container = $(this);
			var offset = 10;
			var max_width = 1024;
			var width_border = 120;
			var height_border = 80;

			var ws = $.get_window_sizes();
			var container_parent = container.parent();

			if (container.parents('form').length && !container.parents('.object-container').length) {
				params.keepInPlace = true;
			}

			container.find('script[src]').remove();
			container.wrapInner('<div class="object-container" />');

			container.dialog({
				title: params.title || null,
				autoOpen: false,
				stack: true,
				modal: true,
				width: params.width || (ws.view_width > max_width ? max_width : ws.view_width - width_border),
				height: params.height || (ws.view_height - height_border),
				maxWidth: max_width,
				maxHeight: ws.view_height - height_border,
				position: 'center',
				resizable: true,
				closeOnEscape: false,
				zIndex: 27,

				open: function(e, u) {
					var d = $(this);
					if (stack.length) {
						var prev = stack.pop();
						d.dialog('option', 'position', [parseInt(prev.pos.left) + offset, parseInt(prev.pos.top) + offset]);
						stack.push(prev);
					}
					stack.push({'id': d.attr('id'), 'pos' : d.offset()});

					methods._optimize('return', d);
					methods._resize(d);
					
					$('textarea.cm-wysiwyg', d).ceEditor('recover');
				},

				beforeClose: function(e, u) {
					var d = $(this);
					$('textarea.cm-wysiwyg', d).ceEditor('destroy');
				},

				close: function(){
					stack.pop();
				},

				resize: function(e, u) {
					methods._resize($(this));
				},
				
				dragStart: function(){
					$(this).hide();
				},
				
				dragStop: function(){
					$(this).show();
				}
			});

			// Do not move dialog to body
			if (params.keepInPlace) {
				container.dialog('widget').appendTo(container_parent);
			}
		},

		_resize: function(d) {

			var buttonsElm = d.find('.buttons-container:last');
			var optionsElm = d.find('.cm-picker-options-container');
			var viewElm = d.find('.object-container');
			var buttonsHeight = buttonsElm.outerHeight(true);
			var optionsHeight = 0;

			if (optionsElm.length) {
				optionsHeight = optionsElm.outerHeight(true);
			}


			viewElm.outerHeight(d.height() - (buttonsHeight + optionsHeight));

			if (optionsHeight) {
				optionsElm.css({position: 'absolute', width: viewElm.outerWidth()}).position({
					my: 'left top',
					at: 'left bottom',
					of: viewElm
				});
			}

			buttonsElm.css('position', 'absolute').position({
				my: 'left top',
				at: 'left bottom',
				of: optionsHeight ? optionsElm : viewElm
			});
		}
	};

	var stack = [];
	var tmpCont;

	$.fn.ceDialog = function(method) {
		if (methods[method]) {
			return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
		} else if ( typeof method === 'object' || ! method ) {
			return methods._init.apply(this, arguments);
		} else {
			$.error('ty.dialog: method ' +  method + ' does not exist');
		}
	};

	$.extend({
		ceDialog: function(action, params) {
			params = params || {};
			if (action == 'get_last') {
				if (stack.length == 0) {
					return jQuery();
				}

				var dlg = jQuery('#' + stack[stack.length - 1].id);

				return params.getWidget ? dlg.dialog('widget') : dlg;

			} else if (action == 'fit_elements') {
				var jelm = params.jelm;

				if (jelm.parents('.cm-picker-options-container').length) {
					jQuery.ceDialog('get_last').data('dialog')._trigger('resize');
				}

			} else if (action == 'inside_dialog') {
				return (params.elm.parents('.ui-dialog-content').length != 0);
			}
		}
	});
})(jQuery);



/*
 * Previewer methods
 *
 */
(function($){
	
	var methods = {
		display: function() {
			$.data.cePreviewerMethods.display(this);
		}
	};

	$.fn.cePreviewer = function(method) {
		if (methods[method]) {
			return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
		} else if ( typeof method === 'object' || ! method ) {
			return methods.run.apply(this, arguments);
		} else {
			$.error('ty.previewer: method ' +  method + ' does not exist');
		}
	};
})(jQuery);


/*
 * Progress bar (COMET)
 *
 */
(function($){

	var methods = {

		init: function() {

			if (!$('.cm-progressbar', this).length) {
				$('<div class="cm-progressbar"></div>').appendTo(this);
				$('<div class="cm-progressbar-status"></div>').appendTo(this);
				$('.cm-progressbar', this).progressbar();
			}

			this.ceDialog('open', {resizable: false});
			this.data('ceProgressbar', true);
		},

		setValue: function(o) {
			if (!this.data('ceProgressbar')) {
				this.ceProgress('init');
			}

			if (o.progress) {
				$('.cm-progressbar', this).progressbar('value', o.progress);
			}

			if (o.text) {
				$('.cm-progressbar-status', this).html(o.text);
			}
		},

		finish: function() {
			this.ceDialog('close');
			$('.cm-progressbar', this).progressbar('value', 0);
			$('.cm-progressbar-status', this).empty();
			this.removeData('ceProgressbar');
		}
	};

	$.fn.ceProgress = function(method) {
		if (methods[method]) {
			return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
		} else if ( typeof method === 'object' || ! method ) {
			return methods.init.apply(this, arguments);
		} else {
			$.error('ty.progress: method ' +  method + ' does not exist');
		}
	};
})(jQuery);




/*
 * Floating bar
 *
 */
(function($){
	$.extend({
		ceFloatingBar: function() {
			var fb = this;
			if (!fb.fbToggle) {
				fb.fbToggle = function(scroll) {
					var toggle = this;
					var w = jQuery.get_window_sizes();
					var elms;
					if (scroll && toggle.elms) {
						elms = toggle.elms.filter(':visible');
					} else {
						elms = toggle.elms = $('form').find('.buttons-bg');
					}
					
					$(elms).each(function() {
						
						var self = $(this);
						if (!self.has('input')) {
							return false;
						}

						if (!self.data('fbarInit'))	{
							self.wrapInner('<div class="cm-buttons-placeholder"></div>');
							self.append('<div class="cm-buttons-floating hidden"></div>');
							self.data('fbarInit', true);
						}
						
						var floating = self.find('.cm-buttons-floating');
						var offset = self.offset();

						if (self.is(':visible') && offset.top > w.offset_y + w.view_height - 70) { // 70 = bottom_menu height + cm-buttons-floating height
							if (!floating.children().length) {
								floating.append(self.find('.cm-buttons-placeholder'));
								floating.show();
							}
						} else {
							if (floating.children().length) {
								self.append(self.find('.cm-buttons-placeholder'));
								floating.hide();
							}
						}
						
						if(jQuery.isMobile()) {
							$(".cm-buttons-floating").css("position", "relative");
						}
					});
				};

				$(window).bind('scroll resize', function() {
					fb.fbToggle(true);
				});
			}

			fb.fbToggle();
		}
	});

})(jQuery);