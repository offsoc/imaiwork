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
                                            @change="(e: any) => formData.chat_type = e == '1' ? '0' : '1'" />
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
                                            @change="(e: any) => formData.add_type = e == '1' ? '0' : '1'" />
                                    </view>
                                </view>
                                <template v-if="formData.add_type == '1'">
                                    <view class="mt-[32rpx]">
                                        <view class="mb-3">加微微信</view>
                                        <data-select
                                            v-model="formData.wechat_id"
                                            multiple
                                            :localdata="optionsData.wechatLists"></data-select>
                                    </view>
                                    <view class="mt-[32rpx]">
                                        <view class="mb-3">加微规则</view>
                                        <data-select
                                            v-model="formData.wechat_reg_type"
                                            :localdata="[
                                                { text: '全部', value: 0 },
                                                { text: '微信号', value: 1 },
                                                { text: '手机号', value: 2 },
                                            ]"></data-select>
                                    </view>
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
</template>

<script setup lang="ts">
import { setFormData } from "@/utils/util";
import { getWeChatLists } from "@/api/person_wechat";
import { useDictOptions } from "@/hooks/useDictOptions";

enum GreetingContentSettingTypeEnum {
    ADD_FRIEND = "add_friends_prompt",
    PRIVATE_CHAT = "private_message_prompt",
}

const formData = reactive({
    chat_type: "0",
    chat_number: 30,
    chat_interval_time: 10,
    greeting_content: "",
    add_type: "0",
    remark: "",
    add_number: 15,
    add_interval_time: 10,
    private_message_prompt: "",
    add_friends_prompt: "",
    wechat_id: "",
    wechat_reg_type: 0,
});

const currentGreetingContentSettingType = ref<GreetingContentSettingTypeEnum>(
    GreetingContentSettingTypeEnum.PRIVATE_CHAT
);

const { optionsData } = useDictOptions<{
    wechatLists: any[];
}>({
    wechatLists: {
        api: getWeChatLists,
        params: { page_size: 1000 },
        transformData: (res: any) =>
            res.lists?.map((item: any) => ({
                text: item.wechat_nickname,
                value: item.wechat_id,
            })),
    },
});

const handleGreetingContentSetting = (type: GreetingContentSettingTypeEnum) => {
    currentGreetingContentSettingType.value = type;
    uni.$u.route({
        url: "/ai_modules/sph/pages/task_prompt/task_prompt",
        params: {
            type,
            prompt: JSON.stringify(formData[type]),
        },
    });
};

const handleSaveAndReturn = () => {
    if (formData.add_type == "1" && formData.wechat_id.length == 0) {
        uni.$u.toast("请选择加微微信");
        return;
    }
    uni.$emit("save", formData);
    uni.navigateBack();
};

onLoad(({ data }: any) => {
    if (data) {
        data = JSON.parse(decodeURIComponent(data));
        setFormData(data, formData);
    }
});

onShow(() => {
    uni.$on("save", (data: any) => {
        const { type, prompt } = data;
        if (type == GreetingContentSettingTypeEnum.PRIVATE_CHAT) {
            formData.private_message_prompt = prompt;
        } else {
            formData.add_friends_prompt = prompt;
        }
        uni.$off("save");
    });
});
</script>

<style scoped></style>
