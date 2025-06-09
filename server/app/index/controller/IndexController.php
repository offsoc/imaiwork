<?php

namespace app\index\controller;

use app\BaseController;
use app\common\logic\PaymentLogic;
use app\common\model\assistants\Assistants;
use app\common\service\ConfigService;
use app\common\service\JsonService;
use app\common\service\openai\GptChatService;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use think\Exception;
use think\facade\Log;
use think\Request;

class IndexController extends BaseController
{

    /**
     * @notes 主页
     * @param string $name
     * @return \think\response\Json|\think\response\View
     * @author 段誉
     * @date 2022/10/27 18:12
     */
    public function index($name = '你好,imai')
    {
        $template = app()->getRootPath() . 'public/pc/index.html';
        $request = new Request();
        if ($request->isMobile()) {
            $template = app()->getRootPath() . 'public/pc/index.html';
        }
        if (file_exists($template)) {
            return view($template);
        }
        return JsonService::success($name);
    }


    public function getConfig()
    {
        dd(config('sd.ultra'));
        return config('sd.ultra');
    }


    public function aa($contents = "")
    {
        if (empty($contents)) {
            $openData = [
                'messages' => [
                    [
                        'role'    => "system",
                        "content" => "请帮我翻译并润色以下文字 *** " . "一个中国的现在时尚女孩，她皮肤偏白，非常漂亮
                        
                        .。"
                    ]
                ],
                'model'    => "gpt-4o"
            ];

            $chat = (new GptChatService($openData))->chat();
            if (empty($chat['choices'][0]['message']['content'])) {
                throw new Exception("请求异常");
            }
            $contents = $chat['choices'][0]['message']['content'];
        }
        dump($contents);
        $url     = "https://api.stability.ai/v2beta/stable-image/generate/ultra";
        $headers = [
            'authorization' => "Bearer " . env('STABLE.OPEN_AI_KEY'),
            "accept"        => "image/*",
        ];
        try {
            $client   = new Client();
            $response = $client->post($url, [
                'headers'   => $headers,
                'multipart' => [
                    [
                        'name'     => 'none',
                        'contents' => ''
                    ],
                    [
                        'name'     => 'prompt',
                        'contents' => $contents //生成文字
                    ],
                    [
                        'name'     => 'output_format',
                        'contents' => "webp" //类型
                    ]
                ],
                "verify"    => false,
                "timeout"   => 120
            ]);
            if ($response->getStatusCode() == 200) {
                file_put_contents('./lighthouse.webp', $response->getBody()->getContents());
                echo "success";
            } else {
                throw new Exception($response->getBody()->getContents() . $response->getStatusCode());
            }
        } catch (GuzzleException $guzzleException) {
            dd($guzzleException->getMessage());
        }
    }


    public function aaa()
    {
        $hosturl           = "wss://tts-api.xfyun.cn/v2/tts";
        //语音评测
        $url_components = parse_url($hosturl);

        $gDate = gmdate('D, d M Y H:i:s');
        $apiKey = "a731a63663723cf29ab03661a67467d9";
        $apiSecret = "YzVlNWRiNTNkODg2ZDUwYjNhODljYmQz";

        $signStr = "host: tts-api.xfyun.cn\ndate: " . $gDate . "\nGET " . $url_components['path'] . " HTTP/1.1";
        dump($signStr);
        $sha = base64_encode(hash_hmac('sha256', $signStr, $apiSecret, true));
        dump($sha);
        $authParams = sprintf(
            'api_key="%s", algorithm="%s", headers="%s", signature="%s"',
            $apiKey,
            'hmac-sha256',
            'host date request-line',
            $sha
        );
        dump($authParams);

        $authorization = base64_encode($authParams);

        dump($authorization);
        //
        //        $params = http_build_query([
        //            "authorization" => $authorization,
        //            "date" => $gDate,
        //            "host" => $url_components['host'],
        //        ]);
        //        $sendUrl = $hosturl . "?" . $params;
        //

        $params = http_build_query([
            "authorization" => $authorization,
            "date" => $gDate,
            "host" => $url_components['host'],
        ]);
        $sendUrl = $hosturl . "?" . $params;

        dump($sendUrl);
        die;





        $config = new ClientConfig();
        $config->setFragmentSize(8096);
        $config->setTimeout(15);
        $config->setHeaders([
            'X-Custom-Header' => 'Foo Bar Baz',
        ]);
        $config->setContextOptions(['ssl' => ['verify_peer' => false, 'verify_peer_name' => false]]);

        $client = new WebSocketClient($sendUrl, new ClientConfig());
        $sendData = [
            'common' => [
                'app_id' => '6b24933c',
            ],
            'business' => [
                'aue' => 'lame',
                'vcn' => 'xiaoyan',
                'pitch' => 50,
                'speed' => 50,
                'sfl' => 1,
            ],
            'data' => [
                'status' => 2,
                'text' => base64_encode("你觉得今天天气号码？"),
            ],
        ];
        $client->send(json_encode($sendData, JSON_UNESCAPED_UNICODE));
        while ($client->isConnected()) {
            $message = $client->receive(); // 接收消息

            if ($message) {
                dump("Received message: $message");
                // 在这里处理接收到的消息，可以根据你的需求进行解析和处理
            }
        }
    }



    public function ddd()
    {
        //语音评测
        $hosturl           = "wss://ise-api.xfyun.cn/v2/open-ise";
        $url_components = parse_url($hosturl);

        $gDate = gmdate('D, d M Y H:i:s \G\M\T');
        $apiKey = "a731a63663723cf29ab03661a67467d9";
        $apiSecret = "YzVlNWRiNTNkODg2ZDUwYjNhODljYmQz";

        $signStr = "host: " . $url_components['host'] . "\ndate: " . $gDate . "\nGET " . $url_components['path'] . " HTTP/1.1";
        dump($signStr);
        $sha = base64_encode(hash_hmac('sha256', $signStr, $apiSecret, true));
        dump($sha);
        $authParams = sprintf(
            'api_key="%s", algorithm="%s", headers="%s", signature="%s"',
            $apiKey,
            'hmac-sha256',
            'host date request-line',
            $sha
        );
        dump($authParams);

        $authorization = base64_encode($authParams);

        dump($authorization);
        //
        //        $params = http_build_query([
        //            "authorization" => $authorization,
        //            "date" => $gDate,
        //            "host" => $url_components['host'],
        //        ]);
        //        $sendUrl = $hosturl . "?" . $params;
        //

        $params = http_build_query([
            "authorization" => $authorization,
            "date" => $gDate,
            "host" => $url_components['host'],
        ]);
        $sendUrl = $hosturl . "?" . $params;

        dump($sendUrl);





        $config = new ClientConfig();
        $config->setFragmentSize(8096);
        $config->setTimeout(15);
        $config->setHeaders([
            'X-Custom-Header' => 'Foo Bar Baz',
        ]);
        $config->setContextOptions(['ssl' => ['verify_peer' => false, 'verify_peer_name' => false]]);

        $client = new WebSocketClient($sendUrl, new ClientConfig());
        $sendData = [
            'common' => [
                'app_id' => '6b24933c',
            ],
            'business' => [
                'category' => 'read_sentence',
                'sub' => 'ise',
                'ent' => 'cn_vip',
                'cmd' => "ssb",
                'auf' => "audio/L16;rate=16000",
                'aue' => "raw",
                'text' => '\uFEFF' . "今天天气怎么样",
                'ttp_skip' => True,
                'aus' => 1,
            ],
            //            'data' => [
            //                'status' => 2,
            //                'text' => base64_encode("你觉得今天天气号码？"),
            //            ],
        ];
        $client->send(json_encode($sendData, JSON_UNESCAPED_UNICODE));
        while ($client->isConnected()) {
            $message = $client->receive(); // 接收消息

            if ($message) {
                dump("Received message: $message");
                // 在这里处理接收到的消息，可以根据你的需求进行解析和处理
            }
        }
    }


    public function wsss()
    {
        $config = new ServerConfig();
        $config->setIsSsl(false)->setAllowSelfSigned(true)
            ->setCryptoType(STREAM_CRYPTO_METHOD_SSLv23_SERVER)
            //               ->setLocalCert("./tests/certs/cert.pem")->setLocalPk("./tests/certs/key.pem")
            ->setPort(8888);

        $websocketServer = new WebSocketServer(new ServerHandler(), $config);

        $websocketServer->run();
    }


    public function ccc()
    {

        $ffmpeg_command = 'F:/ffmpeg/bin/ffmpeg.exe -i ./xf.mp3 -af silencedetect=noise=-30dB:d=1 -f null -';

        // 执行命令并获取输出
        $output = shell_exec($ffmpeg_command . " 2>&1");
        // 解析输出，获取静默部分信息
        $lines    = explode("\n", $output);
        $silences = [];
        foreach ($lines as $line) {
            if (str_contains($line, 'silence_duration') !== false) {
                // 提取静默开始时间
                preg_match('/silence_duration: (\d+\.\d+)/', $line, $matches);
                if (isset($matches[1])) {
                    $silences[] = round($matches[1], 2);
                }
            }
        }
        return $silences;
    }

    public function eee()
    {

        $ffmpeg_command = 'F:/ffmpeg/bin/ffmpeg.exe -i ./xf.mp3';

        // 执行命令并获取输出
        $output = shell_exec($ffmpeg_command . " 2>&1");
        $outputs    = explode("\n", $output);

        // 解析输出，获取静默部分信息
        foreach ($outputs as $line) {
            if (str_contains($line, 'bitrate:') !== false) {
                preg_match('/bitrate: (\d+) kb\/s/', $line, $matches);
                $bitrate = isset($matches[1]) ? $matches[1] . ' kbps' : 'Unknown';
            }
            if (str_contains($line, 'Hz,') !== false) {
                preg_match('/ (\d+) Hz,/', $line, $matches);
                $sample_rate = isset($matches[1]) ? $matches[1] . ' Hz' : 'Unknown';
            }
        }
        dump($bitrate);
        dump($sample_rate);
        return 1;
    }

    public function fff()
    {

        //        $ffmpeg_command = 'F:/ffmpeg/bin/ffmpeg.exe -i ./wf.mp3 -lavfi showfreqs=mode=line:fscale=log -f  null -';
        $ffmpeg_command = 'F:/ffmpeg/bin/ffmpeg.exe -i ./xf.mp3 -lavfi "showspectrumpic=s=1024x512:legend=disabled" -frames:v 1 output.png';

        // 执行命令并获取输出
        $output = shell_exec($ffmpeg_command  . " 2>&1");
        dd($output);
        $outputs    = explode("\n", $output);

        // 解析输出，获取静默部分信息
        foreach ($outputs as $line) {
            if (str_contains($line, 'bitrate:') !== false) {
                preg_match('/bitrate: (\d+) kb\/s/', $line, $matches);
                $bitrate = isset($matches[1]) ? $matches[1] . ' kbps' : 'Unknown';
            }
            if (str_contains($line, 'Hz,') !== false) {
                preg_match('/ (\d+) Hz,/', $line, $matches);
                $sample_rate = isset($matches[1]) ? $matches[1] . ' Hz' : 'Unknown';
            }
        }
        dump($bitrate);
        dump($sample_rate);
        return 1;
    }

    public function sss()
    {
        Log::write(json_encode($this->request->post(), JSON_UNESCAPED_UNICODE), "qw");
        return json_encode([
            'code' => 200,
            'msg' => "success"
        ], JSON_UNESCAPED_UNICODE);

        $list = Assistants::select()->toArray();
        $newList = [];
        foreach ($list as $k => &$v) {
            if (!empty($v['form_info'])) {
                $v['form_info'] = "[" . $v['form_info'] . "]";
                $v['form_info'] = json_decode($v['form_info'], true);
                if (!empty($v['form_info'])) {
                    $newList[$k]['form_info'] = $v['form_info'][0];
                    $newList[$k]['id'] = $v['id'];
                }
                //                dump($v['form_info']);
            }
        }
        foreach ($newList as $vv) {
            Assistants::update([
                'id' => $vv['id'],
                'form_info' => $vv['form_info']
            ]);
        }
        die;
        PaymentLogic::ChangeUserTokens();
        die;
        $config = ConfigService::get("xf", 'info');
        $a = json_encode(json_encode([
            'appid' => $config['appid'],
            'apiSecret' => $config['apiSecret'],
            'apiKey' => $config['apiKey'],
            'audioFile' => public_path() . 'd9626774f3b3d8e64e512b640abf770d.mp3',
            'text' => "当然没问题。我是以简体中文回答你的问题的。如果你有任何其他问题或需要帮助，请告诉我！"
        ], JSON_UNESCAPED_UNICODE), JSON_UNESCAPED_UNICODE);
        $a = shell_exec("E:/Code/Py/AudioTest/Scripts/python E:/Code/Py/AudioTest/pythonProject/ise_ws_python3_demo.py $a 2>&1");
        dd($a);
        $a = json_decode('{"code": 200, "data": {"xml_result": {"read_sentence": {"lan": "cn", "type": "study", "version": "7,0,0,1024", "rec_paper": {"read_sentence": {"accuracy_score": "0.000000", "beg_pos": "0", "content": "\u6d63\u72b2\u951b\u5c7c\u7c96\u6fb6\u59d8\u65c0\u6d94\u581f\u7271\u3002", "emotion_score": "0.000000", "end_pos": "3235", "except_info": "28676", "fluency_score": "0.000000", "integrity_score": "0.000000", "is_rejected": "true", "phone_score": "1.363637", "time_len": "3235", "tone_score": "2.727271", "total_score": "2.139521", "sentence": {"beg_pos": "0", "content": "\u6d63\u72b2\u951b\u5c7c\u7c96\u6fb6\u59d8\u65c0\u6d94\u581f\u7271", "end_pos": "3235", "fluency_score": "0.000000", "phone_score": "0.000000", "time_len": "3235", "tone_score": "0.000000", "total_score": "0.000000", "word": [{"beg_pos": "0", "content": "\u6d63", "end_pos": "2602", "symbol": "huan4", "time_len": "2602", "syll": [{"beg_pos": "0", "content": "sil", "dp_message": "0", "end_pos": "2602", "rec_node_type": "sil", "time_len": "2602", "phone": {"beg_pos": "0", "content": "sil", "dp_message": "0", "end_pos": "2602", "rec_node_type": "sil", "time_len": "2602"}}, {"beg_pos": "2602", "content": "\u6d63", "dp_message": "16", "end_pos": "2602", "rec_node_type": "paper", "symbol": "huan4", "time_len": "0", "phone": [{"content": "h", "dp_message": "16", "is_yun": "0", "perr_msg": "16", "rec_node_type": "paper"}, {"content": "uan", "dp_message": "16", "is_yun": "1", "mono_tone": "TONE4", "perr_msg": "16", "rec_node_type": "paper"}]}]}, {"beg_pos": "2602", "content": "\u72b2", "end_pos": "3219", "symbol": "cai2", "time_len": "617", "syll": {"beg_pos": "2602", "content": "\u72b2", "dp_message": "0", "end_pos": "3219", "rec_node_type": "paper", "symbol": "cai2", "time_len": "617", "phone": [{"beg_pos": "2602", "content": "c", "dp_message": "0", "end_pos": "2609", "is_yun": "0", "perr_level_msg": "3", "perr_msg": "1", "rec_node_type": "paper", "time_len": "7"}, {"beg_pos": "2609", "content": "ai", "dp_message": "0", "end_pos": "3219", "is_yun": "1", "mono_tone": "TONE2", "perr_level_msg": "1", "perr_msg": "0", "rec_node_type": "paper", "time_len": "610"}]}}, {"beg_pos": "3219", "content": "\u951b", "end_pos": "3235", "symbol": "ben1", "time_len": "16", "syll": [{"beg_pos": "3219", "content": "sil", "dp_message": "0", "end_pos": "3235", "rec_node_type": "sil", "time_len": "16", "phone": {"beg_pos": "3219", "content": "sil", "dp_message": "0", "end_pos": "3235", "rec_node_type": "sil", "time_len": "16"}}, {"beg_pos": "3235", "content": "\u951b", "dp_message": "16", "end_pos": "3235", "rec_node_type": "paper", "symbol": "ben1", "time_len": "0", "phone": [{"content": "b", "dp_message": "16", "is_yun": "0", "perr_msg": "16", "rec_node_type": "paper"}, {"content": "en", "dp_message": "16", "is_yun": "1", "mono_tone": "TONE1", "perr_msg": "16", "rec_node_type": "paper"}]}]}, {"beg_pos": "3235", "content": "\u5c7c", "end_pos": "3235", "symbol": "wu4", "time_len": "0", "syll": {"beg_pos": "3235", "content": "\u5c7c", "dp_message": "16", "end_pos": "3235", "rec_node_type": "paper", "symbol": "wu4", "time_len": "0", "phone": [{"content": "_u", "dp_message": "16", "is_yun": "0", "perr_msg": "16", "rec_node_type": "paper"}, {"content": "u", "dp_message": "16", "is_yun": "1", "mono_tone": "TONE4", "perr_msg": "16", "rec_node_type": "paper"}]}}, {"beg_pos": "3235", "content": "\u7c96", "end_pos": "3235", "symbol": "mo4", "time_len": "0", "syll": {"beg_pos": "3235", "content": "\u7c96", "dp_message": "16", "end_pos": "3235", "rec_node_type": "paper", "symbol": "mo4", "time_len": "0", "phone": [{"content": "m", "dp_message": "16", "is_yun": "0", "perr_msg": "16", "rec_node_type": "paper"}, {"content": "o", "dp_message": "16", "is_yun": "1", "mono_tone": "TONE4", "perr_msg": "16", "rec_node_type": "paper"}]}}, {"beg_pos": "3235", "content": "\u6fb6", "end_pos": "3235", "symbol": "chan2", "time_len": "0", "syll": {"beg_pos": "3235", "content": "\u6fb6", "dp_message": "16", "end_pos": "3235", "rec_node_type": "paper", "symbol": "chan2", "time_len": "0", "phone": [{"content": "ch", "dp_message": "16", "is_yun": "0", "perr_msg": "16", "rec_node_type": "paper"}, {"content": "an", "dp_message": "16", "is_yun": "1", "mono_tone": "TONE2", "perr_msg": "16", "rec_node_type": "paper"}]}}, {"beg_pos": "3235", "content": "\u59d8", "end_pos": "3235", "symbol": "pin1", "time_len": "0", "syll": {"beg_pos": "3235", "content": "\u59d8", "dp_message": "16", "end_pos": "3235", "rec_node_type": "paper", "symbol": "pin1", "time_len": "0", "phone": [{"content": "p", "dp_message": "16", "is_yun": "0", "perr_msg": "16", "rec_node_type": "paper"}, {"content": "in", "dp_message": "16", "is_yun": "1", "mono_tone": "TONE1", "perr_msg": "16", "rec_node_type": "paper"}]}}, {"beg_pos": "3235", "content": "\u65c0", "end_pos": "3235", "symbol": "mei4", "time_len": "0", "syll": {"beg_pos": "3235", "content": "\u65c0", "dp_message": "16", "end_pos": "3235", "rec_node_type": "paper", "symbol": "mei4", "time_len": "0", "phone": [{"content": "m", "dp_message": "16", "is_yun": "0", "perr_msg": "16", "rec_node_type": "paper"}, {"content": "ei", "dp_message": "16", "is_yun": "1", "mono_tone": "TONE4", "perr_msg": "16", "rec_node_type": "paper"}]}}, {"beg_pos": "3235", "content": "\u6d94", "end_pos": "3235", "symbol": "cen2", "time_len": "0", "syll": {"beg_pos": "3235", "content": "\u6d94", "dp_message": "16", "end_pos": "3235", "rec_node_type": "paper", "symbol": "cen2", "time_len": "0", "phone": [{"content": "c", "dp_message": "16", "is_yun": "0", "perr_msg": "16", "rec_node_type": "paper"}, {"content": "en", "dp_message": "16", "is_yun": "1", "mono_tone": "TONE2", "perr_msg": "16", "rec_node_type": "paper"}]}}, {"beg_pos": "3235", "content": "\u581f", "end_pos": "3235", "symbol": "zhuan4", "time_len": "0", "syll": {"beg_pos": "3235", "content": "\u581f", "dp_message": "16", "end_pos": "3235", "rec_node_type": "paper", "symbol": "zhuan4", "time_len": "0", "phone": [{"content": "zh", "dp_message": "16", "is_yun": "0", "perr_msg": "16", "rec_node_type": "paper"}, {"content": "uan", "dp_message": "16", "is_yun": "1", "mono_tone": "TONE4", "perr_msg": "16", "rec_node_type": "paper"}]}}, {"beg_pos": "3235", "content": "\u7271", "end_pos": "3235", "symbol": "ge1", "time_len": "0", "syll": {"beg_pos": "3235", "content": "\u7271", "dp_message": "16", "end_pos": "3235", "rec_node_type": "paper", "symbol": "ge1", "time_len": "0", "phone": [{"content": "g", "dp_message": "16", "is_yun": "0", "perr_msg": "16", "rec_node_type": "paper"}, {"content": "e", "dp_message": "16", "is_yun": "1", "mono_tone": "TONE1", "perr_msg": "16", "rec_node_type": "paper"}]}}]}}}}}}, "task_time": 2.28, "msg": "succes"}', true);

        dd($a['data']['xml_result']['read_sentence']['rec_paper']['read_sentence']);
    }
}
