<?php

namespace App\Models;

use App\Interfaces\ICpeContract;
use App\Models\Facades\SoapFacade;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

use stdClass;

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

    private function _setInitialEvents()
    {
        $acs = app()->make('App\Models\ACS');
        $count1 = ACS::$count;
        print_r("CPE: _setInitialEvents() acs count = $count1\n");
        $acs = app()->make('App\Models\ACS');
        $count1 = ACS::$count;
        print_r("CPE: _setInitialEvents() acs count = $count1\n");
        $this->actionsTodo = array([
            'event'=> SoapAction::EVENT_HTTP_AUTH,
            'stage'=> SoapAction::STAGE_INITIAL,
             'data'=>''
        ],
        [
             'event'=>SoapAction::EVENT_BOOTSTRAP,
             'stage'=>SoapAction::STAGE_INITIAL,
             'data'=>'',
        ]);

        if(!$acs->acsGetCPEAuthable())
        {
            $this->actionsTodo = array(
            [
                'event'=>SoapAction::EVENT_BOOTSTRAP,
                'stage'=>SoapAction::STAGE_INITIAL,
                'data'=>'',
            ]);
        }
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

    private function _actionInsert($event, $stage, $data)
    {
        $action = new SoapAction();
        $action->setAttribute('event',$event);
        $action->setAttribute('stage',$stage);
        $action->setAttribute('data',$data);

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
//-------------------------------------------------------------------------
    public static function make(stdClass $object)
    {
        return new self($object);
    }

    public static function makeCollection(array $collection)
    {
        foreach($collection AS $key => $Item)
        {
            $collection[$key] = self::make($Item);
        }
        return $collection;
    }
//-------------------------------------------------------------------------
    public function __construct($attributes = array())
    {
        parent::__construct($attributes);
        $this->_setInitialEvents();
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

    /**
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function cpeGetReadyActions()
    {
        /*
         * need primary key and foreign key for updating
         * */
        $actions = $this->action()->select('id','fk_cpe_id','event','stage','status')
            ->where('status',SoapAction::STATUS_READY)->get();

        return $actions;
    }

    public function cpeGetActionsTodo()
    {
        return $this->actionsTodo;
    }

    public function cpeDoAction(SoapAction $action)
    {
        $soap ='';
        switch ($action->getAttribute('event'))
        {
            case SoapAction::EVENT_BOOTSTRAP:
            case SoapAction::EVENT_BOOT:
                $soap = SoapFacade::soapBuildInformResponse(
                    $action->getAttribute('data'));
                $action->update([
                    'soap'=>$soap,
                    'status'=> SoapAction::STATUS_FINISHED,
                ]);
                break;
            case SoapAction::EVENT_HTTP_AUTH:
                $http_authentication = $action->getAttribute('data');
                break;
            default:

        }

        return $soap;
    }

    public function cpeHandleSoap($soap)
    {
    }


}
