<?php
/**
 * Handlev view sumary & print button
 *
 * @package cf7-summary-and-print
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class to Show Summary after form submission with print button
 *
 * @since 1.0
 * @version 1.1
 */
class CF7_SP_Summary_Print {

	/**
	 * Construct function of a class
	 *
	 * @since 1.0
	 * @version 1.0
	 */
	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'register_sp_script' ) );
		add_action('admin_notices', array( $this, 'notice_for_settings' ) );
	}
	
	public function notice_for_settings(){
		if ( empty( get_option( 'cf7_sp_notice' ) ) ) {
			 echo '<div class="notice notice-warning is-dismissible cf7-summary-print-notice">
			     <p><strong>Contact Form 7 Summary & Print</strong> is activated. Go to <a href="'.admin_url().'admin.php?page=cf7-summary-Print">settings page</a>.</p>
			 </div>';
		}
	}


	/**
	 * Register Script
	 *
	 * @since 1.0
	 * @version 1.2
	 */
	public function register_sp_script() {
		global $post;

		$summary = array();
		
		$cf7_form_data = get_option( 'cf7_summary_print', false );
		$cf7_form_pro_data = get_option( 'cf7_summary_print_pro', false );

		if( !empty( $cf7_form_data ) ) {
			$summary['title'] = ( !empty( $cf7_form_data['cf7-summary-title'] ) ? $cf7_form_data['cf7-summary-title'] : '' );
			$summary['print_btn_txt'] = ( !empty( $cf7_form_data['cf7-summary-btn'] ) ? $cf7_form_data['cf7-summary-btn'] : '' );
			$summary['message_check'] = ( !empty( $cf7_form_data['cf7-summary-msg-enabled'] ) ? $cf7_form_data['cf7-summary-msg-enabled'] : '' );
			$summary['message'] = ( !empty( $cf7_form_data['cf7-summary-msg'] ) ? $cf7_form_data['cf7-summary-msg'] : '' );
			$summary['cf7-enabled'] = ( !empty( $cf7_form_data['cf7-enabled'] ) ? $cf7_form_data['cf7-enabled'] : '' );
			$summary['cf7-enabled-for'] = ( !empty( $cf7_form_data['cf7-enabled-for'] ) ? $cf7_form_data['cf7-enabled-for'] : '' );
		} else {
			// Loop to get all the shortcodes that running on curret page.
			$cf7_form = $this->get_cf7_form();
			if ( ! is_array( $cf7_form ) ) {
				return;
			}

			foreach ( $cf7_form as $form ) {
				// If this is contant form 7 shortcode.
				if ( isset( $form['id'] ) && 'wpcf7_contact_form' === get_post_type( $form['id'] ) ) {
					// Getting Summary Settings.
					$summary = get_post_meta( $form['id'], '_cf7_sp_summary', true );
					break; // exit from loop.
				}
			}

		}

		if ( empty( $summary ) ) {
			return;
		}

		if( !empty( $cf7_form_pro_data ) && isset( $cf7_form_pro_data['cf7-pdf-btn-text'] ) ) {
			$pdf_print_btn_txt = $cf7_form_pro_data['cf7-pdf-btn-text'];
		}

		// Register the script.
		wp_register_script( 'cf7_sp_js', plugins_url( 'assets/js/cf7-sp-script.js', CF7_SP_THIS ), array( 'jquery' ), CF7_SP_VERSION, true );

		// Modify Summary Settings data or add Additional text on print window.
		$summary = apply_filters( 'cf7_sp_summary_settings', $summary );
		
		// Localize the script.
		$localize_data = array(
			'summay_title'            	=> ( !empty( $summary['title'] ) ? $summary['title'] : '' ),
			'summay_print_btn_text'   	=> ( !empty( $summary['print_btn_txt'] ) ? $summary['print_btn_txt'] : '' ),
			'summay_message_check'    	=> ( !empty( $summary['message_check'] ) ? $summary['message_check'] : '' ),
			'summay_message'          	=> ( !empty( $summary['message'] ) ? $summary['message'] : '' ),
			'summay_enabled_for'        => ( !empty( $summary['cf7-enabled-for'] ) ? $summary['cf7-enabled-for'] : '' ),
			'ajax_url'					=> admin_url( 'admin-ajax.php' ),
			'is_pdf_enabled'         	=> ( class_exists( 'CF7_SP_PRO_Settings' ) && !empty( $cf7_form_pro_data['cf7-enabled-pdf-viewer'] ) ? 'pdf_enabled' : '' ),
			'pdf_print_btn_txt'         => ( !empty( $pdf_print_btn_txt ) ? $pdf_print_btn_txt : '' )
			//'summary_additional_text' => $summary['additional_text'],
		);
	
		
		wp_localize_script( 'cf7_sp_js', 'cf7_sp', $localize_data );

		// Enqueued script with localized data.
		if ( !empty($summary['cf7-enabled']) && '1' === $summary['cf7-enabled'] ) {
			wp_enqueue_script( 'cf7_sp_js' );
		}
	}

	/**
	 * Getting Contact Form 7 shortcode Attributes
	 *
	 * @since 1.0
	 * @version 1.0
	 */
	public function get_cf7_form() {
		global $post;
		$result = array();

		// get shortcode regex pattern WordPress function.
		$pattern = get_shortcode_regex();

		if ( preg_match_all( '/' . $pattern . '/s', $post->post_content, $matches ) ) {
			$keys   = array();
			$result = array();
			foreach ( $matches[0] as $key => $value ) {

				// $matches[3] return the shortcode attribute as string. Replace space with '&' for parse_str() function.
				$get = str_replace( ' ', '&', $matches[3][ $key ] );
				parse_str( $get, $output );

				// get all shortcode attribute keys.
				$keys     = array_unique( array_merge( $keys, array_keys( $output ) ) );
				$result[] = $output;

			}

			if ( $keys && $result ) {

				// Loop the result array and add the missing shortcode attribute key.
				foreach ( $result as $key => $value ) {

					// Loop the shortcode attribute key.
					foreach ( $keys as $attr_key ) {
						$result[ $key ][ $attr_key ] = isset( $result[ $key ][ $attr_key ] ) ? str_replace( '"', '', $result[ $key ][ $attr_key ] ) : null;
					}

					// sort the array key.
					ksort( $result[ $key ] );
				}
			}

			// display the result.
			return $result;
		}
	}
}

$cf7_sp_summary_print = new CF7_SP_Summary_Print();
