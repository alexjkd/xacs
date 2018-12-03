<?php

namespace Tests\Feature;

use App\Models\Facades\SoapFacade;
use Illuminate\Support\Facades\File;
use Tests\TestCase;
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
        SoapFacade::shouldReceive('ValidSoap')->andReturn(true);
    }

    public function testRequestNoAuth()
    {
        //$this->call($method, $uri, $parameters, $files, $server, $content);
        $response=$this->call('POST', 'http://xacs/tr069', [], [], [],
            [], 'xml');
        $response->assertStatus(401);
    }

    public function testRequestEmptyUser()
    {
        $response = $this->call('POST','http://xacs/tr069',[],[],[],
            ['HTTP_AUTHORIZATION'=>'Basic ' . base64_encode(':'),'PHP_AUTH_USER'=>'','PHP_AUTH_PW'=>''],'xml');
        $response->assertStatus(200);
    }

    public function testRequestSavedUser()
    {
        $mock_cpe = m::mock('App\Interfaces\ICpeContract');
        $mock_cpe->shouldReceive('cpeBlankUserAuth')->once()->andReturn(false);
        $mock_cpe->shouldReceive('cpeSavedUserAuth')->once()->andReturn(true);
        $this->app->instance('App\Interfaces\ICpeContract',$mock_cpe);

        $response = $this->call('POST','http://xacs/tr069',[],[],[],
            ['HTTP_AUTHORIZATION'=>'Basic ' . base64_encode('test:test'),
             'PHP_AUTH_USER'=>'test',
             'PHP_AUTH_PW'=>'test'], 'xml');
        $response->assertStatus(200);
    }

    public function tearDown()
    {
        m::close();
        parent::tearDown();

    }
}
