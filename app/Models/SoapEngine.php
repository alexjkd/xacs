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
use SimpleXMLElement;
use SimpleXMLIterator;

class SoapEngine
{
    private static $templateInformResponse;

    private static $templateParameterStruct;
    private static $templateSetParameterRequest;
    private $templateGetParameterRequest;


    const STATUS_OK="0";
    const INFROM_BOOTSTRAP="0";

    public function __construct()
    {
        self::$templateParameterStruct = File::get(base_path('app/Models/xml/ParameterStruct.xml'));
        self::$templateSetParameterRequest = File::get(base_path('app/Models/xml/SetParamerterRequest.xml'));
        self::$templateInformResponse = File::get(base_path('app/Models/xml/InformResponse.xml'));
        $this->templateGetParameterRequest = File::get(base_path('app/Models/xml/GetParameterRequest.xml'));
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
        if(empty($soap_xml))
        {
            return true;
        }

        $xml = new \DOMDocument();
        $xml->loadXML($soap_xml);
        if (!$xml->schemaValidate(base_path('app/Models/xml/cwmp-1-2.xsd')))
        {
            SoapEngine::display_errors();
            return false;
        }
        return true;
    }

    public static function GetSoapType(string $soap_xml)
    {
        return SoapEngine::INFROM_BOOTSTRAP;
    }

    public static function ParseInformRequest($soap)
    {
        $xml = simplexml_load_string($soap);
        $ns = $xml->getNamespaces(true);
        $ns_name = array_keys($ns);
        $data = array();

        $data['ID'] = (string)$xml->children($ns_name[0],true)
                ->Header[0]->children($ns_name[1],true)->ID;
        $inform = $xml[0]->children($ns_name[0], true)->Body[0]
                ->children($ns_name[1], true)->Inform[0]->children();
        foreach ($inform->DeviceId[0] as $key=>$value)
        {
            $data['DeviceId'][$key] = $value;
        }

        foreach ($inform->Event[0]->children('', true) as $key=>$value)
        {
            //0 BOOTSTRAP
            $name_value = explode(' ',$value->EventCode);
            $data['EventStruct'][ (string)$name_value[1]] = (string)$name_value[0];
        }
        $data['MaxEnvelopes'] = (integer)$inform->MaxEnvelopes;
        $data['CurrentTime'] = (string)$inform->CurrentTime;
        $data['RetryCount'] = (integer)$inform->RetryCount;

        foreach ($inform->ParameterList[0]->children('', true) as $key => $value)
        {
            $data['ParameterList'][ (string)$value->Name ] = (string)$value->Value;
        }

        return $data;
    }
    public static function soapBuildInformResponse($ID)
    {
        $informResponse = self::$templateInformResponse;
        $informResponse = str_replace('{@ID}',$ID,$informResponse);

        return $informResponse;
    }

    public static function soapBuildSetParameterRequest($data, IDataModelContract $dataModel)
    {
        $struct = '';
        foreach ($data as $key=>$value)
        {
            $type = $dataModel->dataGetType($key);
            $entry = self::$templateParameterStruct;
            $entry = str_replace('{@KEY}',$key,$entry);
            $entry = str_replace('{@TYPE}',$type,$entry);
            $entry = str_replace('{@VALUE}',$value,$entry);
            $struct = sprintf("%s%s",$entry,$struct);
        }
        $setParameterRequest = self::$templateSetParameterRequest;
        $setParameterRequest = str_replace('{@PARAMETER_NUM}',count($data),$setParameterRequest);
        $setParameterRequest = str_replace('{@PARAMETER_VALUE_STRUCT}',$struct,$setParameterRequest);

        return $setParameterRequest;
    }

    public static function soapParseSetParameterResponse($soap)
    {
        $xml = simplexml_load_string($soap);
        $ns = $xml->getNamespaces(true);
        $ns_name = array_keys($ns);

        $status = $xml->children($ns_name[0],true)->Body->children($ns_name[1],true)
            ->SetParameterValuesResponse
            ->children()->Status;

        return $status[0];
    }

    public static function soapGetActionType($soap)
    {

    }

//----------------------------------------------------------------------------
    public function soapBuildGetParameterRequest($data)
    {
        $getParameterRequest = $this->templateGetParameterRequest;
        $getParameterRequest = str_replace('{@COUNT}',count($data), $getParameterRequest);

        foreach ($data as $key=>$value)
        {
            $getParameterRequest = str_replace('{@PARM_NAME}','<string>'.$value.'</string>', $getParameterRequest);
        }

        return $getParameterRequest;

    }

    public function soapParseGetParameterResponse($soap)
    {
        $xml = simplexml_load_string($soap);
        $ns = $xml->getNamespaces(true);
        $ns_name = array_keys($ns);
        $data = array();

        $data['ID'] = (string)$xml->children($ns_name[0],true)
            ->Header[0]->children($ns_name[1],true)->ID;

        $response = $xml[0]->children($ns_name[0], true)->Body[0]
            ->children($ns_name[1], true)->GetParameterValuesResponse[0]->children();
        foreach ($response->ParameterList[0]->children('', true) as $key => $value)
        {
            $data['ParameterList'][ (string)$value->Name ] = (string)$value->Value;
        }

        return $data;

    }
//-----------------------------------------------------------------------------
    /*
        private function xml_to_array( $xml )
        {
            $parser = xml_parser_create();
            xml_parser_set_option( $parser, XML_OPTION_CASE_FOLDING, 0 );
            xml_parser_set_option( $parser, XML_OPTION_SKIP_WHITE, 1 );
            xml_parse_into_struct( $parser, $xml, $tags );
            xml_parser_free( $parser );

            $elements = array();
            $stack = array();
            foreach ( $tags as $tag )
            {
                $index = count( $elements );
                if ( $tag['type'] == "complete" || $tag['type'] == "open" )
                {
                    $elements[$index] = array();
                    $elements[$index]['name'] = $tag['tag'];
                    if(isset($tag['attributes'])){
                        $elements[$index]['attributes'] = $tag['attributes'];
                    }
                    if(isset($tag['value'])) {
                        $elements[$index]['content']=$tag['value'];
                    }
                    if ( $tag['type'] == "open" )
                    {    # push
                        $elements[$index]['children'] = array();
                        $stack[count($stack)] = &$elements;
                        $elements = &$elements[$index]['children'];
                    }
                }

                if ( $tag['type'] == "close" )
                {    # pop
                    $elements = &$stack[count($stack) - 1];
                    unset($stack[count($stack) - 1]);
                }
            }
            return $elements[0];
        }

        // In the following function, it should use as below
            $xml = $this->xml_to_array($soap);
            return $xml['children'][SoapEngine::Body]['children'][SoapEngine::SetParameterValuesResponse]['children'][SoapEngine::Status]['content'];

    */
}
