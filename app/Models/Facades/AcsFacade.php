<?php
/**
 * Created by PhpStorm.
 * User: melon
 * Date: 2018/12/26
 * Time: 16:53
 */

namespace App\Models\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Class AcsFacade
 * @package App\Models\Facades
 * @method bool acsGetCPEAuthable()
 * @see \App\Models\ACS
 */
class AcsFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'acs';
    }
}
