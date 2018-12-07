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

    /*
     * $expected_data = array(
            'ID'=>1641837687,
            'DeviceId'=> array(
                'Manufacturer'=>'NETGEAR',
                'OUI'=>'08028E',
                'ProductClass'=>'V7610',
                'SerialNumber'=>'08028eef0b00',
            ),
            'EventStruct'=>array(
                'BOOTSTRAP'=>0,
                'BOOT'=>1,
            ),
            'MaxEnvelopes'=>1,
            'CurrentTime'=>'2018-06-26T13:19:13+00:00',
            'RetryCount'=>0,
            'ParameterList'=>array(
                'Device.RootDataModelVersion'=>'',
                'Device.DeviceInfo.HardwareVersion'=>'V7610A',
                'Device.DeviceInfo.SoftwareVersion'=>'V2.2.2.26_ST2',
                'Device.DeviceInfo.ProvisioningCode'=>'Telstra1',
                'Device.ManagementServer.ParameterKey'=>'(null)',
                'Device.ManagementServer.ConnectionRequestURL'=>
                    'http://79.0.0.179:7547/bf93a276cc0501c7161c29beb4c32b7d',
                'Device.X_00600F_wansupervision.ActiveWANInterface'=>'Ethernet IPoE',
                'Device.X_00600F_wansupervision.MBBUSBDetected'=>0,
            ),
        );*/

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
        //$cpe_info = $this->cpe_info;
        if (empty($cpe_info))
        {
            return null;
        }

        foreach ($cpe_info['DeviceId'] as $key=>$value)
        {
            $this->setAttribute($key, $value);
        }

        $this->setAttribute('ConnectionRequestUser',$cpe_info['DeviceId']['ProductClass']);
        $this->setAttribute('ConnectionRequestPassword',password_hash($cpe_info['DeviceId']['SerialNumber'],PASSWORD_DEFAULT));

        $this->save();
        return $this;
    }

    public function cpeCreateEntry(IInformContract $inform)
    {
        $body = $inform->informGetBody();
        foreach ($body as $key=>$value)
        {
            $this->setAttribute($key, $value);
        }
        //TODO: Should generate a ReqestUsername and RequestPassword for the device accordingly

        $this->setAttribute('ConnectionRequestUser',$body['ProductClass']);
        $this->setAttribute('ConnectionRequestPassword',password_hash($body['SerialNumber'],PASSWORD_DEFAULT));

        $this->save();
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
    public function cpeActionNextStep()
    {

    }


}
