<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\App;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use \Mockery as m;

class AcsControllerTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
    }

    public function testCpeLogin()
    {
        $mock = m::mock('App\Interfaces\ICpeContract');
        $this->app->instance('App\Interfaces\ICpeContract',$mock);

        $mock->shouldReceive('cpeBlankUserAuth')->once()->andReturn(false);
        $mock->shouldReceive('cpeSavedUserAuth')->once()->andReturn(false);
        $response = $this->post('http://xacs/tr069');
        $response->assertStatus(401);

        $mock->shouldReceive('cpeBlankUserAuth')->once()->andReturn(true);
        $response = $this->post('http://xacs/tr069');
        $response->assertStatus(200);


        $mock->shouldReceive('cpeBlankUserAuth')->once()->andReturn(false);
        $mock->shouldReceive('cpeSavedUserAuth')->once()->andReturn(true);
        $response = $this->post('http://xacs/tr069');
        $response->assertStatus(200);

    }

    public function testInvalid()
    {

    }
    public function tearDown()
    {
        parent::tearDown();
        m::close();
    }
}
