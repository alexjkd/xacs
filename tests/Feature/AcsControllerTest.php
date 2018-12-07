<?php

namespace Tests\Feature;

use App\Models\CPE;
use App\Models\Facades\SoapFacade;
use App\Models\SoapEngine;
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
        SoapFacade::shouldReceive('GetSoapType')->andReturn(SoapEngine::INFROM_BOOTSTRAP);
    }

    public function testRequestNoAuthHeader()
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
        $mock_cpe = m::mock('App\Interfaces\ICpeContract')->makePartial();
        $mock_cpe->shouldReceive('cpeLogin')->andReturn(CPE::STATUS_SUCCEEDED);
        $this->app->instance('App\Interfaces\ICpeContract',$mock_cpe);

        $response = $this->call('POST','http://xacs/tr069',[],[],[],
            ['HTTP_AUTHORIZATION'=>'Basic ' . base64_encode('test:test'),
             'PHP_AUTH_USER'=>'test',
             'PHP_AUTH_PW'=>'test'], 'xml');
        $response->assertStatus(200);
    }

    public function testRequestNoContent()
    {
        $response = $this->call('POST','http://xacs/tr069',[],[],[],
            ['HTTP_AUTHORIZATION'=>'Basic ' . base64_encode(':'),'PHP_AUTH_USER'=>'','PHP_AUTH_PW'=>'']);
        $response->assertStatus(403);

        $response = $this->call('POST','http://xacs/tr069');
        $response->assertStatus(403);
    }

    public function tearDown()
    {
        m::close();
        parent::tearDown();

    }
}
