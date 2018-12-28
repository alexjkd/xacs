<?php

namespace App\Models;

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
}
