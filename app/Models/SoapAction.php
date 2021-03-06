<?php

namespace App\Models;

use App\Models\Actions\BOOTSTRAP_BOOT;
use App\Models\Actions\BOOT;
use App\Models\Actions\HTTP_AUTH;
use App\Models\Actions\SET_PARAMETER;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\Log;

class SoapAction extends Model
{
    protected $table = 'soap_actions';
    /*
     * fillable is the limited for create(),only attributes list can be fillable
     */
    protected $fillable=[
        'cwmpid', 'stage', 'event', 'status', 'data', 'request','response'
    ];

    public function cpe()
    {
        return $this->belongsTo(CPE::class,'fk_cpe_id','id');
    }


    public function newFromBuilder($attributes = [], $connection = null)
    {
        switch (array_get((array) $attributes, 'event')) {
            case SoapActionEvent::BOOTSTRAP:
                $model = new BOOTSTRAP_BOOT();
                break;
            case SoapActionEvent::BOOT:
                $model = new BOOT();
                break;
            case SoapActionEvent::SET_PARAMETER:
                $model = new SET_PARAMETER();
                break;
            default:
                $model = $this->newInstance();
        }

        $model->exists = true;

        $model->setRawAttributes((array) $attributes, true);

        $model->setConnection($connection ?: $this->connection);

        return $model;
    }
    /**
     * @param string $httpContent
     * @param string $authentication
     * @return array
     */
    public function HandleResponse($httpContent = null, $authentication = null)
    {
        Log::warning('The action has not set a handler for response.');
    }

    public function PrepareRequest($data = null)
    {
        Log::warning('The action has not set handler for request');
    }

}
