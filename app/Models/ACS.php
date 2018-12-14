<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use App\Models\CPE;

use App\Interfaces\ICpeContract;

class ACS extends Model
{

    /**
     * @var IProtocolContract
     */
    protected $protocol;
    protected $cpes;
    public static $count=0;

    public function __construct($attributes = array())
    {
        parent::__construct($attributes);
        self::$count++;
    }

    public function acsGenerateCwmpdID()
    {

    }

    public function acsGetCPEAuthable()
    {
        return true;
    }

    /**
     * @param string $cwmpid
     * @return CPE $cpe
     */
    public function acsFindCpe($cwmpid)
    {
        $this->cpes = CPE::all();

        return $this->cpes->first();
    }
}
