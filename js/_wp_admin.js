/**
 * jQuery for _WP plugin
 * @since 3.6
 * @copyright lightningspirit
 * @license GPLv2
 * @package WordPress
 * @subpackage _WP
 */
(function($){

$.fn.capitalize = function () {
    $.each(this, function () {
        var split = this.value.split(' ');
        for (var i = 0, len = split.length; i < len; i++) {
            split[i] = split[i].charAt(0).toUpperCase() + split[i].slice(1).toLowerCase();
        }
        this.value = split.join(' ');
    });
    return this;
};

$.widget( "ui.timespinner", $.ui.spinner, {
	options: {
		// seconds
		step: 60 * 1000,
		// hours
		page: 60
	},

	_parse: function( value ) {
		if ( typeof value === "string" ) {
		// already a timestamp
		if ( Number( value ) == value ) {
			return Number( value );
		}
		return +Globalize.parseDate( value );
		}
		return value;
	},

	_format: function( value ) {
		return Globalize.format( new Date(value), "t" );
	}
});

$(document).ready(function(){
	
	/* Fields form WP_Form class */

	// Input number
	$('input[class=number]').spinner({
		culture : $(this).attr('culture'),
		disabled : ( typeof $(this).attr('disabled') !== 'undefined' || $(this).attr('disabled') !== false ),
		max : $(this).attr('max'),
		min : $(this).attr('min'),
		start : $(this).attr('start'),
		numberFormat : 'n',
		step : $(this).attr('step')
	});

	// Input currency
	$('input[class=currency]').spinner({
		culture : $(this).attr('culture'),
		disabled : ( typeof $(this).attr('disabled') !== 'undefined' || $(this).attr('disabled') !== false ),
		max : $(this).attr('max'),
		min : $(this).attr('min'),
		start : $(this).attr('start'),
		numberFormat : 'C',
		step : $(this).attr('step')
	});

	// Progressbar
	$('div[class=progressbar]').progressbar({
		disabled : ( typeof $(this).attr('disabled') !== 'undefined' || $(this).attr('disabled') !== false ),
		max : $(this).attr('max'),
		value : ( typeof $(this).attr('value') !== 'undefined' || $(this).attr('value') !== false )
	});

	// Name
	$('input[class=name]').on('keyup', function(){
		$(this).capitalize();
	});


	// Date
	$('input[class=date]').datepicker({
		appendText: "(yyyy-mm-dd)",
		showButtonPanel: true,
		dateFormat: "yy-mm-dd",
		maxDate: $(this).attr('max'),
		minDate: $(this).attr('min')
	});

	// Time
	$('input[class=time]').timespinner();

	

});

})(jQuery);