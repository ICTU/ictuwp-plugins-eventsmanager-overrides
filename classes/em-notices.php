<?php
    /**
     *
     * @author marcus
     *
     */
    class EM_Notices implements Iterator, JsonSerializable {
    	/**
    	 * If object has been displayed, this gets set to true, can be checked to avoid duplicates.
    	 * @var boolean
    	 * @since 5.5.7
    	 */
    	public $displayed = false;
    	public $set_cookies = true;
		public $notices = array('errors'=>array(), 'infos'=>array(), 'alerts'=>array(), 'confirms'=>array());

        function __construct( $set_cookies = true ){
        	//Grab from cookie, if it exists
        	$this->set_cookies = $set_cookies == true;
        	if( $this->set_cookies ){
	        	if( !empty($_COOKIE['em_notices']) ) {
	        	    $notices = json_decode(base64_decode($_COOKIE['em_notices']), true);
	        	    if( is_array($notices) ){
						foreach( $notices as $type => $notice ) {
							// sanitize string and title indexes with esc_html
							foreach ( array('string', 'title') as $index ) {
								if ( !empty($notice[$index]) ) {
									$notices[$type][$index] = esc_html($notice[$index]);
								}
							}
						}
		        		$this->notices = $notices;
		        		setcookie('em_notices', '', time() - 3600, COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true); //unset the cookie
	        	    }
	        	}
        	}
            add_filter('wp_redirect', array(&$this,'destruct'), 1,1);
        }

        function destruct($redirect = false){
        	//Flush notices that weren't made to stay cross-requests, we can do this if initialized immediately.
        	foreach($this->notices as $notice_type => $notices){
        		foreach ($notices as $key => $notice){
        			if( empty($notice['static']) ){
        				unset($this->notices[$notice_type][$key]);
        			}else{
        				unset($this->notices[$notice_type][$key]['static']); //so it gets removed next request
        				$has_static = true;
        			}
        		}
        	}
        	if( $this->set_cookies ){
	            if(count($this->notices['errors']) > 0 || count($this->notices['alerts']) > 0 || count($this->notices['infos']) > 0 || count($this->notices['confirms']) > 0){
	            	setcookie('em_notices', base64_encode(json_encode($this->notices)), time() + 30, COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true); //sets cookie for 30 seconds, which may be too much
	            }
        	}
        	return $redirect;
        }

		function display(){
			echo $this;
		}

        function __toString(){
            $string = false;
            if(count($this->notices['errors']) > 0){
                $string .= "<div class='em-notice em-notice-error notice notice-error'>{$this->get_errors()}</div>";
            }
            if(count($this->notices['alerts']) > 0){
                $string .= "<div class='em-notice em-notice-warning notice notice-warning'>{$this->get_alerts()}</div>";
            }
            if(count($this->notices['infos']) > 0){
                $string .= "<div class='em-notice notice notice-info'>{$this->get_infos()}</div>";
            }
            if(count($this->notices['confirms']) > 0){
                $string .= "<div class='em-notice em-notice-success notice notice-success'>{$this->get_confirms()}</div>";
            }
            $this->displayed = true;
            return ($string !== false) ? "<div class='statusnotice'>".$string."</div>" : '';
        }

        /* General */
        function add($string, $type, $static = false){
        	if( is_array($string) ){
        		$result = true;
        		foreach($string as $key => $string_item){
        		    if( !is_array($string_item) ){
	        			if( $this->add($string_item, $type, $static) === false ){ $result = false; }
        			}else{
	        			if( $this->add_item($string_item, $type, $static) === false ){ $result = false; }
        			}
        		}
        		return $result;
        	}
            if($string != ''){
                return $this->add_item($string, $type, $static);
            }else{
                return false;
            }
        }

        function add_item($string, $type, $static = false){
            if( isset($this->notices[$type]) ){
            	$notice_key = 0;
            	foreach( $this->notices[$type] as $notice_key => $notice ){
            		if($string == $notice['string']){
            			return $notice_key;
            		}elseif( is_array($string) && !empty($notice['title']) && $this->get_array_title($string) == $notice['title'] ){
            		    return $notice_key;
            		}
            	}
            	$i = $notice_key+1;
            	if( is_array($string) ){
            		$this->notices[$type][$i]['title'] = $this->get_array_title($string);
					$this->notices[$type][$i]['string'] = array_shift($string);
            	}else{
		            $this->notices[$type][$i]['string'] = $string;
            	}
            	if( $static ){
            		$this->notices[$type][$i]['static'] = true;
            	}
            	return $i;
            }else{
            	return false;
            }
        }

        /**
         * Returns title of an array, assumes a assoc array with one item containing title => messages
         * @param unknown_type $array
         * @return unknown
         */
        function get_array_title($array){
            foreach($array as $title => $msgs)
            return $title;
        }

        function remove($key, $type){
            if( isset($this->notices[$type]) ){
                unset($this->notices[$type][$key]);
                return true;
            }else{
                return false;
            }
        }

        function remove_all(){
        	$this->notices = array('errors'=>array(), 'infos'=>array(), 'alerts'=>array(), 'confirms'=>array());
        }

        function get($type){
            if( isset($this->notices[$type]) ){
                $string = '';
                // @NOTE: GC Override: add .form__message markup
                // This seems to be only used for 'errors'?
                // Confirms are handled elsewhere...
                // ----------------------------------------------
                $state   = rtrim( $type, 's' ); // errorS => error, confirmS => confirm, etc.
                // if ( $state === 'alert' ) { $state = 'error'; }
                if ( $state === 'confirm' ) { $state = 'success'; }
                $string .= '<div class="form__message form__message--' . $state . '" aria-live="assertive" role="alert">';
                $string .= '<div class="form__message__header" tabindex="-1">';

                if ( $state === 'error' ) {
                    // There were errors
                    $count  = count( $this->notices[$type] );
                    $string .= sprintf(
                        _nx( 'In %d veld zijn de gegevens niet correct ingevuld', 'In %d velden zijn de gegevens niet correct ingevuld', $count, 'EM form validation error message', 'gctheme' ),
                        $count,
                    );
                } elseif ( $state === 'success' ) {
                    // Success
                    $string .= _x( 'Het formulier is succesvol verzonden!', 'EM form validation success message', 'gctheme' );
                } else {
                    // Generic?
                    $string .= _x( 'Let op', 'EM form validation generic message', 'gctheme' );
                }
                $string .= '</div>';
                $string .= '<ol class="form__message__body" role="list">';

                foreach ($this->notices[$type] as $message){
                        // ----------------------------------------------
                        // @NOTE: GC Override: change notice-element from <p> to <li>
                        // ----------------------------------------------
                        if( !is_array($message['string']) ){
                        if( preg_match('/<li>/', $message['string']) ){
                            $string .= $message['string'];
                        }else{
                            $string .= "<li>{$message['string']}</li>";
                        }
                    }else{
                        $string .= "<li><strong>".$message['title']."</strong><ol>";
                        foreach($message['string'] as $msg){
                            if( trim($msg) != '' ){
                            	$string .= "<li>$msg</li>";
                            }
                        }
	                    $string .= "</ol></li>";
                    }
                }
                // ----------------------------------------------
                // @NOTE: GC Override: add closing el for errors
                // ----------------------------------------------
                $string .= '</ol></div>';

                return $string;
            }
            return false;
        }

        function count($type){
       		if( isset($this->notices[$type]) ){
        		return count($this->notices[$type]);
            }
            return 0;
        }

        /* Errors */
        function add_error($string, $static=false){
            return $this->add($string, 'errors', $static);
        }
        function remove_error($key){
            return $this->remove($key, 'errors');
        }
        function get_errors(){
            return $this->get('errors');
        }
        function count_errors(){
            return $this->count('errors');
        }

        /* Alerts */
        function add_alert($string, $static=false){
            return $this->add($string, 'alerts', $static);
        }
        function remove_alert($key){
            return $this->remove($key, 'alerts');
        }
        function get_alerts(){
            return $this->get('alerts');
        }
        function count_alerts(){
            return $this->count('alerts');
        }

        /* Info */
        function add_info($string, $static=false){
            return $this->add($string, 'infos', $static);
        }
        function remove_info($key){
            return $this->remove($key, 'infos');
        }
        function get_infos(){
            return $this->get('infos');
        }
        function count_infos(){
            return $this->count('infos');
        }

        /* Confirms */
        function add_confirm($string, $static=false){
        	return $this->add($string, 'confirms', $static);
        }
        function remove_confirm($key){
            return $this->remove($key, 'confirms');
        }
        function get_confirms(){
            return $this->get('confirms');
        }
        function count_confirms(){
            return $this->count('confirms');
        }

        // Encoiding in JsonSerializable
	    #[\ReturnTypeWillChange]
	    function jsonSerialize(){
        	$notices = array();
	        foreach( $notices as $k => $v ){
	        	if( !empty($v) ){
	        		$notices[$k] = $v;
		        }
	        }
        	return $notices;
	    }

		//Iterator Implementation
	    #[\ReturnTypeWillChange]
	    function rewind(){
	        reset($this->bookings);
	    }
	    #[\ReturnTypeWillChange]
	    function current(){
	        $var = current($this->bookings);
	        return $var;
	    }
	    #[\ReturnTypeWillChange]
	    function key(){
	        $var = key($this->bookings);
	        return $var;
	    }
	    #[\ReturnTypeWillChange]
	    function next(){
	        $var = next($this->bookings);
	        return $var;
	    }
	    #[\ReturnTypeWillChange]
	    function valid(){
	        $key = key($this->bookings);
	        $var = ($key !== NULL && $key !== FALSE);
	        return $var;
	    }

    }
    function em_notices_init(){
	    global $EM_Notices;
	    $EM_Notices = new EM_Notices();
    }
    add_action('plugins_loaded', 'em_notices_init');
?>