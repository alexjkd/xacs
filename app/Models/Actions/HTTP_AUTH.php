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

    /**
     * @return array|void
     */
    public function HandlerOnAcs($httpContent = null, $authentication = null)
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
            $cpe = $this->cpe()->get()->first();
            $username = $cpe->getAttribute('ConnectionRequestUser');
            $password = $cpe->getAttribute('ConnectionRequestPassword');
            $data = array (
                    'Device.ManagementServer.Username' => $username,
                    'Device.ManagementServer.Password' => $password,
                    'Device.ManagementServer.URL'=>'http://58.162.32.33/cwmp/cwmp'
            );
            $set_parameter = new SET_PARAMETER(['data'=>$data, 'cwmpid'=>AcsFacade::acsGenerateCwmpdID()]);
            $cpe->action()->save($set_parameter);
            //TODO: Notify ACS with this action ask ACS to send it out
        }
        else
        {
            $result['code'] = 403;
            $result['content'] ='';
            //todo if auth failed, need to response the 401 and clean the action chain.
        }
        return $result;
    }
}
