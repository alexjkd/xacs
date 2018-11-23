<?php
/**
 * Created by PhpStorm.
 * User: melon
 * Date: 2018/11/23
 * Time: 13:43
 */

namespace App\Interfaces;


interface IDataModelContract
{
    public function dataLoad();
    public function dataGetType(string $attribute);
}
