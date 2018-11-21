<?php

namespace App\Models;

use App\Interfaces\ICpeContract;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;

class CPE extends Model implements ICpeContract
{
    protected $table = 'cpes';
    protected $isLogin;

    public function __construct()
    {
        $this->isLogin = false;
    }

    /**
     * @param array $credential
     * @return bool
     */
    public function cpeBlankUserAuth($credential)
    {
        $validated = false;
        $valid_with_empty = 'Basic ' . base64_encode(':');
        if ($credential['authentication'] == $valid_with_empty)
        {
            $validated = true;
        }

        return $validated;
    }

    /**
     * @param array $credential
     * @return bool
     */
    public function cpeSavedUserAuth($credential)
    {
        $this->isLogin = true;
        return false;
    }
}
