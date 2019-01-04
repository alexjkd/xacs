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
    const OK = 0;
    const STATUS_FINISHED = 2;
    const STATUS_READY = 3;
}

class SoapActionEvent extends Enum
{
    const UNKNOWN = -2;
    const HTTP_AUTH = -1;
    const BOOTSTRAP = 0;
    const BOOT = 1;
    const EVENT_GETPARAMETER = 2;
    const SET_PARAMETER = 3;
}

class SoapActionStage extends Enum
{
    const STAGE_INITIAL= 0;
    const STAGE_USER = 1;
}

