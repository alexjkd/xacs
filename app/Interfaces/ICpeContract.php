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
    /**
     * @param array $credential
     * @return bool
     */
    public function cpeBlankUserAuth($credential);

    /**
     * @param array $credential
     * @return bool
     */
    public function cpeSavedUserAuth($credential);
}
