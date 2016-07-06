<?php

class WP_Intervention
{
    /**
     * The basename of the plugin.
     *
     * @var string
     */
    private $basename;
 
    
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
        
    }

    private function add_actions() {
    	add_action( 'wpi_clean_cache', array( $this, 'remove_outdated_cache_files' ) );
    }
    
	// Cron Tab to remove outdated cache files
	public function remove_outdated_cache_files() {

		dump("Clearing cached");
		
		// TODO - make cache dir a filterable option on this core class not on the Lib Wrapper
		$dir = WP_Intervention_Wrapper::get_cache_dir();

		/*** cycle through all files in the directory ***/
		foreach (glob($dir."*") as $file) {

			/*** if file is 24 hours (86400 seconds) old then delete it ***/
			if (filemtime($file) < time() - apply_filters('wpi_clean_outdate_cache_files_period', 86400 ) ) {
			    unlink($file);
			}
		}	
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


