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


class SET_PARAMETER extends SoapAction
{
    public static function boot()
    {
        parent::boot();

        static::addGlobalScope('event', function (Builder $builder) {
            $builder->where('event', SoapActionEvent::SET_PARAMETER);
        });
    }

    public function __construct($attributes = array())
    {
        parent::__construct();
        $this->setAttribute('event',SoapActionEvent::SET_PARAMETER);
        $this->setAttribute('stage',SoapActionStage::STAGE_INITIAL);
        foreach ($attributes as $key=>$value)
        {
            if ($key === 'data')
            {
                $value = json_encode($value);
            }
            $this->setAttribute($key,$value);
        }
    }

    public function HandlerOnAcs($httpContent = null, $authentication = null)
    {
        $result = array(
            'code' => 500,
            'content' =>'',
        );
        //todo should find whether the cwmpid in response['cwmpid'] is exist or not
        if(empty($httpContent))
        {
            Log::error('No content for set parameter method');
            return $result;
        }
        $response = SoapFacade::ParseSetParameterResponse($httpContent);
        if ($response['status'] === SoapActionStatus::OK)
        {
            $this->update([
                'response'=>$httpContent,
                'status' => SoapActionStatus::STATUS_FINISHED,
            ]);
            $result['code'] = 200;
            $result['content'] = '';
        }
        return $result;
    }
}
