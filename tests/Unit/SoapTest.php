<?php
/**
 * Created by PhpStorm.
 * User: melon
 * Date: 2018/11/23
 * Time: 16:14
 */

namespace Tests\Unit;

use App\Models\SoapActionEvent;
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

        $this->datamodel = m::mock('App\Interfaces\IDataModelContract');
        $this->app->instance('App\Interface\IDataModelContract',$this->datamodel);
    }

    /**
     * @return array
     */
    public function testSoapParseInformRequest()
    {
        $soap = file_get_contents(base_path('tests/soap/INFORM_REQUEST.xml'));
        $this->assertTrue(SoapFacade::ValidSoap($soap));

        $expected_data = array(
            'ID'=>1641837687,
            'DeviceId'=> array(
                'Manufacturer'=>'NETGEAR',
                'OUI'=>'08028E',
                'ProductClass'=>'V7610',
                'SerialNumber'=>'08028eef0b00',
            ),
            'EventStruct'=>array(
                'BOOTSTRAP'=>0,
                'BOOT'=>1,
            ),
            'MaxEnvelopes'=>1,
            'CurrentTime'=>'2018-06-26T13:19:13+00:00',
            'RetryCount'=>0,
            'ParameterList'=>array(
                'Device.RootDataModelVersion'=>'',
                'Device.DeviceInfo.HardwareVersion'=>'V7610A',
                'Device.DeviceInfo.SoftwareVersion'=>'V2.2.2.26_ST2',
                'Device.DeviceInfo.ProvisioningCode'=>'Telstra1',
                'Device.ManagementServer.ParameterKey'=>'(null)',
                'Device.ManagementServer.ConnectionRequestURL'=>
                    'http://79.0.0.179:7547/bf93a276cc0501c7161c29beb4c32b7d',
                'Device.X_00600F_wansupervision.ActiveWANInterface'=>'Ethernet IPoE',
                'Device.X_00600F_wansupervision.MBBUSBDetected'=>0,
            ),
        );

        $data = SoapFacade::ParseInformRequest($soap);

        $this->assertEquals($expected_data, $data);

        return $data;
    }

    /**
     * @param array $data
     * @depends testSoapParseInformRequest
     */
    public function testSoapBuildInformResponse($data)
    {
        $expected_response = file_get_contents(base_path('tests/soap/INFORM_RESPONSE.xml'));
        $this->assertTrue(SoapFacade::ValidSoap($expected_response));

        $response = SoapEngine::BuildInformResponse($data['ID']);
        $this->assertEquals($expected_response, $response);
    }

    public function testSoapBuildGetParameterRequest()
    {
        $expected_getparameter = file_get_contents(base_path('tests/soap/GET_PARAMETER.xml'));
        $this->assertTrue(SoapFacade::ValidSoap($expected_getparameter));

        $parameter = array(
            0 => 'Device.Time.',
        );

        $getparameter = SoapFacade::BuildGetParameterRequest($parameter);
        $this->assertEquals($expected_getparameter, $getparameter);
    }

    /**
     * @depends testSoapBuildGetParameterRequest
     */
    public function testSoapParseGetParameterResponse()
    {
        $soap = file_get_contents(base_path('tests/soap/GET_PARAMETER_RESPONSE.xml'));
        $this->assertTrue(SoapFacade::ValidSoap($soap));

        $expected_data = array(
            'ID'=>1,
            'ParameterList'=> array(
                'Device.Time.Enable'=>1,
                'Device.Time.Status'=>'Synchronized',
                'Device.Time.NTPServer1'=>'Tic.ntp.telstra.net',
                'Device.Time.NTPServer2'=>'0.au.pool.ntp.org',
                'Device.Time.NTPServer3'=>'',
                'Device.Time.NTPServer4'=>'',
                'Device.Time.NTPServer5'=>'',
                'Device.Time.CurrentLocalTime'=>'2018-06-26T13:19:34+00:00',
                'Device.Time.LocalTimeZone'=>'AEST-1000',
                'Device.Time.X_BROADCOM_COM_LocalTimeZoneName'=>'Canberra, Melbourne, Sydney',
                'Device.Time.X_BROADCOM_COM_DaylightSavingsUsed'=>0,
                'Device.Time.X_BROADCOM_COM_DaylightSavingsStart'=>'2018-06-26T13:19:34+00:00',
                'Device.Time.X_BROADCOM_COM_DaylightSavingsEnd'=>'2018-06-26T13:19:34+00:00',
                'Device.Time.IsManual'=>0,
                'Device.Time.ManualDateTimeStr'=>'',
                'Device.Time.from_month'=>10,
                'Device.Time.from_week'=>1,
                'Device.Time.from_weekday'=>0,
                'Device.Time.from_day'=>0,
                'Device.Time.from_hour'=>2,
                'Device.Time.to_month'=>4,
                'Device.Time.to_week'=>1,
                'Device.Time.to_weekday'=>0,
                'Device.Time.to_day'=>0,
                'Device.Time.to_hour'=>2,
                'Device.Time.dst_offset'=>0,
                'Device.Time.from_minute'=>0,
                'Device.Time.to_minute'=>0,
            ),
        );

        $actual_data = SoapFacade::ParseGetParameterResponse($soap);

        $this->assertEquals($expected_data,$actual_data);
    }

    public function testSoapBuildSetParameterRequest()
    {
        $this->datamodel->shouldReceive('dataGetType')->andReturn('xstns:string');

        $data = array(
            'cwmpid'=>1234567,
            'values'=> [
            'Device.ManagementServer.Username'=>'08028E-08028EEF0B00',
            'Device.ManagementServer.Password'=>'xwhSLiQAwOXlLeVX',
            'Device.ManagementServer.URL'=>'http://58.162.32.33/cwmp/cwmp'
        ]);

        $soap = SoapFacade::BuildSetParameterRequest($data, $this->datamodel);
        $this->assertTrue(SoapFacade::ValidSoap($soap));
        return $soap;
    }

    /**
     * @depends testSoapBuildSetParameterRequest
     */
    public function testSoapParseSetParameterResponse()
    {
        $soap = file_get_contents(base_path('tests/soap/SET_PARAMETERS_RESPONSE.xml'));
        $this->assertTrue(SoapFacade::ValidSoap($soap));
        $expected_result = array(
            'cwmpid' => "123456",
            'status' => SoapEngine::STATUS_OK,
        );

        $result = SoapFacade::ParseSetParameterResponse($soap);

        $this->assertEquals($expected_result,$result);
    }

    public function testSoapGetSoapType()
    {
        $soap = file_get_contents(base_path('tests/soap/INFORM_REQUEST.xml'));
        $this->assertTrue(SoapFacade::ValidSoap($soap));

        $type = SoapFacade::GetSoapType($soap);
        $expected_str = json_encode(array(SoapActionEvent::fromString("BOOTSTRAP"),
            SoapActionEvent::fromString("BOOT")));
        $type_json = json_encode($type);
        $this->assertEquals($expected_str, $type_json);
    }

    public function tearDown()
    {
        $this->artisan('migrate:refresh');
        m::close();
        parent::tearDown();
    }
}
