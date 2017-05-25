<?php

/**
 * WP INTERVENTION GLOBAL HELPER
 */
if ( !function_exists('wp_intervention') ) {
	function wp_intervention( $src=null, $intervention_args=array(), $plugin_args=array(), $return_instance=false ) {
		$wp_intervention = new Intervention_Wrapper( $src, $intervention_args, $plugin_args ); 
		return $wp_intervention->process($return_instance);
	}	
}


