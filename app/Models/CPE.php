<?php

namespace App\Models;

use App\Interfaces\ICpe;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;

class CPE extends Model implements ICpe
{
    protected $table = 'cpes';

    public function cpeBlankUserAuth(Request $request)
    {
        return true;
    }

    public function cpeSavedUserAuth(Request $request)
    {
        return true;
    }
}
