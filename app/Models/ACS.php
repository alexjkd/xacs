<?php

namespace App\Models;

use App\Interfaces\IDataModelContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use App\Interfaces\ICpeContract;

class ACS extends Model
{
    /**
     * @var IDataModelContract
     */
    private $dataModel;
    private $templateParameterStruct;
    private $templateSetParameterRequest;

    public function __construct(IDataModelContract $dataModel)
    {
        $this->dataModel = $dataModel;
        $this->dataModel->dataLoad();
        $this->templateParameterStruct = File::get(base_path('app/Models/xmls/ParameterStruct.xml'));
        $this->templateSetParameterRequest = File::get(base_path('app/Models/xmls/SetParamerterRequest.xml'));
    }
    /**
     * @param array $data
     * @param ICpeContract cpe
     */
    public function acsBuildParameterStruct($data, $cpe)
    {
        $struct = '';
        foreach ($data as $key=>$value)
        {
            $type = $this->dataModel->dataGetType($key);
            $one = $this->templateParameterStruct;
            $one = str_replace('{@KEY}',$key,$one);
            $one = str_replace('{@TYPE}',$type,$one);
            $one = str_replace('{@VALUE}',$key,$one);
            $struct = sprintf("%s%s",$one,$struct);
        }
        $setParameterRequest = $this->templateSetParameterRequest;
        $setParameterRequest = str_replace('{@PARAMETER_NUM}',count($data),$setParameterRequest);
        $setParameterRequest = str_replace('{@PARAMETER_VALUE_STRUCT}',$struct,$setParameterRequest);

        return $setParameterRequest;

    }
}
