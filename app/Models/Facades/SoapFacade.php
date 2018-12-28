<?php

namespace App\Models\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static bool ValidSoap(string $soap_xml)
 * @method static int GetSoapType(string $soap_xml)
 * @method static string ParseInformRequest(string $soap)
 * @method static string BuildInformResponse($ID)
 * @method static string BuildSetParameterRequest($data, IDataModelContract $dataModel=null)
 * @method static string ParseSetParameterResponse($soap)
 * @method static string BuildGetParameterRequest($data)
 * @method static string ParseGetParameterResponse($soap)
 * @see \App\Models\SoapEngine
 */
class SoapFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'soap';
    }
}
