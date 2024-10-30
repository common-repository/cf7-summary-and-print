<?php
/**
 * Handle Settings Fields
 *
 * @package cf7-summary-and-print
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class to Add CF7 Setting Tab
 *
 * @since 1.0
 * @version 1.2
 */
class CF7_SP_Settings {

	/**
	 * Construct function of a class
	 *
	 * @since 1.0
	 * @version 1.1
	 */
	public function __construct() {		
		add_filter( 'wpcf7_default_template', array( $this, 'set_default_template' ), 10, 2 );
		add_filter( 'wpcf7_contact_form_properties', array( $this, 'add_new_property' ), 10, 2 );
		add_filter( 'wpcf7_save_contact_form', array( $this, 'save_summary_tab' ), 10, 1 );
		add_filter( 'admin_enqueue_scripts', array( $this, 'admin_script_style' ) );
		add_action( 'admin_menu', array( $this, 'register_sub_menu_cf7_summary' ) );
		add_action( 'wp_ajax_cf7_hide_summary_notice', array( $this, 'cf7_hide_summary_notice_func' ) );
		add_action( 'cf7sp_before_settings_loaded', array($this, 'cf7sp_save_settings') );
	}
	

	function cf7_hide_summary_notice_func() {
		global $wpdb; // this is how you get access to the database

		if( isset( $_POST['hide_notice'] ) ) {
			update_option( 'cf7_sp_notice', true );
		}

		wp_die(); // this is required to terminate immediately and return a proper response
	}

	public function register_sub_menu_cf7_summary() {
		add_submenu_page( 'wpcf7', 'Summary & Print', 'Summary & Print', 'manage_options', 'cf7-summary-Print', array( $this, 'cf7_summary_print_callback' ) );
	}

	public function ilc_admin_tabs( $current = 'general' ) {
		$tabs = array( 'general' => 'General', 'pdf' => 'PDF Viewer' );
		echo '<div id="icon-themes" class="icon32"><br></div>';
		echo '<h2 class="nav-tab-wrapper">';
		foreach( $tabs as $tab => $name ){
			$class = ( $tab == $current ) ? ' nav-tab-active' : '';
			echo "<a class='nav-tab$class' href='?page=cf7-summary-Print&tab=$tab'>$name</a>";
	
		}
		echo '</h2>';
	}

	public function cf7_summary_print_callback() {
		
		do_action('cf7sp_before_settings_loaded');
		
		$cf7_form_data = get_option( 'cf7_summary_print', true );

		$cf7_form_list = get_posts( array( 'post_type' => 'wpcf7_contact_form', 'numberposts' => -1 ) );

		$form_dropdown = '';		
		foreach( $cf7_form_list as $cf7_form ) {

			$form_dropdown .= '<option value="'.$cf7_form->ID.'" '.( isset( $cf7_form_data['cf7-enabled-for'] ) && in_array( $cf7_form->ID, (array) $cf7_form_data['cf7-enabled-for'] ) ? 'selected' : '' ).' >'.$cf7_form->post_title.'</option>';
		}

		echo ( isset( $_GET['tab'] ) ? $this->ilc_admin_tabs( $_GET['tab'] ) : $this->ilc_admin_tabs() );
		?>
		<div class="wrap">
			<h1>Contact Form 7 Summary & Print</h1>
			<hr>
			<form action="#" method="post">
			<table class="form-table" role="presentation">

			<tbody>

			<?php if( !isset( $_GET['tab'] ) || $_GET['tab'] == 'general' ) : ?>

			<?php wp_nonce_field('cf7_summary_nonce'); ?>
			<tr>
			<th scope="row"><label for="cf7-enabled">Enable</label></th>
			<td><input name="cf7-enabled" type="checkbox" id="cf7-enabled" <?php echo ( isset( $cf7_form_data['cf7-enabled'] ) ? 'checked' : '' ); ?> value="1" class="regular-text">
			<p class="description" id="cf7-enabled-description">Enable CF7 Summary & Print</p></td>
			</tr>
			<tr>
			<th scope="row"><label for="cf7-enabled-for">Select CF7 Forms</label></th>
			<td><select name="cf7-enabled-for[]" class="cf7-form-list regular-text" multiple="multiple"><?php echo $form_dropdown; ?></select>
			<p class="description" id="cf7-enabled-description">Select forms which you would like to enable for summary</p></td>
			</tr>
			
			<th scope="row"><label for="cf7-summary-title">Summary Title</label></th>
			<td><input name="cf7-summary-title" type="text" id="cf7-summary-title" value="<?php echo ( isset( $cf7_form_data['cf7-summary-title'] ) ? $cf7_form_data['cf7-summary-title'] : 'Summary' ); ?>" class="regular-text">
			<p class="description" id="cf7-summary-title-description">Add summary title</p></td>
			</tr>

			<tr>
			<th scope="row"><label for="cf7-summary-msg-enabled">Display message on summary</label></th>
			<td><input name="cf7-summary-msg-enabled" type="checkbox" id="cf7-summary-msg-enabled" <?php echo ( isset( $cf7_form_data['cf7-summary-msg-enabled'] ) ? 'checked' : '' ); ?> value="1" class="regular-text">
			<p class="description" id="cf7-summary-msg-description">Message will apear after the form submit instead of form field\'s values.</p></td>
			</tr>
			
			<tr>
			<th scope="row"><label for="cf7-summary-msg">Message</label></th>
			<td><textarea id="cf7-summary-msg" name="cf7-summary-msg" rows="5" col="10" class="regular-text"><?php echo ( isset( $cf7_form_data['cf7-summary-msg'] ) ? $cf7_form_data['cf7-summary-msg'] : 'Hi [your-name], Thank you for contacting us we will contact you on your email [your-email].' ); ?></textarea>
			<p class="description" id="cf7-summary-msg-description">Enter message to show on summary page</p></td>
			</tr>
			
			<tr>
			<th scope="row"><label for="cf7-summary-msg">Print Button Text</label></th>
			<td><input name="cf7-summary-btn" type="text" id="cf7-summary-btn" value="<?php echo ( isset( $cf7_form_data['cf7-summary-btn'] ) ? $cf7_form_data['cf7-summary-btn'] : 'Print this form' ); ?>" class="regular-text">
			<p class="description" id="cf7-summary-msg-description">Print Button Text( Leave empty if you do not want to show print button )</p></td>
			</tr>

			<?php do_action('after_cf7_summary_print_settings');?>
			
			<tr>
			<td colspan="2">
			<p style="color: gray;font-style: italic;">Note: If you\'r using any cache plugin, please purge all the cache after saving the settings.</p>
			</td>
			</tr>

			<tr>
			<td><input type="submit" name="cf7-summary-submit" id="submit" class="button button-primary" value="Save Changes"></td>
			</tr>

			<?php endif;
			
			if( isset( $_GET['tab'] ) && $_GET['tab'] == 'pdf' ) :
			
				global $cf7sapp_fs;
				
				$cf7_form_data = get_option( 'cf7_summary_print_pro', true );

				if( !class_exists( 'CF7_SP_PRO_Settings' ) ) { ?>
					<tr>
					<td><p style="color: red;">Available in PRO <a href="https://muhammadrehman.com/contact-form-7-summary-and-print-pro/" target="_blank">Get PRO</a></p></td>
					</tr>
				<?php } elseif( $cf7sapp_fs->is__premium_only() && !$cf7sapp_fs->can_use_premium_code() ) { ?>
					<tr>
					<th></th>
					<td><p style="color: red;">Please activate your plugin license to enable PRO Features <a href="https://muhammadrehman.com/contact-form-7-summary-and-print-pro/license-activation/" target="_blank">activate my license</a></p></td>
					</tr>
				<?php } ?>
					<tr>
					<th scope="row"><label for="cf7-enabled-pdf-viewer">Enable</label></th>
					<td><input name="cf7-enabled-pdf-viewer" type="checkbox" id="cf7-enabled-pdf-viewer" <?php echo ( isset( $cf7_form_data['cf7-enabled-pdf-viewer'] ) ? 'checked' : '' ); ?> value="1" class="regular-text">
					<p class="description" id="cf7-enabled-pdfdescription">Enable PDF viewer</p></td>
					</tr>
					<tr>
					
					<th scope="row"><label for="cf7-pdf-title">PDF Header Title</label></th>
					<td><input name="cf7-pdf-title" type="text" id="cf7-pdf-title" value="<?php echo ( isset( $cf7_form_data['cf7-pdf-title'] ) ? $cf7_form_data['cf7-pdf-title'] : '' ); ?>" class="regular-text">
					<p class="description" id="cf7-pdf-title-description">PDF header title</p></td>
					</tr>

					<th scope="row"><label for="cf7-pdf-btn-text">PDF View Button Text</label></th>
					<td><input name="cf7-pdf-btn-text" type="text" id="cf7-pdf-btn-text" value="<?php echo ( isset( $cf7_form_data['cf7-pdf-btn-text'] ) ? $cf7_form_data['cf7-pdf-btn-text'] : '' ); ?>" class="regular-text">
					<p class="description" id="cf7-pdf-btn-text-description">Download PDF button text</p></td>
					</tr>

					<tr>
					<th scope="row"><label for="cf7-wtrmrk-txt">Watermark Text</label></th>
					<td><input name="cf7-wtrmrk-txt" type="text" id="cf7-wtrmrk-txt" placeholder="W a t e r m a r k   d e m o" value="<?php echo ( isset( $cf7_form_data['cf7-wtrmrk-txt'] ) ? $cf7_form_data['cf7-wtrmrk-txt'] : '' ); ?>" class="regular-text">
					<p class="description" id="cf7-wtrmrk-txt-description">PDF watermark text( Leave empty if you do not want to show watermark text )</p></td>
					</tr>

					<th scope="row"><label for="cf7-pdf-logo">PDF Logo</label></th>
					<td>
						<?php
						$image_id = ( isset( $cf7_form_data['cf7sp_img'] ) ? $cf7_form_data['cf7sp_img'] : NULL );
						if( isset( $image_id ) && $image = wp_get_attachment_image_url( $image_id, 'medium' ) ) : ?>
						<a href="#" class="cf7sp-upload">
							<img src="<?php echo esc_url( $image ) ?>" width="100"/>
						</a>
						<p>
							<a href="#" class="cf7sp-remove">Remove image</a>
						</p>
						<input type="hidden" name="cf7sp_img" value="<?php echo absint( $image_id ) ?>">
						<?php else : ?>
							<a href="#" class="button cf7sp-upload">Upload image</a>
							<a href="#" class="cf7sp-remove" style="display:none">Remove image</a>
						<input type="hidden" name="cf7sp_img" value="">
						<?php endif; ?>
					<p class="description" id="cf7-pdf-title-description">Add logo on PDF</p></td>
					</tr>

					<tr>
					<th scope="row"><label for="cf7-hdr-pos">Header Position</label></th>
					<td>
						<label for="cf7-hdr-pos-l">Left</label>
						<input name="cf7-hdr-pos" type="radio" <?php echo ( isset( $cf7_form_data['cf7-hdr-pos'] ) && $cf7_form_data['cf7-hdr-pos'] == 'left' ? 'checked' : '' ) ?> id="cf7-hdr-pos-l" value="left" class="regular-text">
						<label for="cf7-hdr-pos-r">Right</label>
						<input name="cf7-hdr-pos" type="radio" <?php echo ( isset( $cf7_form_data['cf7-hdr-pos'] ) && $cf7_form_data['cf7-hdr-pos'] == 'right' ? 'checked' : '' ) ?> id="cf7-hdr-pos-r" value="right" class="regular-text">
					<p class="description" id="cf7-hdr-pos-description">Set position of header logo</p></td>
					</tr>
					
					<tr>
					<td colspan="2">
					<p style="color: gray;font-style: italic;">Note: If you\'r using any cache plugin, please purge all the cache after saving the settings.</p>
					</td>
					</tr>

					<tr>
					<td><input type="submit" name="cf7-summary-submit-pro" id="submit" class="button button-primary" value="Save Changes"></td>
					</tr>
			<?php endif; ?>
		</tbody></table>
			</form>
			</div>

		<?php
	}
	
	public function cf7sp_save_settings() {
		
		if( isset( $_POST['cf7-summary-submit'] ) ) {

			if ( !isset( $_POST['_wpnonce'] ) || !wp_verify_nonce( $_POST['_wpnonce'], 'cf7_summary_nonce' ) ) {
				wp_die('<strong>Cheat!</strong> Something went wrong...');
			}

			$form_data = array();
			foreach( $_POST as $index => $form_values ) {
				if( $index != 'cf7-summary-submit' ) {
					if( $index == 'cf7-summary-msg' ) {
						$form_data[ $index ] = sanitize_textarea_field( wp_unslash( $form_values ) );
					} else if( $index == 'cf7-enabled-for' ) {
					 	$form_data[ $index ] = map_deep( $form_values, 'sanitize_text_field' );
					} else {
						$form_data[ $index ] = sanitize_text_field( wp_unslash( $form_values ) );
					}
				}
			}
			update_option( 'cf7_summary_print', $form_data );
			
			$html = '<div class="updated success fs-notice fs-sticky fs-has-title cf7_summary_saved">
					<div class="fs-notice-body">
						Settings Saved!
					</div>
				</div>';

		return $html;
		} else {
			return false;
		}
	}
    
	/**
	 * Handle CSS & JS for backend settings
	 *
	 * @since 1.2
	 * @version 1.0
	 */
	public function admin_script_style() {

		wp_enqueue_style( 'cf7-admin-css', plugins_url( 'assets/css/cf7-admin-sp-style.css', CF7_SP_THIS ), array(), CF7_SP_VERSION );
		wp_enqueue_script( 'cf7-admin-js', plugins_url( 'assets/js/cf7-admin-sp-script.js', CF7_SP_THIS ), array( 'jquery' ), CF7_SP_VERSION, true );

		// WordPress media uploader scripts
		if ( ! did_action( 'wp_enqueue_media' ) ) {
			wp_enqueue_media();
		}
		
		wp_enqueue_script( 'cf7-file-uploader', plugins_url( 'assets/js/cf7-file-uploader.js', CF7_SP_THIS ), array( 'jquery' ), CF7_SP_VERSION, true );
		wp_enqueue_style( 'cf7-sp-select2css', plugins_url( 'assets/css/select2-css.css', CF7_SP_THIS ), false, CF7_SP_VERSION, 'all' );
		wp_enqueue_script( 'cf7-sp-select2js', plugins_url( 'assets/js/select2-js.js', CF7_SP_THIS ), array( 'jquery' ), CF7_SP_VERSION, true );
	}

	/**
	 * Set up Default values of setting fields
	 *
	 * @param string $template cf7 template.
	 * @param string $prop cf7 prop.
	 *
	 * @since 1.0
	 * @version 1.1
	 */
	public function set_default_template( $template, $prop ) {

		if ( 'cf7_sp_summary' === $prop ) {
			$template = $this->default_template();
		}

		return $template;
	}

	/**
	 * Set Default Values of the fields
	 *
	 * @since 1.0
	 * @version 1.1
	 */
	public function default_template() {

		$template = array(
			'cf7_sp_enable'         => __( '1', 'CF7_SP' ),
			'cf7_sp_title'          => __( 'Form Summary', 'CF7_SP' ),
			'cf7_sp_message_check'  => __( '0', 'CF7_SP' ),
			'cf7_sp_message'        => __( 'Enter Your Message', 'CF7_SP' ),
			'cf7_sp_print_btn_text' => __( 'Print This Form', 'CF7_SP' ),
		);

		return $template;
	}

	/**
	 * Add new property to CF7 to save summary tab fields
	 *
	 * @param parse_arg $properties array.
	 * @param object    $object cf7 object.
	 *
	 * @since 1.0
	 * @version 1.0
	 */
	public function add_new_property( $properties, $object ) {

		$properties = wp_parse_args(
			$properties,
			array(
				'cf7_sp_summary' => array(),
			)
		);

		return $properties;
	}

	/**
	 * Save Summary Tab Fields
	 *
	 * @param array $contact_form post values.
	 *
	 * @since 1.0
	 * @version 1.1
	 */
	public function save_summary_tab( $contact_form ) {

		if ( isset( $_POST['cf7_summary_print_nonce'] ) || wp_verify_nonce( sanitize_key( $_POST['cf7_summary_print_nonce'] ), 'cf7_summary_print' ) ) {
				$properties['cf7_sp_summary']['enable']          = ( isset( $_POST['cf7_sp_enable'] ) ? sanitize_text_field( wp_unslash( $_POST['cf7_sp_enable'] ) ) : '' );
				$properties['cf7_sp_summary']['title']           = ( isset( $_POST['cf7_sp_title'] ) ? sanitize_text_field( wp_unslash( $_POST['cf7_sp_title'] ) ) : '' );
				$properties['cf7_sp_summary']['message_check']   = ( isset( $_POST['cf7_sp_message_check'] ) ? sanitize_text_field( wp_unslash( $_POST['cf7_sp_message_check'] ) ) : '' );
				$properties['cf7_sp_summary']['message']         = ( isset( $_POST['cf7_sp_message'] ) ? wp_kses_post( wp_unslash( $_POST['cf7_sp_message'] ) ) : '' );
				$properties['cf7_sp_summary']['print_btn_txt']   = ( isset( $_POST['cf7_sp_print_btn_text'] ) ? sanitize_text_field( wp_unslash( $_POST['cf7_sp_print_btn_text'] ) ) : '' );
				$properties['cf7_sp_summary']['additional_text'] = '';
				$contact_form->set_properties( $properties );
		}
	}
}

$cf7_sp_settings = new CF7_SP_Settings();
