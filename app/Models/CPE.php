<?php

namespace App\Models;

use App\Interfaces\ICpeContract;
use App\Models\Facades\AcsFacade;
use App\Models\Facades\SoapFacade;
use App\Models\SoapActionEvent;
use App\Models\SoapActionStatus;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
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
    protected $cpe_info;

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

    private function _actionInsert($event, $data=null)
    {
        $action = new SoapAction();
        $action->setAttribute('event',$event);
        $action->setAttribute('stage',SoapActionStage::STAGE_INITIAL);
        $action->setAttribute('data',$data);

        $this->action()->save($action);
    }

    private function _buildToDoActionChain()
    {
        $this->_actionInsert(SoapActionEvent::BOOTSTRAP);
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
    }

    public function cpeCreate($cpe_info)
    {
        if (empty($cpe_info))
        {
            Log::error('The CPE information is empty, create CPE failed.');
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
        $actions = $this->action()->select('id','fk_cpe_id','event','data','stage','status')
            ->where('status',SoapActionStatus::STATUS_READY)
            ->orderBy('id','desc')
            ->get();

        return $actions;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function cpeHttpAuthActions()
    {
        /*
         * need primary key and foreign key for updating
         * */
        $action = $this->action()->select('id','fk_cpe_id','event','data','stage','status')
            ->where('event',SoapActionEvent::HTTP_AUTH)
            ->where('status',SoapActionStatus::STATUS_READY)
            ->orderBy('id','desc')
            ->get();

        return $action;
    }

    public function cpeDoAction(SoapAction $action)
    {
        $result = array(
            'code' => 500,
            'content' =>'',
        );

        switch ($action->getAttribute('event'))
        {
            case SoapActionEvent::BOOTSTRAP:
            case SoapActionEvent::BOOT:
                if(empty($action->getAttribute('request')))
                {
                    Log::warning('the http content is empty which will cause an abnormal record in session log');
                }
                $data = json_decode($action->getAttribute('data'),true);
                if(empty($data))
                {
                    Log::error('parse the http content failed!');
                    $result['code'] = 500;
                    $result['content'] ='parse the http content failed.';
                    break;
                }
                $result['content'] = SoapFacade::BuildInformResponse($data['ID']);
                $result['code']=200;
                $action->update([
                    'response'=>$result['content'],
                    'status'=> SoapActionStatus::STATUS_FINISHED,
                ]);
                break;
            case SoapActionEvent::HTTP_AUTH:
                $data = json_decode($action->getAttribute('data'),true);
                $http_authentication = $data['authentication'];
                $blankAuthentication = 'Basic ' . base64_encode(':');
                if ( empty($http_authentication) )
                {
                    Log::warning('There is no http authentication headers, but ACS need auth.');
                    $result['code'] = 401;
                    $result['content'] ='';
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
                    $new_action->setAttribute('event',SoapActionEvent::SET_PARAMETER);
                    $new_action->setAttribute('data',json_encode($data));
                    $new_action->setAttribute('request',SoapFacade::BuildSetParameterRequest($data));
                    $this->action()->save($new_action);
                }
                else
                {
                    //todo if auth failed, need to response the 401 and clean the action chain.
                }

                break;
            case SoapActionEvent::SET_PARAMETER:
                //need to send request for the setting parameter

                break;
            default:
                $result['code'] = 403;
                $result['content'] ='';
        }

        return $result;
    }

    /**
     * @param string $httpContent
     * @return array
     */
    public function cpeStartActionChain(string $httpContent, string $authentication=null)
    {
        Log::info('Start the cpe action chain');
        $actions = $this->cpeGetReadyActions();
        $result = array();

        if($actions->isEmpty())
        {
            Log::warning('No action items to do for this CPE.');
            return $result;
        }
        $action = $actions->first();

        if(AcsFacade::acsGetCPEAuthable())
        {
            Log::info('ACS need authentication, add auth action to do authentication first');

            $actions = $this->cpeHttpAuthActions();
            if($actions->isEmpty())
            {
                $this->_actionInsert(SoapActionEvent::HTTP_AUTH,
                    json_encode(array('authentication'=> $authentication)));
            }
            else
            {
                $actions->first()->update(['data' =>
                    json_encode(array('authentication'=> $authentication))]);
            }
            /*
            try {
                $action = $this->cpeHttpAuthActions();
                $action->update(['data' =>
                    json_encode(array('authentication'=> $authentication))]);
            }catch (\Exception $e) {
                if ($e instanceof ModelNotFoundException )
                {
                    $this->_actionInsert(SoapActionEvent::HTTP_AUTH,
                        json_encode(array('authentication'=> $authentication)));
                }
            }
            */

            $action = $this->cpeGetReadyActions()->first();

            goto do_action;
        }

        if ($action->actionGetDirection() == SoapActionDirection::REQUEST)
        {

            $action->setAttribute('request', $httpContent);
            //todo:set action data part according to the session type
            $action->setAttribute('data',
                json_encode(SoapFacade::ParseInformRequest($httpContent)));
        }
        else if ($action->actionGetDirection() == SoapActionDirection::RESPONSE)
        {
            $action->setAttribute('response', $httpContent);
        }
        else
        {
            Log::warning('The action direction is UNKNOWN');
        }

do_action:
        $result = $this->cpeDoAction($action);

        return $result;
    }

    public function cpeInsertActions(SoapAction $action)
    {
        $this->action()->save($action);
    }
}
