<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\CPE;

class SoapAction extends Model
{
    const EVENT_HTTP_AUTH=0;
    const EVENT_INFORM_BOOTSTRAP=1;
    const EVENT_INFORM_BOOT=2;
    const STAGE_INITIAL=0;
    const STAGE_USER=1;
    const STATUS_FINISHED = 1;
    const STATUS_READY= 0;


    public function cpe()
    {
        return $this->belongsTo(CPE::class,'cpe_id','id');
    }
}
