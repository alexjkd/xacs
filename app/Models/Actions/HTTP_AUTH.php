<?php
/**
 * Created by PhpStorm.
 * User: melon
 * Date: 2019/1/2
 * Time: 15:07
 */

namespace App\Models\Actions;


use App\Models\CPE;
use App\Models\Facades\AcsFacade;
use App\Models\SoapAction;
use App\Models\SoapActionEvent;
use App\Models\SoapActionStage;
use Illuminate\Support\Facades\Log;


class HTTP_AUTH extends SoapAction
{
    public static function boot()
    {
        parent::boot();

        static::addGlobalScope('event', function (Builder $builder) {
            $builder->where('event', SoapActionEvent::HTTP_AUTH);
        });
    }

    public function __construct($attributes = array())
    {
        parent::__construct();
        $this->setAttribute('event',SoapActionEvent::HTTP_AUTH);
        $this->setAttribute('stage',SoapActionStage::STAGE_INITIAL);
        if(isset($attributes['data']))
        {
            $this->setAttribute('data',json_encode($attributes['data']));
        }
    }

    public function GetDirection(string $httpContent)
    {
        // TODO: Implement GetDirection() method.
    }

    /**
     * @return array|void
     */
    public function Handler($httpContent = null, $authentication = null)
    {
        $result = array(
            'code' => 500,
            'content' =>'',
        );

        if ( empty($authentication) )
        {
            Log::warning('There is no http authentication headers, but ACS need auth.');
            $result['code'] = 401;
            $result['content'] ='';
            return $result;
        }

        $this->update(['data' =>
            json_encode(array('authentication'=> $authentication))]);

        $blankAuthentication = 'Basic ' . base64_encode(':');
        $http_authentication = $authentication;

        if ( $http_authentication === $blankAuthentication )
        {
            $result['code'] = 200;
            $result['content'] ='';
            Log::info("CPE() auth with user blank");
            response('',200);
            $new_action = new SoapAction();
            //todo get new user and password
            $data = array (
                    'Device.ManagementServer.Username'=>'08028E-08028EEF0B00',
                    'Device.ManagementServer.Password'=>'xwhSLiQAwOXlLeVX',
                    'Device.ManagementServer.URL'=>'http://58.162.32.33/cwmp/cwmp'
            );
            $set_parameter = new SET_PARAMETER(['data'=>$data, 'cwmpid'=>AcsFacade::acsGenerateCwmpdID()]);
            $cpe = CPE::find($this->getAttribute('fk_cpe_id'));
            $cpe->action()->save($set_parameter);
        }
        else
        {
            $result['code'] = 403;
            $result['content'] ='';
            //todo if auth failed, need to response the 401 and clean the action chain.
        }
        return $result;
    }
    public function ResponseHandler()
    {

    }

    public function RequestHandler()
    {

    }
}
