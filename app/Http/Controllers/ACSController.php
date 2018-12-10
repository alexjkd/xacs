<?php

namespace App\Http\Controllers;

use App\Interfaces\ICpeContract;
use App\Models\Facades\SoapFacade;
use App\Models\SoapEngine;
use App\Models\CPE;

use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class ACSController extends Controller
{
    /**
     * @var ICpeContract
     */
    protected $cpe;

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
                Log::info('Login with blank user and response with 200 OK');
                response('',200);
                Log::info('Create the CPE and set next action to SetParameter for the credential to remote');
                //TODO:Create CPE and build action chain
                $this->cpe = new CPE();
                $this->cpe->cpeCreate(SoapFacade::ParseInformRequest($request->getContent()));
                return;
            }
            Log::info("Login CPE with ".$credential['name'].':'.$credential['password']);
            $this->cpe = CPE::where('ConnectionRequestUser','=', $credential['name'])->first();
            if (empty($this->cpe))
            {
                Log::info('Can not find CPE with credential in Request');
                abort(401);
                return;
            }
            $status = $this->cpe->cpeLogin($credential);
            if($status == CPE::STATUS_SUCCEEDED)
            {
                Log::info('Request with valid credential and response with 200 OK');
                response('',200);
                Log::info('Find CPE and get action list for next steps');
                return;
            }
            else if ($status == CPE::STATUS_FAILED)
            {
                Log::info('Request with invalid password and abort the request with HTTP 401 code');
                abort(401);
                return;
            }

        }
        abort(403);
    }



}
