<?php

namespace app\api\validate\sv;

use app\common\validate\BaseValidate;

/**
 * 发布设置校验
 * Class PublishValidate
 * @package app\api\validate\sv
 * @author Qasim
 */
class PublishDetailValidate extends BaseValidate
{

    protected $rule = [
        'id' => 'require',
        'publish_time' =>'require',
        'retry_time' => 'require',
        'url' => 'require',
    ];



    protected $message = [
        'id.require' => '任务id不能为空',
        'publish_time.require' => '请输入发布结束时间',
        'retry_time.require' => '请输入任务重试时间',
        'url.require' => '请输入视频地址'
    ];


    /**
     * @notes 添加
     * @return Validate
     */
    public function sceneAdd()
    {
        return $this->only([ 'name', 'accounts', 'video_setting_id', 'publish_start', 'publish_end']);
    }

    /**
     * @notes 更新
     * @return Validate
     */
    public function sceneUpdate()
    {
        return $this->only(['id', 'name', 'accounts', 'video_setting_id', 'publish_start', 'publish_end']);
    }

    /**
     * @notes 删除
     * @return Validate
     */
    public function sceneDelete()
    {
        return $this->only(['id']);
    }

    /**
     * @notes 详情
     * @return Validate
     */
    public function sceneDetail()
    {
        return $this->only(['id']);
    }
    /**
     * @notes 详情
     * @return Validate
     */
    public function sceneLists()
    {
        return $this->only(['id']);
    }

    public function sceneRetry() {
        return $this->only(['id', 'retry_time']);
    }
    public function sceneTest() {
        return $this->only(['url']);
    }
}
