<?php
/**
 * Created by PhpStorm.
 * User: melon
 * Date: 2018/11/21
 * Time: 23:12
 */

namespace Tests\Unit;

use App\Models\CPE;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;


class CPETest extends TestCase
{
    protected $cpe;

    public function setUp()
    {
        parent::setUp();

        $this->cpe = factory('App\Models\CPE')->create([
            'connection_request_username'=>'test'
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

    public function testCpeSetParameterValues()
    {

    }

    public function tearDown()
    {
        $this->artisan('migrate:refresh');
        parent::tearDown();
    }
}
