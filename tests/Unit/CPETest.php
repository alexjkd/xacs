<?php
/**
 * Created by PhpStorm.
 * User: melon
 * Date: 2018/11/21
 * Time: 23:12
 */

namespace Tests\Unit;

use App\Models\CPE;
use Tests\TestCase;
use \Mockery as m;


class CPETest extends TestCase
{
    protected $cpe;

    public function setUp()
    {
        parent::setUp();

        $this->cpe = factory('App\Models\CPE')->create([
            'ConnectionRequestUser'=>'test'
        ]);
    }

    public function testCpeLogin()
    {
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
    public function testCpeCreateEntry()
    {
        $body = array(
          //Device ID
          'Manufacturer'=>'NTGR',
          'OUI'=>'08028e',
          'ProductClass'=>'V7610',
          'SerialNumber'=>'08028eef0b00',
          //Parameter List
          'HardwareVersion'=>'abc',
          'SoftwareVersion'=>'V2.2.2.26_ST2',
          'ConnectionRequestURL'=>'http://79.0.0.179:7547/'
        );
        $InformBoot = m::mock('App\Interfaces\IInformContract');
        $InformBoot->shouldReceive('informGetBody')
                    ->once()
                    ->andReturn($body);

        $this->app->instance('App\Interfaces\IInformContract', $InformBoot);

        //$cpe = $this->app->make('App\Models\CPE');
        $cpe = new CPE();

        $cpe->cpeCreateEntry($InformBoot);
        $this->assertDatabaseHas('cpes', [
            'Manufacturer'=>'NTGR',
            'OUI'=>'08028e',
            'ProductClass'=>'V7610',
            'SerialNumber'=>'08028eef0b00'
        ]);
    }

    public function tearDown()
    {
        $this->artisan('migrate:refresh');
        m::close();
        parent::tearDown();
    }
}
