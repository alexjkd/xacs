<?php
/**
 * Created by PhpStorm.
 * User: melon
 * Date: 2018/12/11
 * Time: 14:21
 */

namespace Tests\Unit;

use App\Models\SoapActionStatus;
use Tests\TestCase;

class SoapActionTest extends TestCase
{
    public function testCreateAction()
    {
        $soap = 'test';
        $cwmpid = '123456abc';
        $this->artisan('migrate:refresh');
        $action = factory('App\Models\SoapAction')->create();
        $action->update([
            'request'=> $soap,
            'status' => SoapActionStatus::STATUS_FINISHED,
            'cwmpid' => $cwmpid,
        ]);
        $this->assertDatabaseHas('soap_actions',[
            'request'=>$soap,
            'status'=> SoapActionStatus::STATUS_FINISHED,
            'cwmpid'=>$cwmpid,
        ]);
    }
}
