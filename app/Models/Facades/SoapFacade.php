<?php

namespace App\Models\Facades;

use App\Models\SoapActionEvent;
use Illuminate\Support\Facades\Facade;

/**
 * @method static bool ValidSoap(string $soap_xml)
 * @method static int GetSoapType(string $soap_xml)
 * @method static string ParseInformRequest(string $soap)
 * @method static string BuildInformResponse($ID)
 * @method static string BuildSetParameterRequest($data, IDataModelContract $dataModel)
 * @method static string ParseSetParameterResponse($soap)
 * @see \App\Models\SoapEngine
 */
class SoapFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'soap';
    }
}
