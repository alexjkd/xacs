<?php

namespace App\Models\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static bool ValidSoap(string $soap_xml)
 * @method static int GetSoapType(string $soap_xml)
 * @method static string ParseInformRequest(string $soap)
 * @see \App\Models\SoapEngine
 */
class SoapFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'soap';
    }
}
