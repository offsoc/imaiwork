<template>
    <view class="h-screen flex flex-col bg-[#F6F6F6]">
        <u-navbar title="高级设置" :background="{ backgroundColor: '#f6f6f6' }" />
        <view class="grow min-h-0">
            <scroll-view class="h-full" scroll-y>
                <view class="p-[32rpx]">
                    <view class="flex justify-between">
                        <view class="text-[#000000cc] font-bold">执行设备：</view>
                        <view class="text-primary font-bold">智能选择</view>
                    </view>
                    <view class="bg-white rounded-[40rpx] px-[32rpx] mt-[32rpx]">
                        <view
                            class="h-[100rpx] flex items-center border-0 border-b-[1rpx] border-solid border-[#0000000d]">
                            高级设置
                        </view>
                        <view class="py-[32rpx]">
                            <view v-if="false">
                                <view class="flex items-center justify-between">
                                    <view class="text-[#00000080]"> 未检测到联系方式自动私聊 </view>
                                    <view>
                                        <u-switch
                                            :model-value="formData.chat_type == '1'"
                                            :size="32"
                                            active-value="1"
                                            inactive-value="0"
                                            @change="(e:any) => formData.chat_type = e == '1' ? '0' : '1'" />
                                    </view>
                                </view>
                                <template v-if="formData.chat_type == '1'">
                                    <view class="flex gap-x-[30rpx] mt-[32rpx]">
                                        <view class="flex-1">
                                            <view>当前执行</view>
                                            <view class="flex items-center gap-x-2 mt-[24rpx]">
                                                <view
                                                    class="flex-1 flex items-center h-[80rpx] rounded-xl shadow-[0_0_0_1px_#efefef] px-[24rpx]">
                                                    <u-input
                                                        v-model="formData.chat_number"
                                                        type="digit"
                                                        placeholder="请输入"
                                                        placeholder-style="font-size:26rpx;color:rgba(0,0,0,0.2)" /> </view
                                                >次
                                            </view>
                                        </view>
                                        <view class="flex-1">
                                            <view>每次间隔</view>
                                            <view class="flex items-center gap-x-2 mt-[24rpx]">
                                                <view
                                                    class="flex-1 flex items-center h-[80rpx] rounded-xl shadow-[0_0_0_1px_#efefef] px-[24rpx]">
                                                    <u-input
                                                        v-model="formData.chat_interval_time"
                                                        type="digit"
                                                        placeholder="请输入"
                                                        placeholder-style="font-size:26rpx;color:rgba(0,0,0,0.2)" /> </view
                                                >分钟
                                            </view>
                                        </view>
                                    </view>
                                    <view class="mt-[24rpx]">
                                        <view>私信招呼内容：</view>
                                        <view class="mt-[24rpx] rounded-xl shadow-[0_0_0_1px_#efefef] p-[12rpx]">
                                            <u-input
                                                v-model="formData.greeting_content"
                                                type="textarea"
                                                height="160"
                                                placeholder="请输入打招呼内容，为了避免封控，系统将自动调用AI进行去重润色"
                                                placeholder-style="font-size:26rpx;color:rgba(0,0,0,0.2)" />
                                        </view>
                                    </view>
                                    <view class="flex justify-end mt-[24rpx]">
                                        <u-button
                                            type="primary"
                                            :custom-style="{
                                                width: '240rpx',
                                                height: '80rpx',
                                                boxShadow: '0 6px 12px 0 rgba(0, 101, 251, 0.20)',
                                                fontSize: '26rpx',
                                                borderRadius: '24rpx',
                                            }"
                                            @click="
                                                handleGreetingContentSetting(
                                                    GreetingContentSettingTypeEnum.PRIVATE_CHAT
                                                )
                                            "
                                            >AI提示词设置</u-button
                                        >
                                    </view>
                                </template>
                            </view>
                            <!-- <view class="h-[1rpx] bg-[#0000000d] my-[24rpx]"></view> -->
                            <view>
                                <view class="flex items-center justify-between">
                                    <view class="text-[#00000080]"> 自动添加好友 </view>
                                    <view>
                                        <u-switch
                                            :model-value="formData.add_type == '1'"
                                            :size="32"
                                            active-value="1"
                                            inactive-value="0"
                                            @change="(e:any) => formData.add_type = e == '1' ? '0' : '1'" />
                                    </view>
                                </view>
                                <template v-if="formData.add_type == '1'">
                                    <view class="flex gap-x-[30rpx] mt-[32rpx]">
                                        <view class="flex-1">
                                            <view>当前执行</view>
                                            <view class="flex items-center gap-x-2 mt-[24rpx]">
                                                <view
                                                    class="flex-1 flex items-center h-[80rpx] rounded-xl shadow-[0_0_0_1px_#efefef] px-[24rpx]">
                                                    <u-input
                                                        v-model="formData.add_number"
                                                        type="digit"
                                                        placeholder="请输入"
                                                        placeholder-style="font-size:26rpx;color:rgba(0,0,0,0.2)" /> </view
                                                >次
                                            </view>
                                        </view>
                                        <view class="flex-1">
                                            <view>每次间隔</view>
                                            <view class="flex items-center gap-x-2 mt-[24rpx]">
                                                <view
                                                    class="flex-1 flex items-center h-[80rpx] rounded-xl shadow-[0_0_0_1px_#efefef] px-[24rpx]">
                                                    <u-input
                                                        v-model="formData.add_interval_time"
                                                        type="digit"
                                                        placeholder="请输入"
                                                        placeholder-style="font-size:26rpx;color:rgba(0,0,0,0.2)" /> </view
                                                >分钟
                                            </view>
                                        </view>
                                    </view>
                                    <view class="mt-[24rpx]">
                                        <view>申请时备注内容：</view>
                                        <view class="mt-[24rpx] rounded-xl shadow-[0_0_0_1px_#efefef] p-[12rpx]">
                                            <u-input
                                                v-model="formData.remark"
                                                type="textarea"
                                                height="160"
                                                placeholder="请输入打招呼内容，为了避免封控，系统将自动调用AI进行去重润色"
                                                placeholder-style="font-size:26rpx;color:rgba(0,0,0,0.2)" />
                                        </view>
                                    </view>
                                    <view class="flex justify-end mt-[24rpx]">
                                        <u-button
                                            type="primary"
                                            :custom-style="{
                                                width: '240rpx',
                                                height: '80rpx',
                                                boxShadow: '0 6px 12px 0 rgba(0, 101, 251, 0.20)',
                                                fontSize: '26rpx',
                                                borderRadius: '24rpx',
                                            }"
                                            @click="
                                                handleGreetingContentSetting(GreetingContentSettingTypeEnum.ADD_FRIEND)
                                            "
                                            >AI提示词设置</u-button
                                        >
                                    </view>
                                </template>
                            </view>
                        </view>
                    </view>
                </view>
            </scroll-view>
        </view>
        <view class="flex-shrink-0 mt-[32rpx] mb-[68rpx] px-[64rpx]">
            <u-button
                type="primary"
                shape="circle"
                :custom-style="{
                    width: '100%',
                    height: '100rpx',
                    boxShadow: '0 6px 12px 0 rgba(0, 101, 251, 0.20)',
                    fontSize: '26rpx',
                }"
                @click="handleSaveAndReturn"
                >保存并返回</u-button
            >
        </view>
    </view>
    <u-popup v-model="showChatPopup" mode="center" width="90%" border-radius="64" :closeable="false">
        <view class="flex flex-col p-[32rpx]">
            <view class="text-[26rpx] text-center mt-[14rpx]">{{
                currentGreetingContentSettingType == GreetingContentSettingTypeEnum.PRIVATE_CHAT
                    ? "AI提示词设置（私信内容）"
                    : "AI提示词设置（加好友内容）"
            }}</view>
            <view class="rounded-xl bg-[#00000005] p-[18rpx] mt-[46rpx]">
                <view class="max-h-[500rpx] overflow-y-auto">
                    <u-input
                        v-model="formData[currentGreetingContentSettingType]"
                        class="w-full"
                        type="textarea"
                        placeholder="请输入AI提示词"
                        placeholder-style="font-size:26rpx;color:rgba(0,0,0,0.2)"
                        :focus="showChatPopup"
                        :maxlength="1000" />
                </view>
                <view class="text-[26rpx] text-right mt-[14rpx]">
                    {{ formData[currentGreetingContentSettingType].length }}/1000
                </view>
            </view>
            <view class="text-center text-primary mt-2" @click="handleDefaultPrompt"> 一键填写默认数据 </view>
            <view class="flex gap-x-[24rpx] mt-[36rpx] w-full">
                <view class="flex-1">
                    <u-button
                        type="primary"
                        shape="circle"
                        :custom-style="{
                            flex: 1,
                            height: '100rpx',
                            boxShadow: '0 6px 12px 0 rgba(0, 101, 251, 0.20)',
                            fontSize: '26rpx',
                        }"
                        @click="handleChatKeyword"
                        >确定</u-button
                    >
                </view>
            </view>
        </view>
    </u-popup>
</template>

<script setup lang="ts">
import { setFormData } from "@/utils/util";
import { getScenePrompt as getScenePromptApi } from "@/api/app";

enum GreetingContentSettingTypeEnum {
    ADD_FRIEND = "add_friends_prompt",
    PRIVATE_CHAT = "private_message_prompt",
}

const formData = reactive({
    chat_type: "0",
    chat_number: 30,
    chat_interval_time: 10,
    greeting_content: "",
    add_type: "1",
    remark: "",
    add_number: 15,
    add_interval_time: 10,
    private_message_prompt: "",
    add_friends_prompt: "",
});

const showChatPopup = ref(false);

const currentGreetingContentSettingType = ref<GreetingContentSettingTypeEnum>(
    GreetingContentSettingTypeEnum.PRIVATE_CHAT
);

const handleGreetingContentSetting = (type: GreetingContentSettingTypeEnum) => {
    currentGreetingContentSettingType.value = type;
    showChatPopup.value = true;
};

const handleChatKeyword = () => {
    showChatPopup.value = false;
};

const handleDefaultPrompt = () => {
    if (currentGreetingContentSettingType.value == GreetingContentSettingTypeEnum.PRIVATE_CHAT) {
        formData.private_message_prompt = scenePrompt.value.find((item: any) => item.id == 21)?.prompt_text;
    } else {
        formData.add_friends_prompt = scenePrompt.value.find((item: any) => item.id == 22)?.prompt_text;
    }
};

const handleSaveAndReturn = () => {
    uni.$emit("save", formData);
    uni.navigateBack();
};

const scenePrompt = ref<any[]>([]);
const getScenePrompt = async () => {
    const res = await getScenePromptApi();
    scenePrompt.value = res;
};

onLoad(({ data }: any) => {
    getScenePrompt();
    if (data) {
        data = JSON.parse(decodeURIComponent(data));
        setFormData(data, formData);
    }
});
</script>

<style scoped></style>
