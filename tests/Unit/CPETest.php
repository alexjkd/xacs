<?php
/**
 * Created by PhpStorm.
 * User: melon
 * Date: 2018/11/21
 * Time: 23:12
 */

namespace Tests\Unit;

use App\Models\CPE;
use App\Models\SoapAction;
use Tests\TestCase;
//use \Mockery as m;


class CPETest extends TestCase
{
    protected $cpe;

    public function setUp()
    {
        parent::setUp();
    }

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
     * @return array
     */
    public function testCpeGetInitialEvents($cpe)
    {

         $events = array(
            '0'=>SoapAction::EVENT_HTTP_AUTH,
            '1'=>SoapAction::EVENT_INFORM_BOOTSTRAP,
        );

        $initialEvents = $cpe->cpeGetInitialEvents();

        foreach ($events as $key=>$value)
        {
            $this->assertTrue(in_array($value,$initialEvents));
        }

        return $initialEvents;
    }

    /**
     * @depends testCpeCreate
     * @depends testCpeGetInitialEvents
     * @param CPE $cpe
     * @param array $initialEvents
     */
    public function testCpeGetActions($cpe, $initialEvents)
    {
        $actions = $cpe->cpeGetActions();

        foreach ($actions as $action)
        {
            $this->assertTrue(in_array($action->event,$initialEvents));
        }

        $this->artisan('migrate:refresh');
    }

    public function tearDown()
    {
        //m::close();
        parent::tearDown();
    }
}
