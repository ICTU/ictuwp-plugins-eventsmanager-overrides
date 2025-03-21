<?php
/**
 * Outputs a checkbox that can be used to obtain consent.
 * @param EM_Event|EM_Location|EM_Booking|bool $EM_Object
 */
function em_data_privacy_consent_checkbox( $EM_Object = false ){
	if( !empty($EM_Object) && (!empty($EM_Object->booking_id) || !empty($EM_Object->post_id)) ) return; //already saved so consent was given at one point
	if( !doing_action('em_booking_form_after_user_details') && did_action('em_booking_form_after_user_details') ) return; // backcompat
	$dbem_data_privacy_indicator_required = get_option( 'dbem_data_privacy_indicator_required', 'Verplicht' );
	$dbem_data_privacy_title              = get_option( 'dbem_data_privacy_title', 'Toestemming' );
	$dbem_data_privacy_description        = get_option( 'dbem_data_privacy_description', 'We gebruiken je gegevens alleen waarvoor je ze aan ons hebt doorgegeven. Zie ook onze <a href="%s" target="_blank" rel="privacy-policy">privacyverklaring (opent in een nieuw venster)</a>. Verder verwachten we dat je als deelnemer van onze bijeenkomst je houdt aan <a href="https://www.gebruikercentraal.nl/gedragsregels/" target="_blank">onze gedragsregels (opent in een nieuw venster)</a>. Als je op de knop hieronder drukt, dan ga je daarmee akkoord.' );
	$dbem_data_privacy_checkbox_label     = get_option( 'dbem_data_privacy_checkbox_label', 'Ik geef toestemming' );

	// replace privacy policy URL %s with link to policy page
	if ( function_exists( 'get_privacy_policy_url' ) ) {
		$dbem_data_privacy_description = sprintf( $dbem_data_privacy_description, get_privacy_policy_url() );
	}

	// check if consent was previously given and check box if true
	if( is_user_logged_in() ){
        $consent_given_already = get_user_meta( get_current_user_id(), 'em_data_privacy_consent', true );
        if( !empty($consent_given_already) && get_option('dbem_data_privacy_consent_remember') == 1 ) return; //ignore if consent given as per settings
		if( !empty($consent_given_already) && get_option('dbem_data_privacy_consent_remember') == 2 ) $checked = true;
    }
    if( empty($checked) && !empty($_REQUEST['data_privacy_consent']) ) $checked = true;
    // output checkbox
	?>
    <fieldset class="input-group form__field-wrapper--required input-field-data_privacy_consent">
		<legend class="form__label">
			<span class="form__label__content">
			<?php echo sprintf(
				'%s <span class="form__required-asterisk">* <span class="visually-hidden">%s</span></span></label>',
				$dbem_data_privacy_title,
				$dbem_data_privacy_indicator_required,
			);?>
			</span>
		</legend>
        <p id="pp-info" class="privacy-policy-link-info"><?php echo $dbem_data_privacy_description  ?></p>
		<label class="form__label" for="data_privacy_consent">
			<input aria-describedby="pp-info data_privacy_consent_error" type="checkbox" id="data_privacy_consent" name="data_privacy_consent" value="1" <?php if( !empty($checked) ) echo 'checked="checked"'; ?>>
			<?php echo $dbem_data_privacy_checkbox_label; ?>
		</label>
	</fieldset>
	<?php
}

function em_data_privacy_consent_hooks(){
	//BOOKINGS
	if( get_option('dbem_data_privacy_consent_bookings') == 1 || ( get_option('dbem_data_privacy_consent_bookings') == 2 && !is_user_logged_in() ) ){
		add_action('em_booking_form_footer', 'em_data_privacy_consent_checkbox', 9, 0); //supply 0 args since arg is $EM_Event and callback will think it's an event submission form
	    add_action('em_booking_form_after_user_details', 'em_data_privacy_consent_checkbox', 9, 0); //supply 0 args since arg is $EM_Event and callback will think it's an event submission form
		add_filter('em_booking_get_post', 'em_data_privacy_consent_booking_get_post', 10, 2);
		add_filter('em_booking_validate', 'em_data_privacy_consent_booking_validate', 10, 2);
		add_filter('em_booking_save', 'em_data_privacy_consent_booking_save', 10, 2);
	}
	//EVENTS
	if( get_option('dbem_data_privacy_consent_events') == 1 || ( get_option('dbem_data_privacy_consent_events') == 2 && !is_user_logged_in() ) ){
		add_action('em_front_event_form_footer', 'em_data_privacy_consent_event_checkbox', 9, 1);
		/**
		 * Wrapper function in case old overriden templates didn't pass the EM_Event object and depended on global value
		 * @param EM_Event $event
		 */
		function em_data_privacy_consent_event_checkbox( $event ){
			if( empty($event) ){ global $EM_Event; }
			else{ $EM_Event = $event ; }
			em_data_privacy_consent_checkbox($EM_Event);
		}
		add_action('em_event_get_post_meta', 'em_data_privacy_cpt_get_post', 10, 2);
		add_action('em_event_validate', 'em_data_privacy_cpt_validate', 10, 2);
		add_action('em_event_save', 'em_data_privacy_cpt_save', 10, 2);
	}
	//LOCATIONS
	if( get_option('dbem_data_privacy_consent_locations') == 1 || ( get_option('dbem_data_privacy_consent_events') == 2 && !is_user_logged_in() ) ){
		add_action('em_front_location_form_footer', 'em_data_privacy_consent_location_checkbox', 9, 1);	/**
		 * Wrapper function in case old overriden templates didn't pass the EM_Location object and depended on global value
		 * @param EM_Location $location
		 */
		function em_data_privacy_consent_location_checkbox( $location ){
			if( empty($location) ){ global $EM_Location; }
			else{ $EM_Location = $location ; }
			em_data_privacy_consent_checkbox($EM_Location);
		}
		add_action('em_location_get_post_meta', 'em_data_privacy_cpt_get_post', 10, 2);
		add_action('em_location_validate', 'em_data_privacy_cpt_validate', 10, 2);
		add_action('em_location_save', 'em_data_privacy_cpt_save', 10, 2);
	}
}
if( !is_admin() || ( defined('DOING_AJAX') && DOING_AJAX && !empty($_REQUEST['action']) && !in_array($_REQUEST['action'], array('booking_add_one')) ) ){
	add_action('init', 'em_data_privacy_consent_hooks');
}

/**
 * Gets consent information for the submitted booking and and add it to the booking object for saving later.
 * @param bool $result
 * @param EM_Booking $EM_Booking
 * @return bool
 */
function em_data_privacy_consent_booking_get_post( $result, $EM_Booking ){
    if( !empty($_REQUEST['data_privacy_consent']) ){
        $EM_Booking->booking_meta['consent'] = true;
    }
    return $result;
}

/**
 * Validates a bookng to ensure consent is/was given.
 * @param bool $result
 * @param EM_Booking $EM_Booking
 * @return bool
 */
function em_data_privacy_consent_booking_validate( $result, $EM_Booking ){
	if( is_user_logged_in() && ($EM_Booking->person_id == get_current_user_id() || $EM_Booking->person_id === null) ){
		//check if consent was previously given and ignore if settings dictate so
		$consent_given_already = get_user_meta( get_current_user_id(), 'em_data_privacy_consent', true );
		if( !empty($consent_given_already) && get_option('dbem_data_privacy_consent_remember') == 1 ) return $result; //ignore if consent given as per settings
	}
    if( empty($EM_Booking->booking_meta['consent']) ){
		// @NOTE: GC override link to field
	    // $EM_Booking->add_error( sprintf(__('You must allow us to collect and store your data in order for us to process your booking.', 'events-manager')) );
		$this_err = sprintf(__('You must allow us to collect and store your data in order for us to process your booking.', 'events-manager'));
		$EM_Booking->add_error('<a href="#data_privacy_consent">Toestemming: ' . $this_err . '</a>');
	    $result = false;
    }
    return $result;
}

/**
 * Updates or adds the consent date of user account meta if booking was submitted by a user and consent was given.
 * @param bool $result
 * @param EM_Booking $EM_Booking
 * @return bool
 */
function em_data_privacy_consent_booking_save( $result, $EM_Booking ){
    if( $result ){
        if( $EM_Booking->person_id != 0 ){
            update_user_meta( $EM_Booking->person_id, 'em_data_privacy_consent', current_time('mysql') );
        }
    }
    return $result;
}

/**
 * Save consent to event or location object
 * @param bool $result
 * @param EM_Event|EM_Location $EM_Object
 * @return bool
 */
function em_data_privacy_cpt_get_post($result, $EM_Object ){
	if( !empty($_REQUEST['data_privacy_consent']) ){
		if( get_class($EM_Object) == 'EM_Event' ){
			$EM_Object->event_attributes['_consent_given'] = 1;
			$EM_Object->get_location()->location_attributes['_consent_given'] = 1;
		}else{
			$EM_Object->location_attributes['_consent_given'] = 1;
		}
	}
    return $result;
}

/**
 * Validate the consent provided to events and locations.
 * @param bool $result
 * @param EM_Event|EM_Location $EM_Object
 * @return bool
 */
function em_data_privacy_cpt_validate( $result, $EM_Object ){
	if( !empty($EM_Object->post_id) ) return $result;
	if( is_user_logged_in() ){
		//check if consent was previously given and ignore if settings dictate so
		$consent_given_already = get_user_meta( get_current_user_id(), 'em_data_privacy_consent', true );
		if( !empty($consent_given_already) && get_option('dbem_data_privacy_consent_remember') == 1 ) return $result; //ignore if consent given as per settings
	}
	$attributes = get_class($EM_Object) == 'EM_Event' ? 'event_attributes':'location_attributes';
	if( empty($EM_Object->{$attributes}['_consent_given']) ){
		$EM_Object->add_error( sprintf(__('Please check the consent box so we can collect and store your submitted information.', 'events-manager')) );
		$result = false;
    }
	return $result;
}

/**
 * When an event or location is saved and consent is given or supplied again, update user account with latest consent date IF the object isn't associated with an anonymous user.
 * @param bool $result
 * @param EM_Event|EM_Location $EM_Object
 * @return bool
 */
function em_data_privacy_cpt_save( $result, $EM_Object ){
	$attributes = get_class($EM_Object) == 'EM_Event' ? 'event_attributes':'location_attributes';
	if( $result && !empty($EM_Object->{$attributes}['_consent_given'])){
		if( !get_option('dbem_events_anonymous_submissions') || $EM_Object->post_author != get_option('dbem_events_anonymous_user') ){
			update_user_meta( $EM_Object->post_author, 'em_data_privacy_consent', current_time('mysql') );
		}
	}
    return $result;
}