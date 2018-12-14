<?php
/**
 * Created by PhpStorm.
 * User: melon
 * Date: 2018/12/11
 * Time: 14:21
 */

namespace Tests\Unit;

use App\Models\SoapAction;
use Tests\TestCase;

class SoapActionTest extends TestCase
{
    public function testCreateAction()
    {
        $soap = 'test';
        $this->artisan('migrate:refresh');
        $action = factory('App\Models\SoapAction')->create();
        $action->update([
            'soap'=>$soap,
            'status'=> SoapAction::STATUS_FINISHED,
        ]);
        $this->assertDatabaseHas('soap_actions',[
            'soap'=>$soap,
            'status'=> SoapAction::STATUS_FINISHED,
        ]);
    }
}
