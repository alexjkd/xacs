<?php

namespace App\Models;

use App\Interfaces\ICpeContract;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;

class CPE extends Model implements ICpeContract
{
    /**
     * @var array
     */
    protected $fillable = [
        'connection_request_name', 'connection_request_password',
    ];

    /**
     * @var array
     */
    protected $hidden = [
    ];

    protected $table = 'cpes';
    protected $isLogin = false;
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
        //TODO:Set next action for the session
        return $validated;
    }

    /**
     * @param array $credential
     * @return bool
     */
    public function cpeSavedUserAuth($credential)
    {
        $this->isLogin = true;
        //TODO:Read DB and check the credential

        return false;
    }

    public function cpeSetParameterValues($key_values)
    {
        //build the SOAP body
    }
}
