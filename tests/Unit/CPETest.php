<?php
/**
 * Created by PhpStorm.
 * User: melon
 * Date: 2018/11/21
 * Time: 23:12
 */

namespace Tests\Unit;

use App\Models\Facades\AcsFacade;
use App\Models\SoapActionStage;
use App\Models\SoapActionStatus;
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
/*
    public function testCpeLogin()
    {
        $this->cpe = factory('App\Models\CPE')->create([
            'ConnectionRequestUser'=>'test'
        ]);

        $user_test = array(
            'name'=>'test',
            'password'=>'secret',
            'authentication'=>'Basic '. base64_encode('test:test'),
        );
        $user_invalid=array(
            'name'=>'test',
            'password'=>'secret1',
            'authentication'=>'Basic '. base64_encode('test:test'),
        );

        $this->assertEquals(CPE::STATUS_SUCCEEDED, $this->cpe->cpeLogin($user_test));
        $this->assertEquals(CPE::STATUS_FAILED, $this->cpe->cpeLogin($user_invalid));
    }
*/
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

        $cpe = $this->app->build(CPE::class);

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
        $action = new SoapAction();
        $action->setAttribute('event',SoapActionEvent::SET_PARAMETER);
        $cpe->action()->save($action);

        $action = $cpe->cpeGetReadyActions()->first();
        $this->assertEquals($action->getAttribute('event'),SoapActionEvent::SET_PARAMETER);
        //delete a action
        $cpe->action()->where('event',SoapActionEvent::SET_PARAMETER)->delete();

        $actions = $cpe->cpeGetReadyActions();
        foreach ($actions as $action)
        {
            $this->assertTrue(in_array($action->getAttribute('event'), $initialEvents));
        }

    }

    /**
     * @depends testCpeCreate
     * @param CPE $cpe
     */
    public function testCpeDoAction($cpe)
    {
        $expected_bootstrap = file_get_contents(base_path('tests/soap/INFORM_RESPONSE.xml'));
        $test_request = file_get_contents(base_path('tests/soap/INFORM_REQUEST.xml'));

        $initial_actions = array(
            SoapActionEvent::BOOTSTRAP => array(
                'test_request'=>$test_request,
                'expected'=>$expected_bootstrap,
            ),
            SoapActionEvent::BOOT => array(
                'test_request'=>$test_request,
                'expected'=>$expected_bootstrap,
            ),
        );
        $action = new SoapAction();
        $action->setAttribute('event',SoapActionEvent::BOOTSTRAP);
        $cpe->cpeInsertActions($action);

        $action = new SoapAction();
        $action->setAttribute('event',SoapActionEvent::BOOT);
        $cpe->cpeInsertActions($action);

        $actions = $cpe->cpeGetReadyActions();

        foreach ($actions as $action)
        {
            foreach ($initial_actions as $key=>$value)
            {
                if ($action->getAttribute('event') === $key)
                {
                    $action->setAttribute('request', $value['test_request']);
                    $action->setAttribute('data',
                        json_encode(SoapFacade::ParseInformRequest($value['test_request'])));
                    $result = $cpe->cpeDoAction($action);

                    $this->assertTrue(SoapFacade::ValidSoap($value['expected']));
                    $this->assertEquals($value['expected'],$result['content']);
                    $this->assertDatabaseHas('soap_actions',[
                        'fk_cpe_id'=>$cpe->getAttribute('id'),
                        'request' =>$value['test_request'],
                        'response'=>$result['content'],
                    ]);
                }
            }
        }
    }
//-----------------------------------------------------------
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
    public function testCpeStartActionWithBlankAuth($cpe)
    {
        $header = 'Basic ' . base64_encode(':');
        $request = file_get_contents(base_path('tests/soap/INFORM_REQUEST.xml'));

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

        $this->assertEquals(SoapActionEvent::SET_PARAMETER,
            $actions->getAttribute('event'));
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

    public function tearDown()
    {
        parent::tearDown();

    }
}
