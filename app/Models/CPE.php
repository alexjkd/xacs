<?php

namespace App\Models;

use App\Interfaces\ICpeContract;
use App\Models\Facades\SoapFacade;
use App\Models\SoapActionType;
use App\Models\SoapActionStatus;



use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Notifications\Action;
use Illuminate\Support\Facades\Log;
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
            'event'=> SoapActionType::EVENT_HTTP_AUTH,
            'stage'=> SoapActionStatus::STAGE_INITIAL,
            'data'=>''
        ],
        [
             'event'=>SoapActionType::EVENT_BOOTSTRAP,
             'stage'=>SoapActionStatus::STAGE_INITIAL,
             'data'=>'',
        ]);

        if(!$acs->acsGetCPEAuthable())
        {
            $this->actionsTodo = array(
            [
                'event'=>SoapActionType::EVENT_BOOTSTRAP,
                'stage'=>SoapActionStatus::STAGE_INITIAL,
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
        print_r("CPE::__constructor__\n");
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
/*
    public function cpeLogin($credential)
    {
        $status_code = CPE::STATUS_FAILED;

        if ($this->_savedUserAuth($credential))
        {
            $status_code = CPE::STATUS_SUCCEEDED;
        }
        return $status_code;
    }
*/
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
            ->where('status',SoapActionStatus::STATUS_READY)
            ->orderBy('id','desc')
            ->get();

        return $actions;
    }

    public function cpeGetActionsTodo()
    {
        return $this->actionsTodo;
    }

    public function cpeDoAction(SoapAction $action)
    {
        $result = array(
            'code'=>403,
            'content'=>'',
        );
        switch ($action->getAttribute('event'))
        {
            case SoapActionType::EVENT_BOOTSTRAP:
            case SoapActionType::EVENT_BOOT:
                if(empty($action->getAttribute('request')))
                {
                    Log::warning('the request is empty which will cause an abnormal record in session log');
                }
                $data = json_decode($action->getAttribute('data'),true);
                $result['content'] = SoapFacade::soapBuildInformResponse($data['ID']);
                $result['code']=200;
                $action->update([
                    'response'=>$result['content'],
                    'status'=> SoapActionStatus::STATUS_FINISHED,
                ]);
                break;
            case SoapActionType::EVENT_HTTP_AUTH:
                $http_authentication = $action->getAttribute('data');
                $blankAuthentication = 'Basic ' . base64_encode(':');
                if ( empty($http_authentication) )
                {
                    break;
                }

                if ( $http_authentication === $blankAuthentication )
                {
                    $result['code'] = 200;
                    $result['content'] ='';
                    Log::info("CPE() auth with user blank");
                    response('',200);
                    $new_action = new SoapAction();
                    //get new user and password
                    $data = array (
                        'Device.ManagementServer.Username'=>'08028E-08028EEF0B00',
                        'Device.ManagementServer.Password'=>'xwhSLiQAwOXlLeVX',
                        'Device.ManagementServer.URL'=>'http://58.162.32.33/cwmp/cwmp'
                    );
                    $new_action->setAttribute('event',SoapAction::EVENT_SETPARAMETER);
                    $new_action->setAttribute('request',$data);
                    $this->action()->save($new_action);
                    $this->cpeDoAction($new_action);
                }
                else
                {

                }

                break;
            case SoapActionType::EVENT_SETPARAMETER:

                break;
            default:
        }

        return $result;
    }

    public function cpeHandleSoap($soap)
    {
    }


}
