<?php
/**
 * Created by PhpStorm.
 * User: melon
 * Date: 2018/12/11
 * Time: 14:21
 */

namespace Tests\Unit;

use Tests\TestCase;

class SoapActionTest extends TestCase
{
    public function testCreateAction()
    {
        $action = factory('App\Models\SoapAction')->create();
        $this->assertTrue(true);
    }
}
