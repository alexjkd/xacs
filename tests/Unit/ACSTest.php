<?php
/**
 * Created by PhpStorm.
 * User: melon
 * Date: 2018/11/23
 * Time: 13:27
 */

namespace Tests\Unit;

use App\Models\ACS;
use App\Models\Inform;
use \Mockery as m;
use Tests\TestCase;

class ACSTest extends TestCase
{
    public function testAcsBuildParameterStruct()
    {
        $cpe = m::mock('App\Interfaces\ICpeContract');
        $this->app->instance('App\Interfaces\ICpeContract',$cpe);

        $datamodel = m::mock('App\Interfaces\IDataModelContract');
        $this->app->instance('App\Interface\IDataModelContract',$datamodel);

        $datamodel->shouldReceive('dataLoad')->once()->andReturn(true);
        $datamodel->shouldReceive('dataGetType')->andReturn('xstns:string');

        $data = array (
            'Device.ManagementServer.Username'=>'08028E-08028EEF0B00',
            'Device.ManagementServer.Password'=>'xwhSLiQAwOXlLeVX',
            'Device.ManagementServer.URL'=>'http://58.162.32.33/cwmp/cwmp'
        );
        $acs = new ACS($datamodel);
        $soap = $acs->acsBuildParameterStruct($data,$cpe);

        $this->assertTrue(Inform::ValidSoap($soap));
    }

    public function tearDown()
    {
        $this->artisan('migrate:refresh');
        parent::tearDown();
    }
}
