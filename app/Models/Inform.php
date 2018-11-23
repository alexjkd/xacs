<?php

namespace App\Models;

use App\Interfaces\IInformContract;
use Illuminate\Database\Eloquent\Model;

class Inform extends Model implements IInformContract
{
    /**
     * @var array
     */
    public $body;

    public static function ValidSoap(string $soap_xml)
    {
        return true;
    }

    public function informBuildBody($soap_xml)
    {
        //TODO:Build body array: key=>value
    }

    public function informBodyAttribute($key,$value)
    {

    }

    /**
     *
     */
    public function informGetBody()
    {
        //TODO:return the body array
    }
}

