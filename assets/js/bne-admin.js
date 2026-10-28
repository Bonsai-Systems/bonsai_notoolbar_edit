/**
 * Bonsai No Toolbar Edit — settings screen.
 *
 * Turns the hover colour field into the core WordPress colour picker, which
 * accepts typed or pasted hex values (e.g. #E95A3A) as well as the swatch.
 */
(function ($) {
	$(function () {
		$('.bne-color-field').wpColorPicker();
	});
})(jQuery);
