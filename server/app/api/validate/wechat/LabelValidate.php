<?php

namespace app\api\validate\wechat;

use app\common\validate\BaseValidate;

/**
 * 标签校验
 * Class LabelValidate
 * @package app\api\validate\wechat
 * @author Qasim
 */
class LabelValidate extends BaseValidate
{

    protected $rule = [
        'id' => 'require',
        'device_code' => 'require',
        'device_status' => 'require',
        'device_model' => 'require',
        'sdk_version' => 'require',
    ];


    protected $message = [
        'id.require' => '请输入主键ID',
        'device_code.require' => '请输入设备码',
        'device_status.require' => '请输入设备状态',
        'device_model.require' => '请输入设备型号',
        'sdk_version.require' => '请输入SDK版本',
    ];


    /**
     * @notes 添加标签
     * @return LabelValidate
     */
    public function sceneAdd()
    {
        return $this->only(['device_code', 'device_status', 'device_model', 'sdk_version']);
    }

    /** 
     * @notes 更新标签
     * @return LabelValidate
     */
    public function sceneUpdate()
    {
        return $this->only(['device_code', 'is_used']);
    }

    /**
     * @notes 删除标签
     * @return LabelValidate
     */
    public function sceneRemove()
    {
        return $this->only(['id']);
    }
}
