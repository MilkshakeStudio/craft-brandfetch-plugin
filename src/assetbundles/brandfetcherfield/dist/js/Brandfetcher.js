/**
 * Brandfetch plugin for Craft CMS
 *
 * Brandfetcher Field JS
 *
 * @author    Milkshake Studio
 * @copyright Copyright (c) 2020 Milkshake Studio
 * @link      https://milkshake.studio
 * @package   Brandfetch
 * @since     0.0.1BrandfetchBrandfetcher
 */

(function($, window, document, undefined) {
	var pluginName = "BrandfetchBrandfetcher",
		self,
		defaults = {
			actionUrl: "brandfetch/bfcontroller/fetch-logo",
			getLogoBtn: ".js-get-brandfetch",
			logo: "#fields-brandfetch-logo img",
			emptyView: "#fields-brandfetch-empty",
			filledView: "#fields-brandfetch-filled",
			imageIdField: "#fields-brandfetcher-img-id",
			urlInput : '#fields-brandfetch-url'
		};

	// Plugin constructor
	function Plugin(element, options) {
		this.element = element;

		this.options = $.extend({}, defaults, options);

		this._defaults = defaults;
		this._name = pluginName;
		self = this;
		this.init();
	}

	Plugin.prototype = {
		init: function(id) {
			// Initial Callers
			$(this.options.getLogoBtn).on("click", this.getLogo);
			
			// watch for enter
			$(this.options.urlInput).on('focus', ()=>{
				$(document).keypress(function(e) {
					if(e.which == 13) {
						e.preventDefault();
						self.getLogo();
					}
				});
			})
			// remove action
			$(this.options.urlInput).on('blur', ()=>{
				$(this.options.urlInput).off('focus')
			});

		},
		
		// API Call to get the logo
		getLogo: function() {
			self.removeErrors();
			var fetchUrl = $(this.options.urlInput).val();
			if (
				fetchUrl == null ||
				fetchUrl == "" ||
				self.validateUrl(fetchUrl) == false
			) {
				Craft.cp.displayError("Please enter a valid URL.");
				self.setFieldErrors();
				return false;
			}

			//call to action
			Craft.postActionRequest(
				self.options.actionUrl,
				{ url: fetchUrl },
				$.proxy(function(response, textStatus) {
					console.log(textStatus, response);
					if (textStatus === "success") {
						if (response.success) {
							//reset value
							$(self.options.urlInput).val("");
							
							$(self.options.imageIdField).html(response.brandfetch.html)
							
							new Craft.BaseElementSelectInput({
								id: 'brandfetcher-img-id',
								fieldId:'brandfetcher-img-upload',
								name: 'fields[logo]',
								elementType: self.options.elementType,
								viewMode: 'large',
								limit: 1,
								fieldId:  self.options.id,
								elements:([response.brandfetch.result.id]),
								modalSettings: {hideSidebar: true}
							});
							

							Craft.cp.displayNotice(response.brandfetch.message);
						} else {
							Craft.cp.displayError(response.message);
						}
					}
				}, this)
			);
		},
		// Basic URL validation
		validateUrl: function(url) {
			return /^(https?|s?ftp):\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i.test(
				url
			);
		},
		setFieldErrors: function() {
			$(this.options.urlInput).addClass("error");
			$("#fields-brandfetch-url-label").addClass("error");
		},
		removeErrors() {
			$(this.options.urlInput).removeClass("error");
			$("#fields-brandfetch-url-label").removeClass("error");
		},
		
	};

	// A really lightweight plugin wrapper around the constructor,
	// preventing against multiple instantiations
	$.fn[pluginName] = function(options) {
		return this.each(function() {
			if (!$.data(this, "plugin_" + pluginName)) {
				$.data(this, "plugin_" + pluginName, new Plugin(this, options));
			}
		});
	};
})(jQuery, window, document);
