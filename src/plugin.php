<?php

class WP_Intervention
{
    /**
     * The basename of the plugin.
     *
     * @var string
     */
    private $basename;



    private static $cache_dir;

    private static $wp_upload_dir;
 
    
    /**
     * Constructor.
     *
     * @param string $file
     */
    public function __construct($file)
    {
        $this->basename = plugin_basename($file);
        $this->loaded = false;    
    }

    /**
     * Loads the plugin into WordPress.
     * Only allows this to happen once.
     */
    public function load()
    {
        if ($this->loaded) {
            return;
        }
 
        $this->loaded = true;

        // Add actions...
        $this->add_actions();

        // Create the cache dir if it doesn't exist
        $this->make_cache_dir();    

    }

    private function add_actions() {
    	add_action( 'wpi_clean_cache', array( $this, 'remove_outdated_cache_files' ) );

    	add_action( 'switch_blog', array( $this, 'clear_upload_dir_cache' ) );
        
    }



    public static function get_cache_dir() {

    	if ( empty( static::$cache_dir ) ) {

			$uploads_info = wp_upload_dir();
			$rtn = $uploads_info['basedir']  . '/intervention/cache/'; // TODO: automatically create cache dir at point of init

			// Allow overide by devs...
			static::$cache_dir = apply_filters( 'wpi_cache_directory', $rtn );
		}

		return static::$cache_dir;
	}



	private function make_cache_dir() {
		wp_mkdir_p( static::get_cache_dir() );
	}



    
	// Cron Tab to remove outdated cache files
	public function remove_outdated_cache_files() {
	
		// TODO - make cache dir a filterable option on this core class not on the Lib Wrapper
		$dir = static::get_cache_dir();

		/*** cycle through all files in the directory ***/
		foreach (glob($dir."*") as $file) {

			/*** if file is 24 hours (86400 seconds) old then delete it ***/
			if (filemtime($file) < time() - apply_filters('wpi_clean_outdate_cache_files_period', 86400 ) ) {
			    unlink($file);
			}
		}	
	}


	public static function upload_dir() {
		if ( empty( self::$wp_upload_dir ) ) {
			self::$wp_upload_dir = wp_upload_dir();
		}
		return self::$wp_upload_dir;
	}

	public static function clear_upload_dir_cache() {
		self::$wp_upload_dir = null;
	}

	public static function activated() 
    {
        wp_schedule_event( time(), apply_filters('wpi_clean_outdate_cache_files_cron_recurrance', 'hourly' ), 'wpi_clean_cache');
    }

    public static function deactivated() 
    {
        wp_clear_scheduled_hook( 'wpi_clean_cache' );
    }

}


