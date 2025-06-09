<?php

namespace app\api\controller;

use app\api\lists\chat\ChatLists;
use app\api\logic\ChatLogic;
use think\response\Json;
use app\api\lists\chat\SceneLists;
use app\api\lists\chat\SceneAssistantLists;
use app\api\logic\KnowledgeLogic;

class ChatController extends BaseApiController
{
    public array $notNeedLogin = ['commonChatInfo', 'sceneLists', 'sceneAassistantLists', 'chatLists','sceneChatInfo'];

    /**
     * @desc 会话列表
     * @return Json
     */
    public function chatLists()
    {
        return $this->dataLists(new ChatLists());
    }

    /**
     * @notes 场景列表
     * @return Json
     */
    public function sceneLists(): Json
    {
        return $this->dataLists(new SceneLists());
    }

    /**
     * @notes 场景助手列表
     * @return Json
     */
    public function sceneAassistantLists(): Json
    {
        return $this->dataLists(new SceneAssistantLists());
    }

    /**
     * @desc 助手聊天
     * @return Json
     */
    public function commonChat(): Json
    {
        $params = $this->request->post();

        if (isset($params['indexid']) && !empty($params['indexid'])) {
            $params['scene'] = '通用聊天';
            $params['stream'] = true;
            return KnowledgeLogic::chat($params) ? $this->data(KnowledgeLogic::getReturnData()) : $this->fail(KnowledgeLogic::getError());
        }else{
            return ChatLogic::commonChat($params) ? $this->data(ChatLogic::getReturnData()) : $this->fail(ChatLogic::getError());
        }
       
    }

    /**
     * @desc 场景聊天
     * @return Json
     */
    public function sceneChat(): Json
    {
        $params = $this->request->post();
        if (isset($params['indexid']) && !empty($params['indexid'])) {
            $params['scene'] = '助理聊天';
            $params['stream'] = true;
            return KnowledgeLogic::sceneChat($params) ? $this->data(KnowledgeLogic::getReturnData()) : $this->fail(KnowledgeLogic::getError());
        }else{
            return ChatLogic::sceneChat($params) ? $this->data(ChatLogic::getReturnData()) : $this->fail(ChatLogic::getError());
        }
        
    }

    /**
     * 提示词聊天
     * @return Json
     * @author L
     * @data 2024/6/12 14:04
     */
    public function promptChat()
    {
        $params = $this->request->post();
        return ChatLogic::promptChat($params) ? $this->data(ChatLogic::getReturnData()) : $this->fail(ChatLogic::getError());
    }

    /**
     * @desc 获取通用聊天助手
     * @return Json
     */
    public function commonChatInfo()
    {
        return ChatLogic::commonChatInfo() ? $this->data(ChatLogic::getReturnData()) : $this->fail(ChatLogic::getError());
    }

    /**
     * @desc 场景聊天 - 助理信息
     * @return Json
     */
    public function sceneChatInfo()
    {
        $params = $this->request->get();
        return ChatLogic::sceneChatInfo($params) ? $this->data(ChatLogic::getReturnData()) : $this->fail(ChatLogic::getError());
    }


    /**
     * @desc 删除聊天记录
     * @return Json
     */
    public function deleteChat()
    {
        $params = $this->request->post();
        return ChatLogic::deleteChat($params) ? $this->success() : $this->fail(ChatLogic::getError());
    }

    /**
     * @desc 场景聊天 - 助理信息
     * @return Json
     */
    public function chatLogs()
    {
        $params = $this->request->get();
        return ChatLogic::chatLogs($params) ? $this->data(ChatLogic::getReturnData()) : $this->fail(ChatLogic::getError());
    }
}
