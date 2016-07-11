<?php
/**
 * Class SampleTest
 *
 * @package 
 */

namespace WPIntervention;

use phpmock\phpunit\PHPMock;

use \Intervention_Wrapper as Intervention_Wrapper;
 
class CoreTest extends \PHPUnit_Framework_TestCase
{
    use PHPMock;

  
    public function test_global_function()
    {
        $this->assertTrue( function_exists( 'wp_intervention' ), 'Global helper function defined' );
    }

    public function test_mocking() {

    	// Stub mime and save methods on Image instance
    	$intervention_instance_stub = $this->getMockBuilder('\Intervention\Image\Image')
    	    ->setMethods(array('mime','save'))    				
    	    ->getMock();

    	$intervention_instance_stub
    	    ->expects( $this->once() )
            ->method('mime')
            ->will( $this->returnValue( 'image/png' ) );

        $intervention_instance_stub
    	    ->expects( $this->once() )
            ->method('save')
            ->will( $this->returnValue( 'some_path_to_file_system' ) );
		

    	
    	$image_manager_stub = $this->getMockBuilder('\Intervention\Image\ImageManager')
			->setMethods(array('make'))    				
            ->getMock();

    	$image_manager_stub
    	    ->expects( $this->any() )
            ->method('make')
            ->will( $this->returnValue( $intervention_instance_stub ) );  
        
        
    	

        $subject = new Intervention_Wrapper( 'fake_source', array(), array(
        	'cache' => false
        ) ); 

		$subject->set_manager( $image_manager_stub );

		$subject->process();
    	
    }
}

