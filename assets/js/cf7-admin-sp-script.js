/**
 * Admin script for handle settings things
 *
 * @package cf7-summary-and-print
 */

/**
 * Hide / Show message area in setting area
 *
 * @since 1.2
 * @version 1.0
 */
jQuery( document ).ready(
	function () {
	
		jQuery('.cf7-form-list').select2();
		jQuery( "#cf7_sp_message_check" ).click(
			function () {
				if ( jQuery( "#cf7_sp_message_check" ).prop( 'checked' ) == true ) {
					jQuery( '#cf7_sp_summary p.sp_msg' ).removeClass( 'hide' );
				} else {
					jQuery( '#cf7_sp_summary p.sp_msg' ).addClass( 'hide' );
				}
			}
		);
	
	
	jQuery('.cf7-summary-print-notice button.notice-dismiss').click(function() {
		var data = {
			'action': 'cf7_hide_summary_notice',
			'hide_notice': true
		};

		// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
		jQuery.post(ajaxurl, data, function(response) {			
		});
	});
	}
);
