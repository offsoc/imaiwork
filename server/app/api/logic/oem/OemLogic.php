<?php

namespace app\api\logic\oem;

use app\common\model\oem\Oem;
use app\common\service\FileService;
use app\api\logic\ApiLogic;
use think\facade\Request;

/**
 * OemLogic
 * @desc oem
 */
class OemLogic extends ApiLogic
{
    public static function check()
    {
        $domain = Request::domain();
        $domain = str_replace(['https://', 'http://'], '', $domain);

        $result = Oem::where('domain', $domain)->where('status', 1)->findOrEmpty();

        if ($result->isEmpty()) {
            self::$returnData = [
                'is_oem' => 0,
                'domain' => $domain,
                'logo_url' => '',
                'name' => '',
            ];
        } else {
            self::$returnData = [
                'is_oem' => 1,
                'domain' => $result->domain,
                'name' => $result->name,
                'logo_url' => FileService::getFileUrl($result->logo_url),
            ];
        }

        return true;
    }
}
