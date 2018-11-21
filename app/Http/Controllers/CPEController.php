<?php

namespace App\Http\Controllers;

use App\Interfaces\ICpe;
use Illuminate\Http\Request;

class CPEController extends Controller
{
    /**
     * @var ICpe
     */
    protected $cpe;

    public function __construct(ICpe $cpe)
    {
        $this->cpe = $cpe;
    }

    public function ConnectionRequest(Request $request)
    {
        $status_code = 401;

        if ($this->cpe->cpeBlankUserAuth($request) ||
            $this->cpe->cpeSavedUserAuth($request))
        {
            $status_code = 200;
        }
        return response('',$status_code);

    }

}
