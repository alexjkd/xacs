<?php

namespace App;

use App\Interfaces\ICpe;
use http\Env\Request;
use Illuminate\Database\Eloquent\Model;

class CPE extends Model implements ICpe
{
    protected $table = 'cpes';

    public function cpeBlankUserAuth(Request $request)
    {

    }

    public function cpeSavedUserAuth(Request $request)
    {

    }
}
