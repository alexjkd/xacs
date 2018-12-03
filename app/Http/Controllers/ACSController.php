<?php

namespace App\Http\Controllers;

use App\Interfaces\ICpeContract;
use App\Models\Inform;
use App\Models\Facades\SoapFacade;
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
        $authorization = '';

        if ($request->server->getHeaders()['AUTHORIZATION'])
        {
            $authorization = $request->server->getHeaders()['AUTHORIZATION'];
        }
        $user = $request->server->getHeaders()['PHP_AUTH_USER'];
        $password = $request->server->getHeaders()['PHP_AUTH_PW'];

        $credential = array('authentication'=>$authorization,
                        'user'=>$user,'password'=>$password);

        return $credential;
    }

    protected function _withAuthentication(Request $request)
    {
        $withHttpAuth = (!empty($request->server->getHeaders()['HTTP_AUTHORIZATION']) ||
                        !empty($request->server->getHeaders()['AUTHORIZATION']));

        return $withHttpAuth;
    }

    public function AcsDispatcher(Request $request)
    {
        //TODO:It should not send 403 when ending session with an empty post
        if($request->getContent() && !$this->_withAuthentication($request))
        {
            return response('Unauthenticated',401);
        }
        else if ($request->getContent() && $this->_withAuthentication($request))
        {
            return response('',$this->CpeLogin($request));
        }

        return response('Unknown Request',403);
    }

    public function CpeLogin(Request $request)
    {
        if (!SoapFacade::ValidSoap($request->getContent()))
        {
            return response('The SOAP message is not validated',403);
        }

        $status_code = 401;
        $credential = $this->_GetCredentialFromHeader($request);
        if ($this->cpe->cpeBlankUserAuth($credential)||
            $this->cpe->cpeSavedUserAuth($credential))
        {
            $status_code = 200;
        }
        return $status_code;
    }

}
