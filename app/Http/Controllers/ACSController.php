<?php

namespace App\Http\Controllers;

use App\Interfaces\ICpeContract;
use App\Models\CPE;
use App\Models\Inform;
use App\Models\Facades\SoapFacade;
use App\Models\SoapEngine;
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
        $name = $request->server->getHeaders()['PHP_AUTH_USER'];
        $password = $request->server->getHeaders()['PHP_AUTH_PW'];

        $credential = array('authentication'=>$authorization,
                        'name'=>$name,'password'=>$password);

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
            if (!SoapFacade::ValidSoap($request->getContent()))
            {
                abort(403);
            }
            $credential = $this->_GetCredentialFromHeader($request);
            $blankAuthentication = 'Basic ' . base64_encode(':');
            $isBlankUser = $credential['authentication'] === $blankAuthentication;

            if ($isBlankUser
                && SoapFacade::GetSoapType($request->getContent()) == SoapEngine::INFROM_BOOTSTRAP)
            {
                //create the CPE
                return response('',200);
            }
            $status = $this->cpe->cpeLogin($credential);
            return response('',$status);
        }
        abort(403);
    }



}
