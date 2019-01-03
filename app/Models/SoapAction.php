<?php

namespace App\Models;

use App\Models\Actions\BOOTSTRAP;
use App\Models\Actions\BOOT;
use App\Models\Actions\HTTP_AUTH;
use App\Models\Actions\SET_PARAMETER;
use Illuminate\Database\Eloquent\Model;

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
        return $this->belongsTo(CPE::class,'cpe_id','id');
    }

    /**
     * @return int
     */
    public function actionGetDirection()
    {
        $direction = SoapActionDirection::UNKNOWN;

        switch ($this->getAttribute('event'))
        {
            case SoapActionEvent::BOOT:
            case SoapActionEvent::BOOTSTRAP:
                $direction = SoapActionDirection::REQUEST;
                break;
            case SoapActionEvent::SET_PARAMETER:
                $direction = SoapActionDirection::RESPONSE;
                break;
            default:
                break;
        }
        return $direction;
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
    public function Handler($httpContent = null, $authentication = null)
    {

    }
    //abstract public function GetDirection(string $httpContent);

    //abstract public function RequestHandler();
    //abstract public function ResponseHandler();
}
