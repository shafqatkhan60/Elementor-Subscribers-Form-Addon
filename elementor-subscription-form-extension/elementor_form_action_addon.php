<?php
/**
 * Class subscriber_Action_After_Submit
 * @see https://developers.elementor.com/custom-form-action/
 * Custom elementor form action after submit to add a subsciber to
 * subscriber Apply via API 
 */
class subscriber_Action_After_Submit extends \ElementorPro\Modules\Forms\Classes\Action_Base {
	/**
	 * Get Name
	 *
	 * Return the action name
	 *
	 * @access public
	 * @return string
	 */


	public function get_name() {
		return 'Subscriber';
	}

	/**
	 * Get Label
	 *
	 * Returns the action label
	 *
	 * @access public
	 * @return string
	 */
	public function get_label() {
		return __( 'Subscriber', 'elementor-pro' );
	}

	/**
	 * Run
	 *
	 * Runs the action after submit
	 *
	 * @access public
	 * @param \ElementorPro\Modules\Forms\Classes\Form_Record $record
	 * @param \ElementorPro\Modules\Forms\Classes\Ajax_Handler $ajax_handler
	 */
	public function run( $record, $ajax_handler ) {
		//$settings = $record->get( 'form_settings' );
		
		$settings = $record->get( 'form_settings' );
		

		$settings = $record->replace_setting_shortcodes( $settings );
		$fields = [];
		// Get sumitetd Form data
		$raw_fields = $record->get( 'fields' );

		// Normalize the Form Data
		$fields = [];
		foreach ( $raw_fields as $id => $field ) {
			$fields[ $id ] = $field['value'];
		}

		$subscriber_user_name = $settings['subscriber_user_name'];
		$subscriber_user_email = $settings['subscriber_user_email'];
		if($subscriber_user_name==''){
			$subscriber_user_name = $subscriber_user_email;
		}
/*Spam Validation Email */
		$endings = array('\.ru');
if(preg_match('/('.implode('|', $endings).')$/i', $subscriber_user_email )) {
    $message = ['email', 'You can\'t submit this form because your email domain is blacklisted' ];
	$messageAjax = 'You can\'t submit this form because your email domain is blacklisted';
    $ajax_handler->add_error_message( $messageAjax );
    return $message;
}

//----------------------------------

		$args = array(
   	'fields' => 'ids',
   'post_type'   => 'ele-subscribers',
   'meta_query'  => array(
     array(
     'key' => 'email',
     'value' => $subscriber_user_email
     ),
     
   )
 );

 $my_query = new WP_Query( $args );
 if( empty($my_query->have_posts()) ) {
   $inserted_subscription = wp_insert_post(array (
  				'post_title'    => $subscriber_user_name,
 				 'post_status'   => 'publish',
  				'post_type'     => 'ele-subscribers',
 	));


   if ($inserted_subscription) {
       // insert post meta
				add_post_meta ($inserted_subscription, 'email', $subscriber_user_email);
				//add_post_meta ($inserted_subscription, 'status', 'Not allowed');
   }
 }
 else{
 /*
	$fields = $record->get_field( [
        'id' => 'email',
    ] );
    $field = current( $fields );
        $ajax_handler->add_error();
    */
	 
	 
	 $message = 'Thank you. You already submitted your email.';

			$ajax_handler->add_error_message( $message );
	
 }






//----------------------------------
	


	}



	/**
	 * Register Settings Section
	 *
	 * Registers the Action controls
	 *
	 * @access public
	 * @param \Elementor\Widget_Base $widget
	 */
	public function register_settings_section( $widget ) {
		$widget->start_controls_section(
			'section_subscriber',
			[
				'label' => __( 'Elementor subscribers', 'elementor-pro' ),
				'condition' => [
					'submit_actions' => $this->get_name(),
				],
			]
		);


	
		$widget->add_control(
			'subscriber_user_name',
			[
				'label' => __( 'Name field', 'elementor-pro' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'separator' => 'before',
				'description' => __( 'Please insert the field code for Name field e.g. [field id="name"]' , 'elementor-pro' ),
			]
		);

		$widget->add_control(
			'subscriber_user_email',
			[
				'label' => __( 'Email Field', 'elementor-pro' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'separator' => 'before',
				'description' => __( 'Please insert the field code for Email field e.g. [field id="email"]' , 'elementor-pro' ),
			]
		);

		$widget->end_controls_section();

	}

	/**
	 * On Export
	 *
	 * Clears form settings on export
	 * @access Public
	 * @param array $element
	 */
	public function on_export( $element ) {
		
		/*unset(
			$element['subscriber_resume_field'],
			$element['subscriber_password_field'],
			$element['subscriber_name_field'],
			$element['subscriber_email_field']
		);*/
	}
}