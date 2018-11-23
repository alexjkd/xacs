<?php

namespace App\Models;

use App\Interfaces\ICpeContract;
use App\Interfaces\IInformContract;
use Illuminate\Database\Eloquent\Model;


class CPE extends Model implements ICpeContract
{
    /**
     * @var array
     */
    protected $fillable = [
        'ConnectionRequestUser',
        'ConnectionRequestPassword',
        'Manufacturer',
        'OUI',
        'ProductClass',
        'SerialNumber',
        'ConnectionRequestURL'
    ];

    /**
     * @var array
     */
    protected $hidden = [
        'ConnectionRequestPassword'
    ];

    protected $table = 'cpes';
    protected $isLogin = false;
    protected $inform;

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
        //TODO: need to travel all the CPE table to check the connection user

        $validated = ($credential['user'] == $this->getAttribute('ConnectionRequestUser')) &&
            password_verify($credential['password'],$this->getAttribute('ConnectionRequestPassword'));

        $this->isLogin = $validated;

        return $validated;
    }

    public function cpeCreateEntry(IInformContract $inform)
    {
        $body = $inform->informGetBody();
        foreach ($body as $key=>$value)
        {
            $this->setAttribute($key, $value);
        }
        //TODO:Generate a ReqestUsername and RequestPassword for the device accordingly

        $this->setAttribute('ConnectionRequestUser',$body['ProductClass']);
        $this->setAttribute('ConnectionRequestPassword',password_hash($body['SerialNumber'],PASSWORD_DEFAULT));

        $this->save();
    }
}
