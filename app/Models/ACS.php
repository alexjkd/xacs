<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

use App\Interfaces\ICpeContract;

class ACS extends Model
{

    /**
     * @var IProtocolContract
     */
    protected $protocol;

    public function __construct()
    {

    }

    public function generateCwmpdID()
    {

    }

}
