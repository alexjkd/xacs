<?php

namespace App\Models;

use App\Interfaces\ICpeContract;
use App\Interfaces\IInformContract;
use Illuminate\Database\Eloquent\Model;


class CPE extends Model implements ICpeContract
{
    const STATUS_FAILED=401;
    const STATUS_SUCCEEDED=200;

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
    protected $actionQueue;
    protected $cpe_info;

    public function __construct($attributes = array())
    {
        parent::__construct($attributes);
    }

    /**
     * @param array $credential
     * @return bool
     */
    private function _SavedUserAuth($credential)
    {
        //TODO: need to travel all the CPE table to check the connection user

        $validated = ($credential['name'] == $this->getAttribute('ConnectionRequestUser')) &&
            password_verify($credential['password'],$this->getAttribute('ConnectionRequestPassword'));

        $this->isLogin = $validated;

        return $validated;
    }

    public function cpeCreate($cpe_info)
    {
        if (empty($cpe_info))
        {
            return null;
        }

        foreach ($cpe_info['DeviceId'] as $key=>$value)
        {
            $this->setAttribute($key, $value);
        }

        //TODO: Should generate a ReqestUsername and RequestPassword for the device accordingly
        $this->setAttribute('ConnectionRequestUser',$cpe_info['DeviceId']['ProductClass']);
        $this->setAttribute('ConnectionRequestPassword',password_hash($cpe_info['DeviceId']['SerialNumber'],PASSWORD_DEFAULT));

        $this->save();
        return $this;
    }

    public function cpeLogin($credential)
    {
        $status_code = CPE::STATUS_FAILED;

        if ($this->_SavedUserAuth($credential))
        {
            $status_code = CPE::STATUS_SUCCEEDED;
        }
        return $status_code;
    }
/*
    public function cpeInitActionQueue()
    {

    }

    public function cpeActionEnqueue()
    {

    }

    public function cpeActionDequeue()
    {

    }
*/

    public function cpeBuildActions()
    {

    }
    public function cpeActionNextStep()
    {

    }


}
