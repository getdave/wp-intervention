<?php

// import the Intervention Image Manager Class
use Intervention\Image\ImageManager;


class Intervention_Wrapper {

	private $src;
	private $intervention_args;
	private $options;

	private $cache_file_path;

	private $intervention_instance;

	private $manager;



	function __construct( $src=null, $intervention_args=array(), $options=array() ) {
		
		if ( empty( $src ) ) {
			return new WP_Error( 'No image source provided' );
		}

		// Main image src provided by user
		$this->src 					= $src;

		// Intervention args
		// image.intervention.io
		$this->intervention_args 	= $intervention_args;
		
		// Allow defaults to be overriden on a global basis
		$default_options = apply_filters('wpi_default_options', array(
			'quality'		=> 80,
			'cache'			=> true
		));

		// Set the options
		$this->options 	= array_merge( $default_options, $options);


		
	}




	

	public function process() {

		// Init Intervention Library	
		$this->intervention_instance = $this->manager->make( $this->src );



		// Cache the setting of the cache path 
		$this->set_cache_file_path();

		// If we have a cache of this image then just return that directly
		if ( $this->options['cache'] && $this->check_cache() ) {
			//dump("FROM CACHE");	
			return $this->get_cache_file_path( 'uri' ); // return a URI not a DIR
		}	
		
		//dump("NOT FROM CACHE");

		// Proxy all args to underlying Intevention library
		// note: args will be called in order defined in the 
		// $intervention_args array
		foreach ($this->intervention_args as $sFn => $aFnArgs)
		{
			if (!is_array($aFnArgs))
			{
				$aFnArgs = [ $aFnArgs ];
			}

			call_user_func_array([$this->intervention_instance, $sFn], $aFnArgs);
		}

		// Save resulting file to cache dir
		$this->save_image();
		
		// Return a public URL to the image
		return $this->get_cache_file_path( 'uri' );
	
	}


	private function check_cache() {	
		$cache_file_path = $this->get_cache_file_path();

		if ( file_exists( $cache_file_path ) ) {
			// Update the timestamp on the file to show it's been accessed
			touch( $cache_file_path );		
			return $cache_file_path;
		}
	}

	private function get_cache_file_path( $type='directory' ) {

		$cache_file_path;		

		if ( empty( $this->cache_file_path ) ) {
			$this->set_cache_file_path();

		} 

		$cache_file_path = $this->cache_file_path;
		
		// TODO: account for situations where user has filtered the cache path
		// we can't reply on str_replace or upload dir...
		if ( $type === 'uri' ) {
			$upload_dir = WP_Intervention::upload_dir();
			$cache_file_path = str_replace( $upload_dir['basedir'], $upload_dir['baseurl'], $this->cache_file_path );
		}

		return $cache_file_path;
	}

	private function set_cache_file_path() {
		$args = $this->intervention_args;

		// Sort the array by key to ensure consistency of caching filename
		ksort($args);

		$ext = $this->get_extension();

		$file_pathinfo = pathinfo($this->src);

		$new_filename = $file_pathinfo['filename'] . '-' . hash('md5', $this->r_implode( $args, '-') ) . $ext;

		$this->cache_file_path = WP_Intervention::get_cache_dir() . $new_filename;
	}


	private function save_image() {
		$rtn = $this->intervention_instance->save( $this->get_cache_file_path(), $this->options['quality'] );
		return $rtn;
	}


	private function get_extension() {
		$mime = $this->intervention_instance->mime();
		return "." . str_replace('image/', '', $mime);
	}

	public function set_manager( $alt_manager=null ) {

		if( !empty( $alt_manager) ) { // allow to overide dependency via setter injection
			$this->manager = $alt_manager;
		} else {
			// Set Image driver (gd by default)
			$this->manager = new ImageManager( array(
				'driver' => apply_filters('wpi_driver', 'gd' )
			) );
		}
	}

	
	/**
	 * RECURSIVE IMPLODE
	 * @param  array $pieces the array to implode
	 * @param  string $glue   the string to used when combining the array
	 * @return string         the imploded array
	 */
	private function r_implode( $pieces, $glue ) {
		
	  	$retVal = array_map(function($item) use ($glue) {

	  		// Handle PHP CLosures which cannot be converted into a string
	  		if ( is_callable ( $item ) ) {
	  			$item = 'closure';
	  		}

	  		if( is_array( $item ) ) {
	  			return $this->r_implode( $item, $glue );
	  		} else {
	  			return $item;
	  		}
	  	}, $pieces);

	  	return implode( $glue, $retVal );
	} 



}



	

