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
			removeLogoBtn: ".js-remove-brandfetch",
			logo: "#fields-brandfetch-logo img",
			emptyView: "#fields-brandfetch-empty",
			filledView: "#fields-brandfetch-filled",
			imageIdField: "#fields-brandfetcher-img-id",
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
			console.log(this.options);
			// Initial Callers
			$(this.options.removeLogoBtn).on("click", this.removeLogo);
			$(this.options.getLogoBtn).on("click", this.getLogo);

			// console.log('HIT', self.options.url, Craft.getActionUrl('brandfetch/bfcontroller/fetch-logo'))

			/* -- _this.options gives us access to the $jsonVars that our FieldType passed down to us */
		},
		removeLogo: function() {
			$(self.options.imageIdField).val("");

			$(self.options.logo).remove();
			$(self.options.filledView).hide();

			$(self.options.emptyView).show();
		},

		getLogo: function() {
			self.removeErrors();
			var fetchUrl = $("#fields-brandfetch-url").val();
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
							$("#fields-brandfetch-url").val("");
							// set image ID Value
							$(self.options.imageIdField).val(
								response.brandfetch.result.id
							);
							// create image
							var $html = $("<img/>");
							$html.attr("src", response.brandfetch.result.url);
							// show files
							$(self.options.filledView)
								.show()
								.prepend($html);
							$(self.options.emptyView).hide();

							Craft.cp.displayNotice(response.brandfetch.message);
						} else {
							Craft.cp.displayError(response.message);
						}
					}
				}, this)
			);
		},
		validateUrl: function(url) {
			return /^(https?|s?ftp):\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i.test(
				url
			);
		},
		setFieldErrors: function() {
			$("#fields-brandfetch-url").addClass("error");
			$("#fields-brandfetch-url-label").addClass("error");
		},
		removeErrors() {
			$("#fields-brandfetch-url").removeClass("error");
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
