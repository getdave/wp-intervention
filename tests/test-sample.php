<?php
/**
 * Class SampleTest
 *
 * @package 
 */

namespace WPIntervention;

use phpmock\phpunit\PHPMock;
 
class CoreTest extends \PHPUnit_Framework_TestCase
{
    use PHPMock;
 
    public function test_global_function()
    {
        $this->assertTrue( function_exists( 'wp_intervention' ), 'Global helper function defined' );
    }
}

