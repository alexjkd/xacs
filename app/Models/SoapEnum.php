<?php
/**
 * Created by PhpStorm.
 * User: melon
 * Date: 2018/12/25
 * Time: 15:10
 */

namespace App\Models;


use App\Interfaces\Enum;

class SoapActionStatus extends Enum
{
    const STAGE_INITIAL=0;
    const STAGE_USER=1;
    const STATUS_FINISHED = 2;
    const STATUS_READY= 3;
}

class SoapActionType extends Enum
{
    const EVENT_HTTP_AUTH=-1;
    const EVENT_BOOTSTRAP=0;
    const EVENT_BOOT=1;
    const EVENT_GETPARAMETER= 2;
    const EVENT_SETPARAMETER= 3;
}
