<?php

namespace App\Models;

use App\Interfaces\ICpeContract;
use App\Models\Facades\AcsFacade;
use App\Models\Actions\BOOTSTRAP;
use App\Models\Actions\HTTP_AUTH;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use stdClass;

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

        if(AcsFacade::acsGetCPEAuthable())
        {
            //TODO: Should generate a ReqestUsername and RequestPassword for the device accordingly
            /*
            $this->setAttribute('ConnectionRequestUser',$cpe_info['DeviceId']['ProductClass']);
            $this->setAttribute('ConnectionRequestPassword',
                                 password_hash($cpe_info['DeviceId']['SerialNumber'],
                                 PASSWORD_DEFAULT));
            */
        }

        $this->setAttribute('ConnectionRequestURL',
            $cpe_info['ParameterList']['Device.ManagementServer.ConnectionRequestURL']);
        $this->save();
        $this->cpeInsertAction(new BOOTSTRAP());

    }

    public function action()
    {
        return $this->hasMany(SoapAction::class,'fk_cpe_id','id');
    }

    public function cpeInsertAction(SoapAction $action)
    {
        $this->action()->save($action);
    }

    public function cpeCleanReadyActions()
    {
        $this->action()->where('status',SoapActionStatus::STATUS_READY)
            ->delete();
    }
    /**
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function cpeGetReadyActions()
    {
        /*
         * need primary key and foreign key for updating
         * */
        $actions = $this->action()->select('id','cwmpid','fk_cpe_id','event','data','stage','status')
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
        $action = $this->action()->select('id','cwmpid','fk_cpe_id','event','data','stage','status')
            ->where('event',SoapActionEvent::HTTP_AUTH)
            ->where('status',SoapActionStatus::STATUS_READY)
            ->orderBy('id','desc')
            ->get();

        return $action;
    }

    /**
     * @param string $httpContent
     * @return array
     */
    public function cpeStartActionChain(string $httpContent=null, string $authentication=null)
    {
        Log::info('Start the cpe action chain');
        $actions = $this->cpeGetReadyActions();

        if($actions->isEmpty())
        {
            Log::warning('No action items to do for this CPE.');
            return null;
        }
        $action = $actions->first();

        if(AcsFacade::acsGetCPEAuthable())
        {
            Log::info('ACS need authentication, add auth action to do authentication first');

            $actions = $this->cpeHttpAuthActions();
            if($actions->isEmpty())
            {
                $this->cpeInsertAction(new HTTP_AUTH(
                    array('authentication'=> $authentication)));
            }

            $action = $this->cpeGetReadyActions()->first();
        }
        return $action->HandlerOnAcs($httpContent, $authentication);
    }

}
