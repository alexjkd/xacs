<?php
/**
 * Created by PhpStorm.
 * User: melon
 * Date: 2018/11/21
 * Time: 23:12
 */

namespace Tests\Unit;

use App\Models\Actions\BOOT;
use App\Models\Actions\BOOTSTRAP;
use App\Models\Actions\HTTP_AUTH;
use App\Models\Actions\SET_PARAMETER;
use App\Models\Facades\AcsFacade;
use App\Models\SoapActionEvent;
use App\Models\CPE;
use App\Models\Facades\SoapFacade;
use App\Models\SoapAction;

use Tests\TestCase;

class CPETest extends TestCase
{
    protected $cpe;

    public function setUp()
    {
        parent::setUp();
    }

    /**
     * @return CPE
     */
    public function testCpeCreate()
    {
        $this->artisan('migrate:refresh');

        $cpe_info = array(
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

        //$cpe = $this->app->build(CPE::class);
        $cpe = new CPE();
        $cpe->cpeCreate($cpe_info);
        $this->assertDatabaseHas('cpes', [
            'Manufacturer'=>'NETGEAR',
            'OUI'=>'08028E',
            'ProductClass'=>'V7610',
            'SerialNumber'=>'08028eef0b00'
        ]);
        return $cpe;
    }

    /**
     * @depends testCpeCreate
     * @param CPE $cpe
     */
    public function testCpeGetReadyActions($cpe)
    {
        $initialEvents = array(
            SoapActionEvent::HTTP_AUTH,
            SoapActionEvent::BOOTSTRAP,
            SoapActionEvent::BOOT,
        );

        $actions = $cpe->cpeGetReadyActions();
        foreach ($actions as $action)
        {
            $this->assertTrue(in_array($action->getAttribute('event'), $initialEvents));
        }
        //insert a action
        $action = new SET_PARAMETER();
        $cpe->action()->save($action);

        $action = $cpe->cpeGetReadyActions()->first();
        $this->assertTrue($action instanceof SET_PARAMETER);
        //delete a action
        //$action->delete();

        //$actions = $cpe->cpeGetReadyActions();
        //foreach ($actions as $action)
        //{
        //    $this->assertTrue(in_array($action->getAttribute('event'), $initialEvents));
        //}

    }
//------------------------------------------------------------------------------------
    /**
     * @depends testCpeCreate
     */
    public function testCpeCleanReadyActions()
    {
        $cpe = $this->testCpeCreate();

        $cpe->cpeCleanReadyActions();
        $actions = $cpe->cpeGetReadyActions();

        $this->assertTrue($actions->isEmpty());
    }
    /**
     * @depends testCpeCreate
     */
    public function testCpeInsertAction()
    {
        $cpe = $this->testCpeCreate();

        $authentication = 'Basic ' . base64_encode('insert:insert');
        $data = array('authentication'=> $authentication);
        $expected_data = json_encode(array('authentication'=> $authentication));

        $http_auth = new HTTP_AUTH(['data'=>$data]);
        $cpe->cpeInsertAction($http_auth);

        $this->assertDatabaseHas('soap_actions',[
            'fk_cpe_id'=>$cpe->getAttribute('id'),
            'event' => SoapActionEvent::HTTP_AUTH,
            'data'=> $expected_data,
        ]);
    }
    //TODO need to draw a flow to complete the cases for authentication
//--------- CPE Authentication Test --------------------------------------------------
    /**
     * @depends testCpeCreate
     */
    public function testCpeStartActionWithoutAuth()
    {
        $cpe = $this->testCpeCreate();

        $request = file_get_contents(base_path('tests/soap/INFORM_REQUEST.xml'));
        $expected_response = file_get_contents(base_path('tests/soap/INFORM_RESPONSE.xml'));
        AcsFacade::shouldReceive('acsGetCPEAuthable')->andReturn(false);
        AcsFacade::getFacadeRoot()->makePartial();

        $result = $cpe->cpeStartActionChain($request);

        $this->assertEquals(200,$result['code']);
        $this->assertEquals($expected_response, $result['content']);
    }

    /**
     * @depends testCpeCreate
     * @return CPE
     */
    public function testCpeLackOfAuth()
    {
        $cpe = $this->testCpeCreate();

        $request = file_get_contents(base_path('tests/soap/INFORM_REQUEST.xml'));

        $result = $cpe->cpeStartActionChain($request);
        $this->assertEquals(401,$result['code']);

        return $cpe;
    }
    /**
     * @depends testCpeLackOfAuth
     * @return CPE
     */
    public function testCpeStartActionWithBlankAuth(CPE $cpe)
    {
        $header = 'Basic ' . base64_encode(':');
        $request = file_get_contents(base_path('tests/soap/INFORM_REQUEST.xml'));

        $setparameter_cwmpid = "123456";
        AcsFacade::shouldReceive('acsGetCPEAuthable')->andReturn(false);
        AcsFacade::shouldReceive('acsGenerateCwmpdID')->andReturn($setparameter_cwmpid);
        AcsFacade::getFacadeRoot()->makePartial();

        $result = $cpe->cpeStartActionChain($request,$header);
        $this->assertEquals(200,$result['code']);

        return $cpe;
    }

    /**
     * @depends testCpeStartActionWithBlankAuth
     * @param CPE $cpe
     */
    public function testCpeActionAfterBlankAuth($cpe)
    {
        $actions = $cpe->cpeGetReadyActions()->first();

        $this->assertTrue($actions instanceof SET_PARAMETER);
    }

    /**
     * @depends testCpeCreate
     */
    public function testCpeStartActionWithInvalidAuth()
    {
        $cpe = $this->testCpeCreate();

        $header = 'Basic ' . base64_encode('test:test');
        $request = file_get_contents(base_path('tests/soap/INFORM_REQUEST.xml'));

        $result = $cpe->cpeStartActionChain($request,$header);
        $this->assertEquals(403,$result['code']);
    }
//--------- CPE Execute Action Test --------------------------------------------------
    /**
     * @depends testCpeCleanReadyActions
     * @depends testCpeInsertAction
     */
    public function testCpeBootStrapAction()
    {
        $expected_response = file_get_contents(base_path('tests/soap/INFORM_RESPONSE.xml'));
        $test_request = file_get_contents(base_path('tests/soap/INFORM_REQUEST.xml'));
        $expected_cwmpid = "1641837687";

        AcsFacade::shouldReceive('acsGetCPEAuthable')->andReturn(false);
        AcsFacade::getFacadeRoot()->makePartial();

        $cpe = $this->testCpeCreate();
        $cpe->cpeCleanReadyActions();

        $data = SoapFacade::ParseInformRequest($test_request);
        $bootstrap_action = new BOOTSTRAP($data);
        $cpe->cpeInsertAction($bootstrap_action);

        $result = $cpe->cpeStartActionChain($test_request);
        $this->assertEquals($expected_response, $result['content']);
        $this->assertDatabaseHas('soap_actions',[
            'fk_cpe_id'=>$cpe->getAttribute('id'),
            'request' =>$test_request,
            'response'=>$expected_response,
            'cwmpid' => $expected_cwmpid,
        ]);
    }
    /**
     * @depends testCpeInsertAction
     */
    public function testCpeBootAction()
    {
        $expected_response = file_get_contents(base_path('tests/soap/INFORM_RESPONSE.xml'));
        $test_request = file_get_contents(base_path('tests/soap/INFORM_REQUEST.xml'));
        $expected_cwmpid = "1641837687";

        AcsFacade::shouldReceive('acsGetCPEAuthable')->andReturn(false);
        AcsFacade::getFacadeRoot()->makePartial();


        $cpe = $this->testCpeCreate();

        $data = SoapFacade::ParseInformRequest($test_request);
        $boot = new BOOT(['data'=>$data]);
        $cpe->cpeInsertAction($boot);

        $result = $cpe->cpeStartActionChain($test_request);

        $this->assertEquals($expected_response, $result['content']);
        $this->assertDatabaseHas('soap_actions',[
            'fk_cpe_id'=>$cpe->getAttribute('id'),
            'request' =>$test_request,
            'response'=>$expected_response,
            'cwmpid' => $expected_cwmpid,
        ]);
    }

    /**
     * @depends testCpeInsertAction
     * @param CPE $cpe
     */
    public function testCpeSetParameterAction()
    {
        $response = file_get_contents(base_path('tests/soap/SET_PARAMETERS_RESPONSE.xml'));
        $expected_cwmpid = "123456";
        AcsFacade::shouldReceive('acsGetCPEAuthable')->andReturn(false);
        AcsFacade::shouldReceive('acsGenerateCwmpdID')->andReturn($expected_cwmpid);
        AcsFacade::getFacadeRoot()->makePartial();

        $data = array (
                'Device.ManagementServer.Username'=>'08028E-08028EEF0B00',
                'Device.ManagementServer.Password'=>'xwhSLiQAwOXlLeVX',
                'Device.ManagementServer.URL'=>'http://58.162.32.33/cwmp/cwmp'
                );
        $cpe = $this->testCpeCreate();
        $set_parameter = new SET_PARAMETER(['data'=>$data,'cwmpid'=>AcsFacade::acsGenerateCwmpdID()]);
        $cpe->cpeInsertAction($set_parameter);
        //todo notify ACS to sendout the request

        $result = $cpe->cpeStartActionChain($response);

        $this->assertDatabaseHas('soap_actions',[
            'fk_cpe_id'=>$cpe->getAttribute('id'),
            'response'=>$response,
            'cwmpid' => $expected_cwmpid,
        ]);

    }


    public function tearDown()
    {
        parent::tearDown();

    }
}
