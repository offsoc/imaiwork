<?php


namespace app\api\controller;

use app\common\enum\FileEnum;
use app\common\model\lianlian\LlConversation;
use app\common\service\UploadService;
use Exception;
use think\response\Json;


/** 上传文件
 * Class UploadController
 * @package app\api\controller
 */
class UploadController extends BaseApiController
{

    /**
     * @notes 上传图片
     * @return Json
     * @author 段誉
     * @date 2022/9/20 18:11
     */
    public function image()
    {
        try {
            $result = UploadService::image(0, $this->userId, FileEnum::SOURCE_USER);
            return $this->success('上传成功', $result);
        } catch (Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    /**
     * @notes 上传文件
     * @return Json
     * @author 段誉
     * @date 2022/9/20 18:11
     */
    public function file()
    {
        try {
          
            $result = UploadService::fileLocal(0, $this->userId, FileEnum::SOURCE_USER);
            return $this->success('上传成功', $result);
        } catch (Exception $e) {
            return $this->fail($e->getMessage());
        }
    }


    /**
     * @notes 上传文件
     * @return Json
     * @author 段誉
     * @date 2022/9/20 18:11
     */
    public function csvFile()
    {
        try {
            $result = UploadService::csvFile(0, $this->userId, FileEnum::SOURCE_USER);
            return $this->success('上传成功', $result);
        } catch (Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    /**
     * @notes 上传视频
     * @return Json
     * @author 段誉
     * @date 2021/12/29 16:27
     */
    public function video()
    {
        try {
            $cid = $this->request->post('cid', 0);
            $result = UploadService::video($cid, $this->userId, FileEnum::SOURCE_USER);
            return $this->success('上传成功', $result);
        } catch (Exception $e) {
            return $this->fail($e->getMessage());
        }
    }


    /**
     * @notes 上传文件
     * @return Json
     * @author 段誉
     * @date 2022/9/20 18:11
     */
    public function audio()
    {
        set_time_limit(0);
        try {
            $result = UploadService::audio(0, $this->userId, FileEnum::SOURCE_USER);
            return $this->success('上传成功', $result);
        } catch (Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    /**
     * @notes 上传文件
     * @return Json
     * @author 段誉
     * @date 2022/9/20 18:11
     */
    public function llAudio()
    {
        $postData = $this->request->post();

        $conversationInfo = LlConversation::where('status', 1)->findOrEmpty($postData['conversation_id']);
        if ($conversationInfo->isEmpty()) {
            return $this->fail('会话异常');
        }
        $filePath = "lianlian/audio/" . $this->userId . "/" . $postData['conversation_id'] . "/";
        if (!is_dir(public_path() . $filePath)) {
            mkdir($filePath, 0755, true);
        }
        try {
            $result = UploadService::audio(0, $this->userId, FileEnum::SOURCE_USER, $filePath, false);
            return $this->success('上传成功', $result);
        } catch (Exception $e) {
            return $this->fail($e->getMessage());
        }
    }
}
