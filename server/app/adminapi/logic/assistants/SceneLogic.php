<?php

namespace app\adminapi\logic\assistants;

use app\common\model\chat\Scene;
use app\common\logic\BaseLogic;
use app\common\service\FileService;


/**
 * logic
 */
class SceneLogic extends BaseLogic
{
    /**
     * 添加
     * @param array $postData
     * @return bool
     * @author L
     * @data 2024-07-02 16:25:03
     */
    public static function add(array $postData): bool
    {
        try {

            if (isset($postData['logo'])) {
                $postData['logo'] = FileService::setFileUrl($postData['logo']);
            }

            self::$returnData = Scene::create($postData)->toArray();
            return true;
        } catch (\Exception $exception) {
            self::setError($exception->getMessage());
            return false;
        }
    }


    /**
     * 删除
     * @param array $data
     * @return bool
     * @author L
     * @data 2024-07-02 16:25:03
     */
    public static function delete(array $data): bool
    {
        try {
            if (is_string($data['id'])) {
                Scene::destroy(['id' => $data['id']]);
            } else {
                Scene::destroy($data['id']);
            }
            return true;
        } catch (\Exception $exception) {
            self::setError($exception->getMessage());
            return false;
        }
    }


    /**
     * 编辑
     * @param array $postData
     * @return bool
     * @author L
     * @data 2024-07-02 16:25:03
     */
    public static function edit(array $postData): bool
    {
        try {
            $info = Scene::findOrEmpty($postData['id']);
            if ($info->isEmpty()) {
                throw new \Exception("信息异常");
            }

            if (isset($postData['logo'])) {
                $postData['logo'] = FileService::setFileUrl($postData['logo']);
            }

            self::$returnData = Scene::update($postData)->toArray();
            return true;
        } catch (\Exception $exception) {
            self::setError($exception->getMessage());
            return false;
        }
    }


    /**
     * 详情
     * @param int $id
     * @return bool
     * @author L
     * @data 2024-07-02 16:25:03
     */
    public static function detail(int $id): bool
    {
        try {
            $info = Scene::findOrEmpty($id);
            if ($info->isEmpty()) {
                throw new \Exception("信息异常");
            }
            $info->logo = FileService::getFileUrl($info['logo']);
            self::$returnData = $info->toArray();
            return true;
        } catch (\Exception $exception) {
            self::setError($exception->getMessage());
            return false;
        }
    }


    /**
     * 修改状态
     * @param int $id
     * @return bool
     * @author L
     * @data 2024/7/2 15:14
     */
    public static function changeStatus(int $id): bool
    {
        try {
            $assistantsInfo = Scene::findOrEmpty($id);
            if ($assistantsInfo->isEmpty()) {
                throw new \Exception("修改异常");
            }
            $assistantsInfo->status = 1 - $assistantsInfo->status;
            $assistantsInfo->save();
            return true;
        } catch (\Exception $exception) {
            self::setError($exception->getMessage());
            return false;
        }
    }
}
