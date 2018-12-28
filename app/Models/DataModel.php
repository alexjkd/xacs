<?php

namespace App\Models;

use App\Interfaces\IDataModelContract;
use Illuminate\Database\Eloquent\Model;

class DataModel extends Model implements IDataModelContract
{
    public function dataLoad()
    {

    }

    public function dataGetType(string $attribute)
    {
       return "xstns:string";
    }
}
