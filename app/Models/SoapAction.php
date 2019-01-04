<?php

namespace App\Models;

use App\Models\Actions\BOOTSTRAP;
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
            case SoapActionEvent::HTTP_AUTH:
                $model = new HTTP_AUTH();
                break;
            case SoapActionEvent::BOOTSTRAP:
                $model = new BOOTSTRAP();
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
    public function HandlerOnAcs($httpContent = null, $authentication = null)
    {
        Log::warning('the action has not set a handler for ACS.');
        return null;
    }
}
