<?php
/**
 * Created by PhpStorm.
 * User: melon
 * Date: 2018/11/21
 * Time: 14:17
 */

namespace App\Interfaces;


use Illuminate\Http\Request;


interface ICpeContract
{
    public function cpeCreate($cpe_info);
    //public function cpeLogin($credential);
    public function cpeHandleSoap($soap);
}
