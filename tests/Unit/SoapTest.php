<?php
/**
 * Created by PhpStorm.
 * User: melon
 * Date: 2018/11/23
 * Time: 16:14
 */

namespace Tests\Unit;

use App\Models\SoapEngine;
use App\Models\Facades\SoapFacade;
use Tests\TestCase;
use \Mockery as m;

class SoapTest extends TestCase
{
    protected $cpe;
    protected $datamodel;

    public function setUp()
    {
        parent::setUp();
        $this->cpe = m::mock('App\Interfaces\ICpeContract');
        $this->app->instance('App\Interfaces\ICpeContract',$this->cpe );

        $this->datamodel = m::mock('App\Interfaces\IDataModelContract');
        $this->app->instance('App\Interface\IDataModelContract',$this->datamodel);
    }

    public function testSoapBuildParameterStruct()
    {
        $this->datamodel->shouldReceive('dataGetType')->andReturn('xstns:string');

        $data = array (
            'Device.ManagementServer.Username'=>'08028E-08028EEF0B00',
            'Device.ManagementServer.Password'=>'xwhSLiQAwOXlLeVX',
            'Device.ManagementServer.URL'=>'http://58.162.32.33/cwmp/cwmp'
        );
        $engine = new SoapEngine();
        $soap = $engine->soapBuildParameterStruct($data, $this->datamodel);
        $this->assertTrue(SoapFacade::ValidSoap($soap));
        return $soap;
    }

    /**
     * @param $struct
     * @depends testSoapBuildParameterStruct
     */
    public function testSoapGetDataFromParameterStruct($struct)
    {
        $data = array (
            'Device.ManagementServer.Username'=>'08028E-08028EEF0B00',
            'Device.ManagementServer.Password'=>'xwhSLiQAwOXlLeVX',
            'Device.ManagementServer.URL'=>'http://58.162.32.33/cwmp/cwmp'
        );
        $engine = new SoapEngine();
        $this->assertEquals($data,$engine->soapGetDataFromParameterStruct($struct));
    }

    public function tearDown()
    {
        $this->artisan('migrate:refresh');
        m::close();
        parent::tearDown();
    }
}
