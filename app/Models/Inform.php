<?php

namespace App\Models;

use App\Interfaces\IInformContract;
use Illuminate\Database\Eloquent\Model;


class Inform extends Model implements IInformContract
{
    protected $type;
    protected $attributes;
    protected $soap;
    /**
     * @var CPE
     */
    protected $cpe;
    protected $time;

    public function informGetBody()
    {

    }
}

