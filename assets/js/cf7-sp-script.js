/**
 * Handle JS Events
 *
 * @package cf7-summary-and-print
 */

/**
 * Trigger when CF7 submit and sent an email notif ication
 *
 * @since 1.0
 * @version 1.2
 */

var wpcf7Elm = document.querySelector( '.wpcf7' );

if( wpcf7Elm != null ) {

wpcf7Elm.addEventListener(
	'wpcf7mailsent',
	function ( event ) {		
				
		if( cf7_sp.summay_enabled_for.includes( event['detail']['contactFormId'].toString() ) == true ) {

			var summary 	= '';
			var input_label = '';
			var input_value = '';
			
			/* To avoid deuplicate labels on summary page.
			* This is happing with multi checkboxes
			*/
			var last_label = '';

			// Make sure title is not empty.
			if ( cf7_sp.summay_title != '' ) {
				summary += '<h3>' + cf7_sp.summay_title + '</h3>';
			}

			if ( cf7_sp.summay_message_check == '1' ) {

				// Use regex to get tags.
				var regex 		   = /\[(.*?)\]/g;
				var summay_message = cf7_sp.summay_message;

				var replace_tags = '';
				while ( matches = summay_message.match( regex ) ) {					

					if ( matches[0] == '' || typeof matches[0] == 'undefined' ) {
						break;
					}

					var match = matches[0];
					match     = match.replace( '[','' );
					match     = match.replace( ']','' );
			
		
					var fields_value = jQuery( 'input[name="' + match + '"],select[name="' + match + '"],textarea[name="' + match + '"]' ).val();

					if ( typeof fields_value != 'undefined' ) {
						summay_message = cf7_sp_replace_tag( summay_message, '[' + match + ']', fields_value );
						summay_message = summay_message.replace( /\n/g,'<br>' );
					} else {
						var multi_select = '';
						jQuery( 'input[name="' + match + '[]"]:checked' ).each(						
							function () {
								multi_select += jQuery( this ).val() + ' ';
							}
						);
						summay_message = cf7_sp_replace_tag( summay_message, '[' + match + ']', multi_select );
						summay_message = summay_message.replace( /\n/g,'<br>' );
					}
				}
				summary += '<p class="custom-msg">' + summay_message + '</p>';
			} else {

				// Getting all the form data.
				jQuery( 'form.wpcf7-form p' ).each(
					function () {						

						input_value = '';
						input_value = '';

						if ( jQuery( this ).find( 'input[type=checkbox],input[type=radio]' ) && jQuery( this ).find( 'input[type=checkbox],input[type=radio]' ).is( ':checked' ) ) {
							input_value += '<ul>';
							jQuery( this ).find( 'input[type="checkbox"]:checked,input[type=radio]:checked' ).each(
								function () {									
									var elem     = jQuery( this );
									input_label  = elem.attr( 'name' );
									input_value += '<li>';
									input_value += elem.val();
									input_value += '</li>';									
									
								}
							);
							input_value += '</ul>';
						} else if ( jQuery( this ).find( 'input[type=email],input[type=text],input[type=date],input[type=number],select,textarea' ) ) {

							var elem = jQuery( this ).find( 'input[type=email],input[type=text],input[type=date],input[type=number],select,textarea' );

							input_label = elem.attr( 'name' ); // Getting field label.
							input_value = '<p>'+elem.val()+'</p>'; // Getting field value.							
						}

						// dump all the form data in a variable to print them after form submission.
						// Skip all unwanted fields.
						if ( typeof input_label != 'undefined' && typeof input_value != 'undefined' ) {

							if( last_label != cf7_sp_label_formating( input_label ) ) {
								summary += '<strong>' + cf7_sp_label_formating( input_label ) + '</strong>';
								last_label = cf7_sp_label_formating( input_label );
							}							
							summary += '<div class="cf7-row">' + input_value + '</div>';							
						}
					}
				);
			}

			// Add additional text at the bottom of the summary page.
			//if ( cf7_sp.summary_additional_text != '' && cf7_sp.summary_additional_text != null ) {
			//	summary += cf7_sp.summary_additional_text;
			//}

			// Print Button.
			summary += cf7_sp_print_btn();
			summary += cf7_sp_pdf_print_btn();

			setTimeout(function() {
				// Show the summary.
				jQuery( '.wpcf7' ).html( summary );
			},500);
		}
		cf7_sp_save_entry( summary );
	},
	false
);

}

/**
 * Clean Field's label
 *
 * @param cf7_label
 *
 * @since 1.0
 * @version 1.0
 */
function cf7_sp_label_formating( cf7_label ) {
	var cf7_label = cf7_label.replace( /-/g, ' ' );
	cf7_label     = cf7_label.replace( /\[/g, ' ' );
	cf7_label     = cf7_label.replace( /]/g, ' ' );
	cf7_label     = cf7_label.toLowerCase().replace(
		/\b[a-z]/g,
		function ( letter ) {
			return letter.toUpperCase();
		}
	);

	return cf7_label;
}

/**
 * Print Button
 *
 * @since 1.0
 * @version 1.0
 */
function cf7_sp_print_btn() {

	var html = '';
	if ( cf7_sp.summay_print_btn_text != "" ) {
		var html = '<div><input type="button" id="cf7-print-btn" value="' + cf7_sp.summay_print_btn_text + '" onclick="cf7_sp_print_form()" /></div>';
	}

	return html;
}

/**
 * Print Button
 *
 * @since 1.0
 * @version 1.0
 */
function cf7_sp_pdf_print_btn() {

	var html = '';
	if ( cf7_sp.pdf_print_btn_txt != "" && cf7_sp.is_pdf_enabled == 'pdf_enabled' ) {
		var html = '<div><input type="button" id="cf7-print-pdf-btn" value="' + cf7_sp.pdf_print_btn_txt + '" onclick="cf7_sp_print_pdf()" /></div>';
	}

	return html;
}

/**
 * Print only form area
 *
 * @since 1.0
 * @version 1.0
 */
function cf7_sp_print_form() {

	jQuery( '#cf7-print-btn' ).hide();
	var whole_content = jQuery( 'body' ).html();
	var print_form = jQuery( 'div.wpcf7' ).html();

	jQuery( 'body' ).html( print_form );
	window.print();

	setTimeout(
		function () {
			jQuery( 'body' ).html( whole_content );
			jQuery( '#cf7-print-btn' ).show();
		},
		10
	);
}

/**
 * PDF Viewer
 *
 * @since 1.0
 * @version 1.0
 */
function cf7_sp_print_pdf() {

	window.open( window.location.origin + '?cf7-sp-pdf-viewer' );
}

/**
 * Replace bracket tags to Field's value
 *
 * @since 1.1
 * @version 1.0
 */
function cf7_sp_replace_tag( str,replaceWhat,replaceTo ) {
	replaceWhat = replaceWhat.replace( /[-\/\\^$* + ?.()|[\]{}]/g, '\\$&' );
	var re      = new RegExp( replaceWhat, 'g' );
	return str.replace( re,replaceTo );
}

/**
 * Save entry in CPT to generate PDF
 */
function cf7_sp_save_entry( html ) {
	var data = {
		'action': 'cf7_sp_save_entry_action',
		'form_data': html      // We pass php values differently!
	};
	// We can also pass the url value separately from ajaxurl for front end AJAX implementations
	jQuery.post(cf7_sp.ajax_url, data, function(entry_id) {		
		setCookie('cf7_sp_entry_id', entry_id);
	});
}

function setCookie(cname, cvalue, exdays) {
	const d = new Date();
	d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
	let expires = "expires="+d.toUTCString();
	document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
}
  
function getCookie(cname) {
	let name = cname + "=";
	let ca = document.cookie.split(';');
	for(let i = 0; i < ca.length; i++) {
	  let c = ca[i];
	  while (c.charAt(0) == ' ') {
		c = c.substring(1);
	  }
	  if (c.indexOf(name) == 0) {
		return c.substring(name.length, c.length);
	  }
	}
	return "";
}
  
function delete_cookie( name, path, domain ) {
	if( getCookie( name ) ) {
	  document.cookie = name + "=" +
		((path) ? ";path="+path:"")+
		((domain)?";domain="+domain:"") +
		";expires=Thu, 01 Jan 1970 00:00:01 GMT";
	}
  }