<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SoapAction extends Model
{
    const EVENT_HTTP_AUTH=0;
    const EVENT_BOOTSTRAP=1;
    const EVENT_BOOT=2;
    const STAGE_INITIAL=0;
    const STAGE_USER=1;
    const STATUS_FINISHED = 1;
    const STATUS_READY= 0;
    protected $table = 'soap_actions';
    /*
     * fillable is the limited for create(),only attributes list can be fillable
     */
    protected $fillable=[
        'stage', 'event', 'status', 'data', 'soap'
    ];

    public function cpe()
    {
        return $this->belongsTo(CPE::class,'cpe_id','id');
    }
}
