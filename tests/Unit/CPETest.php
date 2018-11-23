<?php
/**
 * Created by PhpStorm.
 * User: melon
 * Date: 2018/11/21
 * Time: 23:12
 */

namespace Tests\Unit;

use App\Models\CPE;
use App\Models\Inform;

use Illuminate\Support\Facades\Artisan;
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

    public function testCpeBlankUserAuth()
    {
         $authentication = 'Basic ' . base64_encode(':');
         $credential=array('authentication'=>$authentication);

         $result = $this->cpe->CpeBlankUserAuth($credential);
         $this->assertTrue($result);
    }

    public function testCpeSavedUserAuth()
    {
        $user = 'test';
        $password = 'secret';
        $credential = array('user'=>$user, 'password'=>$password);

        $result = $this->cpe->CpeSavedUserAuth($credential);
        $this->assertTrue($result);

    }

    public function testCpeCreate()
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
                    ->andReturn($body)
                    ->getMock();
        $this->app->instance('App\Interfaces\IInformContract', $InformBoot);

        //$cpe = $this->app->make('App\Models\CPE');
        $cpe = new CPE();

        $cpe->cpeCreate($InformBoot);
        $this->assertDatabaseHas('cpes', [
            'Manufacturer'=>'NTGR',
            'OUI'=>'08028e',
            'ProductClass'=>'V7610',
            'SerialNumber'=>'08028eef0b00'
        ]);


    }
    public function testCpeSetParameterValues()
    {

    }

    public function tearDown()
    {
        $this->artisan('migrate:refresh');
        m::close();
        parent::tearDown();
    }
}
