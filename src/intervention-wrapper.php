<?php

// import the Intervention Image Manager Class
use Intervention\Image\ImageManagerStatic as Image;


class WP_Intervention_Wrapper {

	private $src;
	private $intervention_args;
	private $options;

	private $cache_file_path;



	function __construct( $src=null, $intervention_args=array(), $options=array() ) {
		
		if ( empty( $src ) ) {
			return new WP_Error( 'No image source provided' );
		}

		// Main image src provided by user
		$this->src 					= $src;

		// Intervention args
		// image.intervention.io
		$this->intervention_args 	= $intervention_args;
		
		// Options
		$this->options 			= array_merge( array(
			'quality'		=> 80,
			'cache'			=> true
		), $options);


		// Set Image driver (gd by default)
		Image::configure( array(
			'driver' => apply_filters('wpi_driver', 'gd' )
		) );


		// Make the caching directory
		$this->make_cache_dir();
	}

	private function make_cache_dir() {
		wp_mkdir_p( static::get_cache_dir() );
	}


	public static function get_cache_dir() {
		$uploads_info = wp_upload_dir();
		$rtn = $uploads_info['basedir']  . '/intervention/cache/'; // TODO: automatically create cache dir at point of init

		// Allow overide by devs...
		return apply_filters( 'wpi_cache_directory', $rtn );
	}

	public static function get_cache_dir_uri() {
		$uploads_info = wp_upload_dir();
		$rtn = $uploads_info['basedir']  . '/intervention/cache/'; // TODO: automatically create cache dir at point of init

		// Allow overide by devs...
		return apply_filters( 'wpi_cache_directory', $rtn );
	}

	public function process() {

		// Init Intervention Library	
		$this->image = Image::make( $this->src );

		// Force setting of cache path for this file
		$this->set_cache_file_path();

		// If we have a cache of this then just return that directly
		if ( !$this->options['cache'] && $this->check_cache() ) {
			dump("FROM CACHE");
			return $this->get_cache_file_path();
		}	
		
		dump("NOT FROM CACHE");
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

		$this->cache_file_path = static::get_cache_dir() . $new_filename;
	}


	private function update_cache() {
		$rtn = $this->image->save( $this->get_cache_file_path(), $this->options['quality'] );

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



	

