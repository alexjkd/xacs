<?php

namespace App\Http\Controllers;

use App\Interfaces\ICpeContract;
use App\Models\Inform;
use Illuminate\Http\Request;

class ACSController extends Controller
{
    /**
     * @var ICpeContract
     */
    protected $cpe;

    public function __construct(ICpeContract $cpe)
    {
        $this->cpe = $cpe;
    }

    protected function _ParseRequest(Request $request)
    {
        //return soap xml
        return 'this is a xml';
    }

    protected function _GetCredentialFromHeader(Request $request)
    {
        $authorization = $request->headers->get('authorization');
        $user = $request->headers->get('php-auth-user');
        $password = $request->headers->get('php-auth-pw');

        $credential = array('authentication'=>$authorization,
                        'user'=>$user,'password'=>$password);

        return $credential;
    }

    public function AcsDispatcher(Request $request)
    {
        $xml_soap = $this->_ParseRequest($request);
        if(!Inform::ValidSoap($xml_soap))
        {
            return response('The SOAP message is not validated',403);
        }
    }

    public function CpeLogin(Request $request)
    {
        $status_code = 401;
        $credential = $this->_GetCredentialFromHeader($request);
        if ($this->cpe->cpeBlankUserAuth($credential)||
            $this->cpe->cpeSavedUserAuth($credential))
        {
            $status_code = 200;
        }
        return response('',$status_code);
    }

}
