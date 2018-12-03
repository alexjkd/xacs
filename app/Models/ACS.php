<?php

namespace App\Models;


use App\Interfaces\IProtocolContract;
use Illuminate\Database\Eloquent\Model;

use App\Interfaces\ICpeContract;

class ACS extends Model
{

    /**
     * @var IProtocolContract
     */
    protected $protocol;

    public function __construct(IProtocolContract $protocol)
    {
        $this->protocol = $protocol;
    }

}
