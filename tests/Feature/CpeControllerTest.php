<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\App;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use \Mockery as m;

class CpeControllerTest extends TestCase
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

    public function testConectionRequest()
    {
        $mock = m::mock('App\Interfaces\ICpe');
        $this->app->instance('App\Interfaces\ICpe',$mock);

        $mock->shouldReceive('cpeBlankUserAuth')->once()->andReturn(true);
        $response = $this->post('http://xacs/tr069');
        $response->assertStatus(200);


        $mock->shouldReceive('cpeBlankUserAuth')->once()->andReturn(false);
        $mock->shouldReceive('cpeSavedUserAuth')->once()->andReturn(true);
        $response = $this->post('http://xacs/tr069');
        $response->assertStatus(200);

        $mock->shouldReceive('cpeBlankUserAuth')->once()->andReturn(false);
        $mock->shouldReceive('cpeSavedUserAuth')->once()->andReturn(false);
        $response = $this->post('http://xacs/tr069');
        $response->assertStatus(401);
    }

    public function tearDown()
    {
        parent::tearDown();
        m::close();
    }


}
