<?php

namespace App\Models;


use App\Interfaces\IAcsContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
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
    private static $instance;

    public function __construct($attributes = array())
    {
        parent::__construct($attributes);
        self::$count++;
        $show = self::$count;
        print_r("__constructor(): count = $show\n");
    }

    public static function singleton()
    {
        if (!isset(self::$instance)) {
            self::$instance = new ACS();
        }
        return self::$instance;
    }

    public function acsGenerateCwmpdID()
    {

    }

    public function acsGetCPEAuthable()
    {
        $show = self::$count;
        print_r("acsGetCPEAuthable(): count = $show\n");
        return true;
    }

    /**
     * @param string $cwmpid
     * @return CPE $cpe
     */
    public function acsFindCpe(Request $request)
    {
        $this->cpes = CPE::all();

        return $this->cpes->first();
    }


}
