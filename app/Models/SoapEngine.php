<?php
/**
 * Created by PhpStorm.
 * User: melon
 * Date: 2018/12/3
 * Time: 13:47
 */

namespace App\Models;

use App\Interfaces\IDataModelContract;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;

class SoapEngine
{
    private $templateParameterStruct;
    private $templateSetParameterRequest;

    public function __construct()
    {
        $this->templateParameterStruct = File::get(base_path('app/Models/xml/ParameterStruct.xml'));
        $this->templateSetParameterRequest = File::get(base_path('app/Models/xml/SetParamerterRequest.xml'));
    }
    protected static function _display_error($error)
    {
        $return = "<br/>\n";
        switch ($error->level) {
            case LIBXML_ERR_WARNING:
                $return .= "<b>Warning $error->code</b>: ";
                break;
            case LIBXML_ERR_ERROR:
                $return .= "<b>Error $error->code</b>: ";
                break;
            case LIBXML_ERR_FATAL:
                $return .= "<b>Fatal Error $error->code</b>: ";
                break;
        }
        $return .= trim($error->message);
        if ($error->file) {
            $return .=    " in <b>$error->file</b>";
        }
        $return .= " on line <b>$error->line</b>\n";

        return $return;
    }

    protected static function _display_errors()
    {
        $errors = libxml_get_errors();
        foreach ($errors as $error) {
            print SoapEngine::_display_error($error);
        }
        libxml_clear_errors();
    }

    public static function ValidSoap(string $soap_xml)
    {
        $xml = new \DOMDocument();
        $xml->loadXML($soap_xml);
        if (!$xml->schemaValidate(base_path('app/Models/xml/cwmp-1-2.xsd')))
        {
            SoapEngine::display_errors();
            return false;
        }
        return true;
    }

    public function soapBuildParameterStruct($data, IDataModelContract $dataModel)
    {
        $struct = '';
        foreach ($data as $key=>$value)
        {
            $type = $dataModel->dataGetType($key);
            $entry = $this->templateParameterStruct;
            $entry = str_replace('{@KEY}',$key,$entry);
            $entry = str_replace('{@TYPE}',$type,$entry);
            $entry = str_replace('{@VALUE}',$key,$entry);
            $struct = sprintf("%s%s",$entry,$struct);
        }
        $setParameterRequest = $this->templateSetParameterRequest;
        $setParameterRequest = str_replace('{@PARAMETER_NUM}',count($data),$setParameterRequest);
        $setParameterRequest = str_replace('{@PARAMETER_VALUE_STRUCT}',$struct,$setParameterRequest);

        return $setParameterRequest;
    }

    public function soapGetDataFromParameterStruct($struct)
    {

    }
}
