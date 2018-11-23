<?php
/**
 * Created by PhpStorm.
 * User: melon
 * Date: 2018/11/23
 * Time: 09:57
 */

namespace App\Interfaces;


interface IInformContract
{
    public static function ValidSoap(string $soap_xml):bool;
    public function informBuildBody($soap_xml);
    public function informBodyAttribute($key,$value);
    public function informGetBody();

}
