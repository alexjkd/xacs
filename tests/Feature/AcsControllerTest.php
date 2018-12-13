<?php

namespace Tests\Feature;

use App\Models\Facades\SoapFacade;
use App\Models\SoapAction;
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
        SoapFacade::shouldReceive('GetSoapType')->andReturn(SoapAction::EVENT_INFORM_BOOTSTRAP);
        SoapFacade::getFacadeRoot()->makePartial();

        //SoapFacade::shouldReceive('ParseInformRequest')->andReturnSelf();
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
        $this->artisan('migrate:refresh');

        $inform_request = file_get_contents(base_path('tests/soap/INFORM_REQUEST.xml'));
        $response = $this->call('POST','http://xacs/tr069',[],[],[],
            ['HTTP_AUTHORIZATION'=>'Basic ' . base64_encode(':'),
             'PHP_AUTH_USER'=>'',
             'PHP_AUTH_PW'=>''],$inform_request);

        $response->assertStatus(200);
    }

    /**
     * @depends testRequestEmptyUser
     */
    public function testRequestSavedUser()
    {
        $response = $this->call('POST','http://xacs/tr069',[],[],[],
            ['HTTP_AUTHORIZATION'=>'Basic ' . base64_encode('V7610:08028eef0b00'),
             'PHP_AUTH_USER'=>'V7610',
             'PHP_AUTH_PW'=>'08028eef0b00'], 'xml');
        $response->assertStatus(200);
        $this->artisan('migrate:refresh');
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
