<?php
/*
Plugin Name: Intervention Image Manipulation
Plugin URI: http://wordpress.org/plugins/hello-dolly/
Description: Test
Author: Mr Foo
Version: 0.1
Author URI: http://www.wordpress.org
*/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Composer Autoloader
require_once __DIR__ . '/vendor/autoload.php';

// import the Intervention Image Manager Class
use Intervention\Image\ImageManagerStatic as Image;


class WP_Intervention {

	private $src;
	private $intervention_args;
	private $plugin_args;

	private $cache_file_path;



	function __construct( $src=null, $intervention_args=array(), $plugin_args=array() ) {
		
		if ( empty( $src ) ) {
			return new WP_Error( 'No image source provided' );
		}

		// Main image src provided by user
		$this->src 					= $src;
		$this->intervention_args 	= $intervention_args;
		
		// Set Plugin Args
		$this->plugin_args 			= array_merge( array(
			'driver' 		=> 'gd',
			'cache_dir' 	=> static::get_cache_dir(),
			'quality'		=> 80,
			'cache'			=> true
		), $plugin_args);


		// Set Image driver (gd by default)
		Image::configure( array(
			'driver' => $this->plugin_args['driver']
		) );

		// Make the caching directory
		$this->make_cache_dir();
	}

	private function make_cache_dir() {
		wp_mkdir_p( $this->plugin_args['cache_dir'] );
	}


	public static function get_cache_dir() {
		$uploads_info = wp_upload_dir();
		return $uploads_info['basedir']  . '/intervention/cache/'; // TODO: automatically create cache dir at point of init
	}

	public function process() {

		// Init Intervention Library	
		$this->image = Image::make( $this->src );

		// Force setting of cache path for this file
		$this->set_cache_file_path();

		// If we have a cache of this then just return that directly
		if ( !$this->plugin_args['cache'] && $this->check_cache() ) {
			dump("NOT CACHING");
			return $this->get_cache_file_path();
		}	
		
		dump("CACHING");
		foreach ($this->intervention_args as $sFn => $aFnArgs)
		{
			if (!is_array($aFnArgs))
			{
				$aFnArgs = [ $aFnArgs ];
			}

			call_user_func_array([$this->image, $sFn], $aFnArgs);
		}

		return $this->update_cache();

	
	}


	private function check_cache() {	
		$cache_file_path = $this->get_cache_file_path();

		if ( file_exists( $cache_file_path ) ) {
			// Update the timestamp on the file to show it's been accessed
			touch( $cache_file_path );		
			return $cache_file_path;
		}
	}

	private function get_cache_file_path() {

		if ( empty($this->cache_file_path) ) {
			$this->set_cache_file_path();
		} 

		return $this->cache_file_path;
	}

	private function set_cache_file_path() {
		$args = $this->intervention_args;

		// Sort the array by key to ensure consistency of caching filename
		ksort($args);

		$ext = $this->get_extension();

		$file_pathinfo = pathinfo($this->src);

		$new_filename = $file_pathinfo['filename'] . '-' . hash('md5', $this->r_implode( $args, '-') ) . $ext;

		$this->cache_file_path = $this->plugin_args['cache_dir'] . $new_filename;
	}


	private function update_cache() {
		$rtn = $this->image->save( $this->get_cache_file_path(), $this->plugin_args['quality'] );

		dump($rtn);

		return $rtn;
	}


	private function get_extension() {
		$mime = $this->image->mime();
		return "." . str_replace('image/', '', $mime);
	}

	
	/**
	 * RECURSIVE IMPLODE
	 * @param  array $pieces the array to implode
	 * @param  string $glue   the string to used when combining the array
	 * @return string         the imploded array
	 */
	private function r_implode( $pieces, $glue ) {
		
	  	$retVal = array_map(function($item) use ($glue) {
	  		if( is_array( $item ) ) {
	  			return $this->r_implode( $item, $glue );
	  		} else {
	  			return $item;
	  		}
	  	}, $pieces);

	  	return implode( $glue, $retVal );
	} 



}


/**
 * WP INTERVENTION GLOBAL HELPER
 */
function wp_intervention( $src=null, $intervention_args=array(), $plugin_args=array() ) {
	$wp_intervention = new WP_Intervention( $src, $intervention_args, $plugin_args ); 
	return $wp_intervention->process();

	
}



// Cron Tab to remove outdated cache files
function wpi_remove_outdated_cache() {
	/** define the directory **/
	$dir = WP_Intervention::get_cache_dir();

	/*** cycle through all files in the directory ***/
	foreach (glob($dir."*") as $file) {

		/*** if file is 24 hours (86400 seconds) old then delete it ***/
		if (filemtime($file) < time() - 60) {
		    unlink($file);
		}
	}	
}
add_action( 'wpi_clean_cache', 'wpi_remove_outdated_cache' );

if ( ! wp_next_scheduled( 'wpi_clean_cache' ) ) {
	wp_schedule_event( time(), 'hourly', 'wpi_clean_cache' ); // TODO: make configurable
}

	

