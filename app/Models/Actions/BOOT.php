<?php
/**
 * Created by PhpStorm.
 * User: melon
 * Date: 2019/1/2
 * Time: 15:07
 */

namespace App\Models\Actions;

use App\Models\Facades\SoapFacade;
use App\Models\SoapAction;
use App\Models\SoapActionEvent;
use App\Models\SoapActionStage;
use App\Models\SoapActionStatus;
use Illuminate\Support\Facades\Log;

class BOOT extends SoapAction
{
    public static function boot()
    {
        parent::boot();

        static::addGlobalScope('event', function (Builder $builder) {
            $builder->where('event', SoapActionEvent::BOOT);
        });
    }

    public function __construct($attributes = array())
    {
        parent::__construct();
        $this->setAttribute('event',SoapActionEvent::BOOT);
        $this->setAttribute('stage',SoapActionStage::STAGE_INITIAL);
        if(isset($attributes['data']))
        {
            $this->setAttribute('data',json_encode($attributes['data']));
        }
    }

    public function Handler($httpContent = null, $authentication = null)
    {
        $result = array(
            'code' => 500,
            'content' =>'',
        );

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

    public function GetDirection(string $httpContent)
    {
        // TODO: Implement GetDirection() method.
    }

    public function ResponseHandler()
    {

    }

    public function RequestHandler()
    {

    }
}
