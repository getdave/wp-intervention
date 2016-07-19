<?php
/**
 * Class SampleTest.
 */

namespace WPIntervention;

use phpmock\phpunit\PHPMock;
use Intervention_Wrapper as Intervention_Wrapper;

class CoreTest extends \PHPUnit_Framework_TestCase
{
    use PHPMock;

    private $image_manager_stub;

    public function setUp()
    {
        // Setup basic Image Manager Stub to avoid calls to make
        $this->image_manager_stub = $this->getMockBuilder('\Intervention\Image\ImageManager')
            ->setMethods(array('make'))
            ->getMock();
    }

    public function test_global_function()
    {
        $this->assertTrue(function_exists('wp_intervention'), 'Global helper function defined');
    }

    public function test_proxies_manipulation_methods_to_intervention()
    {

        // Stub mime and save methods on Image instance
        $intervention_image_stub = $this->getMockBuilder('\Intervention\Image\Image')
            ->setMethods(array(
                'mime',
                'save',
                'blur',
                'fit',
            ))
            ->getMock();

        $intervention_image_stub
            ->expects($this->once())
            ->method('mime')
            ->will($this->returnValue('image/png'));

        $intervention_image_stub
            ->expects($this->once())
            ->method('save')
            ->will($this->returnValue('some_path_to_file_system'));

        $intervention_image_stub
            ->expects($this->once())
            ->method('blur')
            ->with($this->equalTo('30'))
            ->will($this->returnSelf());

        $intervention_image_stub
            ->expects($this->once())
            ->method('fit')
            ->with($this->equalTo(300, 200))
            ->will($this->returnSelf());

        // Pass mocked Image Manager 
        $this->image_manager_stub
            ->expects($this->any())
            ->method('make')
            ->will($this->returnValue($intervention_image_stub));

        $subject = new Intervention_Wrapper('fake_source', array(
            'blur' => 30,
            'fit' => [300, 200],
        ), array(
            'cache' => false,
        ));

        $subject->set_manager($this->image_manager_stub);

        $subject->process();
    }
}
