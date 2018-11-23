<?php

namespace App\Models;

use App\Interfaces\IInformContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;

class Inform extends Model implements IInformContract
{
    /**
     * @var array
     */
    public $body;

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
            print Inform::_display_error($error);
        }
        libxml_clear_errors();
    }

    public static function ValidSoap(string $soap_xml)
    {
        $xml = new \DOMDocument();
        $xml->loadXML($soap_xml);
        if (!$xml->schemaValidate(base_path('app/Models/xml/cwmp-1-2.xsd')))
        {
            Inform::display_errors();
            return false;
        }
        return true;
    }

    public function informBuildBody($soap_xml)
    {
        //TODO:Build body array: key=>value
    }

    public function informBodyAttribute($key,$value)
    {

    }

    /**
     *
     */
    public function informGetBody()
    {
        //TODO:return the body array
    }
}

