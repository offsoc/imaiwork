<template>
    <div class="h-full flex flex-col py-6">
        <div class="grow min-h-0">
            <el-scrollbar>
                <div class="px-[30px] flex flex-col gap-4">
                    <!-- 多条信息回复策略 -->
                    <div class="flex flex-col">
                        <div>短时间内多条信息回复策略</div>
                        <div class="mt-2">
                            <el-radio-group v-model="formData.multiple_type">
                                <el-radio :value="0">逐条回复</el-radio>
                                <el-radio :value="1">
                                    <el-tooltip content="当用户在两分钟以外，没有继续发送消息，则将多条信息合并回复">
                                        <div class="flex items-center gap-2">
                                            <span>合并回复</span>
                                            <Icon name="el-icon-WarningFilled" color="#9E9E9E" />
                                        </div>
                                    </el-tooltip>
                                </el-radio>
                                <el-radio :value="2">
                                    <el-tooltip content="当用户在两分钟以外，没有继续发送消息，只回复最后一条信息">
                                        <div class="flex items-center gap-2">
                                            <span> 只回复最后一条 </span>
                                            <Icon name="el-icon-WarningFilled" color="#9E9E9E" />
                                        </div>
                                    </el-tooltip>
                                </el-radio>
                            </el-radio-group>
                        </div>
                    </div>
                    <!-- 语音消息回复策略 -->
                    <div class="flex flex-col">
                        <div>语音消息回复策略（目前只支持微信）</div>
                        <div class="mt-2">
                            <el-switch v-model="formData.voice_enable" :active-value="1" :inactive-value="0" />
                        </div>
                    </div>
                    <!-- 聊天轮数策略 -->
                    <div class="flex flex-col">
                        <div>聊天轮数策略</div>
                        <div class="mt-2">
                            <el-input-number
                                v-model="formData.number_chat_rounds"
                                class="mr-2"
                                :precision="0"
                                :min="3"
                                :max="50"
                                size="small" />条
                        </div>
                    </div>
                    <!-- 图片固定回复策略 -->
                    <div class="flex flex-col">
                        <div>智能体被调用时，接收到图片进行固定回复</div>
                        <div class="mt-2">
                            <el-switch v-model="formData.image_enable" :active-value="1" :inactive-value="0" />
                        </div>
                        <div class="mt-2" v-if="formData.image_enable == 1">
                            <el-input
                                v-model="formData.image_reply"
                                type="textarea"
                                placeholder="点击输入您要回复的内容"
                                resize="none"
                                :rows="4" />
                        </div>
                    </div>
                    <!-- 停止回复策略 -->
                    <div class="flex flex-col">
                        <div class="">智能体被调用时，接收到某些信息后停止对该条信息进行回复</div>
                        <div class="mt-2">
                            <el-switch v-model="formData.stop_enable" :active-value="1" :inactive-value="0" />
                        </div>
                        <div class="mt-2" v-if="formData.stop_enable == 1">
                            <el-input
                                v-model="formData.stop_keywords"
                                type="textarea"
                                placeholder="点击输入您要检测的词语"
                                resize="none"
                                :rows="4" />
                        </div>
                    </div>
                    <div class="flex flex-col">
                        <div>
                            <div>分段回复</div>
                        </div>
                        <div class="flex items-center justify-between gap-2">
                            <div class="text-xs">
                                用户在聊天界面看到的 AI 回复 不是一次性的大段话，而是按段落逐条展示
                            </div>
                            <el-switch v-model="formData.paragraph_enable" :active-value="1" :inactive-value="0" />
                        </div>
                    </div>
                </div>
            </el-scrollbar>
        </div>
        <!-- 保存按钮 -->
        <div class="flex items-center justify-center mt-4">
            <el-button
                type="primary"
                class="w-[318px] !rounded-full !h-[50px]"
                :loading="isLockSubmit"
                @click="lockSubmit">
                保存
            </el-button>
        </div>
    </div>
</template>

<script setup lang="ts">
import { saveReplyStrategy, getReplyStrategy } from "@/api/ai_application/agent";
import { useLockFn } from "@/hooks/useLockFn";
import feedback from "@/utils/feedback";
import { setFormData } from "@/utils/util";

/**
 * @description 智能体回复策略设置组件
 * @summary 用户可以配置多种场景下的自动回复行为。
 */

const props = defineProps<{
    agentId: string | number;
}>();

const emit = defineEmits<{ (event: "close"): void }>();

// 回复策略表单数据
const formData = reactive({
    multiple_type: 0, // 多轮回复类型 0: 逐条回复 1: 合并回复 2：只回复最后一条
    number_chat_rounds: 3, // 聊天轮数策略
    voice_enable: 0, // 语音消息回复策略 0：关闭 1：开启
    image_enable: 0, // 图片消息回复策略 0：关闭 1：开启
    image_reply: "", // 图片消息回复内容
    stop_enable: 0, // 停止回复策略 0：关闭 1：开启
    stop_keywords: "", // 停止回复关键词(用英文;分割)
    paragraph_enable: 0, // 段落回复策略 0：关闭 1：开启
});

/**
 * @description 验证并保存回复策略
 */
const handleConfirm = async () => {
    if (formData.image_enable == 1 && !formData.image_reply) {
        feedback.msgError("请输入图片消息回复内容");
        return;
    }
    if (formData.stop_enable == 1 && !formData.stop_keywords) {
        feedback.msgError("请输入停止回复关键词");
        return;
    }
    await saveReplyStrategy({
        ...formData,
        robot_id: props.agentId,
    });
};

// 使用 useLockFn 防止重复提交
const { lockFn: lockSubmit, isLock: isLockSubmit } = useLockFn(handleConfirm);

/**
 * @description 获取已保存的回复策略
 */
const getReplyStrategyFn = async () => {
    try {
        const data = await getReplyStrategy({ id: props.agentId });
        // 使用 setFormData 更新表单，确保只更新存在的字段
        if (data) {
            setFormData(data, formData);
        }
    } catch (error) {
        console.error("获取回复策略失败:", error);
    }
};

// 组件挂载时获取数据
onMounted(() => {
    getReplyStrategyFn();
});
</script>

<style scoped></style>
