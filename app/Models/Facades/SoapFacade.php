<?php

namespace App\Models\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static bool ValidSoap(string $soap_xml)
 * @method static int GetSoapType(string $soap_xml)
 * @method static string ParseInformRequest(string $soap)
 * @method static string soapBuildInformResponse($ID)
 * @method static string soapBuildSetParameterRequest($data, IDataModelContract $dataModel)
 * @method static string soapParseSetParameterResponse($soap)
 * @method static function soapGetActionType($soap)
 * @see \App\Models\SoapEngine
 */
class SoapFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'soap';
    }
}
