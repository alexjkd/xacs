<?php

namespace App\Models;

use App\Interfaces\ICpeContract;
use App\Models\SoapAction;

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
    protected $actionsTodo;
    protected $cpe_info;

    public function __construct($attributes = array())
    {
        parent::__construct($attributes);
        $this->_setInitialEvents();
    }

    private function _setInitialEvents()
    {
        $this->actionsTodo = array(
          [
              'event'=>SoapAction::EVENT_HTTP_AUTH,
              'stage'=>SoapAction::STAGE_INITIAL,
              'data'=>''
          ],
          [
              'event'=>SoapAction::EVENT_BOOTSTRAP,
              'stage'=>SoapAction::STAGE_INITIAL,
              'data'=>'',
          ],
        );
    }
    /**
     * @param array $credential
     * @return bool
     */
    private function _savedUserAuth($credential)
    {
        //TODO: need to travel all the CPE table to check the connection user

        $validated = ($credential['name'] == $this->getAttribute('ConnectionRequestUser')) &&
            password_verify($credential['password'],$this->getAttribute('ConnectionRequestPassword'));

        $this->isLogin = $validated;

        return $validated;
    }

    private function _actionInsert($event, $stage)
    {
        $action = new SoapAction();
        $action->event = $event;
        $action->stage = $stage;

        $this->action()->save($action);
    }

    private function _buildToDoActionChain()
    {
        $actionsTodo = $this->actionsTodo;
        foreach ($actionsTodo as $index=>$action)
        {
            $this->_actionInsert($action['event'],
                                 $action['stage'],
                                 $action['data']);
        }

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
        $this->setAttribute('ConnectionRequestPassword',
                             password_hash($cpe_info['DeviceId']['SerialNumber'],
                             PASSWORD_DEFAULT));
        $this->save();
        $this->_buildToDoActionChain();

        return $this;
    }

    public function cpeLogin($credential)
    {
        $status_code = CPE::STATUS_FAILED;

        if ($this->_savedUserAuth($credential))
        {
            $status_code = CPE::STATUS_SUCCEEDED;
        }
        return $status_code;
    }

    public function action()
    {
        return $this->hasMany(SoapAction::class,'fk_cpe_id','id');
    }

    public function cpeGetReadyActions()
    {
        $actionList = $this->action()->select('event','stage','status')
            ->where('status',SoapAction::STATUS_READY)->get();

        return $actionList;
    }

    public function cpeGetActionsTodo()
    {
        return $this->actionsTodo;
    }

    /**
     * @param \App\Models\SoapAction $action
     * @return array
     */
    public function cpeDoActionEvent(SoapAction $action)
    {
        return $soap;
    }

    public function cpeHandleSoap($soap)
    {
    }


}
