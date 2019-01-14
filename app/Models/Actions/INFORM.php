<?php
/**
 * Created by PhpStorm.
 * User: melon
 * Date: 2019/1/2
 * Time: 15:07
 */

namespace App\Models\Actions;


use App\Models\Facades\AcsFacade;
use App\Models\Facades\SoapFacade;
use App\Models\SoapAction;
use App\Models\SoapActionStatus;
use App\Interfaces\IAuthorized;
use Illuminate\Support\Facades\Log;


abstract class INFORM extends SoapAction  implements IAuthorized
{
    public function HandleResponse($httpContent = null, $authentication = null)
    {
        if(AcsFacade::acsGetCPEAuthable())
        {
            Log::info('ACS need authentication, add auth action to do authentication first');
            $result = $this->HandleAuthorization($httpContent, $authentication);
            if ($result['code'] != 200)
            {
                Log::info('CPE authenticate failed, return 401');
                return $result;
            }
        }

        if(empty($httpContent))
        {
            Log::warning('the http content is empty which will cause an abnormal record in session log');
        }
        $data = SoapFacade::ParseInformRequest($httpContent);
        if(empty($data))
        {
            Log::error('parse the http content failed!');
            $result['code'] = 500;
            $result['content'] ='parse the http content failed.';
            return $result;
        }
        $result['content'] = SoapFacade::BuildInformResponse($data['ID']);
        $result['code']=200;
        $this->update([
            'request' => $httpContent,
            'data' => json_encode($data),
            'cwmpid'=>$data['ID'],
            'response'=>$result['content'],
            'status'=> SoapActionStatus::STATUS_FINISHED,
        ]);
        //todo notify the ACS to send out the response
        return $result;
    }

    public function HandleAuthorization($httpContent = null, $authentication = null)
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
