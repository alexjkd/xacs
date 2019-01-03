<?php
/**
 * Created by PhpStorm.
 * User: melon
 * Date: 2019/1/2
 * Time: 15:07
 */

namespace App\Models\Actions;

use App\Models\SoapActionEvent;
use App\Models\SoapActionStage;
use Illuminate\Support\Facades\Log;


class BOOTSTRAP extends INFORM
{
    public static function boot()
    {
        parent::boot();

        static::addGlobalScope('event', function (Builder $builder) {
            $builder->where('event', SoapActionEvent::BOOTSTRAP);
        });
    }

    public function __construct($attributes = array())

    {
        parent::__construct();
        $this->setAttribute('event',SoapActionEvent::BOOTSTRAP);
        $this->setAttribute('stage',SoapActionStage::STAGE_INITIAL);
        if(isset($attributes['data']))
        {
            $this->setAttribute('data',json_encode($attributes['data']));
        }
    }
}
