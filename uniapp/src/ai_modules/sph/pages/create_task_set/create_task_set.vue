<template>
    <view class="h-screen flex flex-col bg-[#F6F6F6]">
        <u-navbar title="高级设置" :background="{ backgroundColor: '#f6f6f6' }" />
        <view class="grow min-h-0">
            <scroll-view class="h-full" scroll-y>
                <view class="p-[32rpx]">
                    <view class="bg-white rounded-[40rpx] px-[32rpx] mt-[32rpx]">
                        <view
                            class="h-[100rpx] flex items-center border-0 border-b-[1rpx] border-solid border-[#0000000d]">
                            高级设置
                        </view>
                        <view class="py-[32rpx]">
                            <view>
                                <view class="text-[#00000080] flex items-center gap-x-2" @click="showOCRTip = true">
                                    线索识别方式
                                    <view
                                        class="text-white bg-[rgba(0,0,0,0.3)] w-[24rpx] h-[24rpx] rounded-full flex items-center justify-center"
                                        ><u-icon name="info" :size="16" color="#ffffff"></u-icon
                                    ></view>
                                </view>
                                <view class="mt-[32rpx]">
                                    <u-radio-group v-model="formData.ocr_type">
                                        <u-radio :name="1" label-size="24" size="28">云端OCR识别</u-radio>
                                        <u-radio :name="2" label-size="24" size="28">本地识别</u-radio>
                                    </u-radio-group>
                                </view>
                            </view>
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
                            <view class="mt-[24rpx]">
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
                                        <view class="flex items-center justify-between">
                                            <view>加好友备注内容：</view>
                                            <u-switch
                                                :model-value="formData.add_remark_enable"
                                                :size="32"
                                                active-value="1"
                                                inactive-value="0"
                                                @change="(e: any) => formData.add_remark_enable = e == 1 ? 0 : 1" />
                                        </view>
                                        <template v-if="formData.add_remark_enable == 0">
                                            <view class="mt-[24rpx] rounded-xl shadow-[0_0_0_1px_#efefef] p-[12rpx]">
                                                <u-input
                                                    v-model="formData.remark"
                                                    type="textarea"
                                                    height="160"
                                                    placeholder="请输入打招呼内容，为了避免封控，系统将自动调用AI进行去重润色"
                                                    placeholder-style="font-size:26rpx;color:rgba(0,0,0,0.2)" />
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
                                                            GreetingContentSettingTypeEnum.ADD_FRIEND
                                                        )
                                                    "
                                                    >AI提示词设置</u-button
                                                >
                                            </view>
                                        </template>
                                        <view v-if="formData.add_remark_enable == 1" class="mt-[24rpx]">
                                            <view class="flex flex-wrap gap-2">
                                                <view
                                                    v-for="(item, index) in formData.remarks"
                                                    :key="index"
                                                    class="border border-solid border-[#E5E5E5] rounded-md px-4 py-2 flex items-center cursor-pointer"
                                                    @click="handleEditRemark(index)">
                                                    <view class="text-xs">{{ item }}</view>
                                                    <view class="w-[2rpx] h-[24rpx] bg-[#E5E5E5] mx-2"></view>
                                                    <view
                                                        class="flex-shrink-0 w-4 h-4 rounded-full border border-solid border-[#E5E5E5] flex items-center justify-center"
                                                        @click.stop="handleDeleteRemark(index)">
                                                        <u-icon name="close" :size="16" color="#00000080"></u-icon>
                                                    </view>
                                                </view>
                                            </view>
                                            <view class="flex justify-end mt-[24rpx]">
                                                <u-button
                                                    type="primary"
                                                    :custom-style="{
                                                        boxShadow: '0 6px 12px 0 rgba(0, 101, 251, 0.20)',
                                                        fontSize: '26rpx',
                                                        borderRadius: '24rpx',
                                                    }"
                                                    @click="showAddRemark = true">
                                                    新增备注</u-button
                                                >
                                            </view>
                                        </view>
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
    <u-popup v-model="showOCRTip" mode="bottom" border-radius="24" @close="showOCRTip = false">
        <view>
            <view class="text-center text-lg font-bold py-3"> 线索识别方式 </view>
            <u-line />
            <view class="w-full overflow-hidden text-[#4C4B6A] text-xs p-6">
                <view>
                    本地识别（每条扣{{
                        getOCRLocalToken
                    }}算力）使用系统内置识别逻辑完成，识别率较依赖本地环境，复杂图片可能不够精准
                </view>
                <view class="mt-3">
                    云端OCR识别（每条扣 {{ getOCRCloudToken }} 算力）使用云端OCR服务识别微信号，每条线索消耗{{
                        getOCRCloudToken
                    }}算力，识别率更高，支持更复杂的图片和场景
                </view>
            </view>
        </view>
    </u-popup>
    <u-popup v-model="showAddRemark" mode="center" border-radius="24" width="90%" closeable>
        <view class="w-full p-4">
            <view class="text-center text-lg font-bold"> 加好友备注文案 </view>
            <view class="border border-solid border-[#E5E5E5] rounded-md p-2 my-4">
                <u-input
                    v-model="addRemarkContent"
                    type="textarea"
                    height="160"
                    maxlength="100"
                    placeholder="请输入打招呼内容"
                    placeholder-style="font-size:26rpx;color:rgba(0,0,0,0.2)" />
            </view>
            <u-button
                type="primary"
                :custom-style="{
                    height: '100rpx',
                    boxShadow: '0 6px 12px 0 rgba(0, 101, 251, 0.20)',
                    fontSize: '26rpx',
                    borderRadius: '24rpx',
                }"
                @click="handleAddRemark">
                立即保存
            </u-button>
        </view>
    </u-popup>
</template>

<script setup lang="ts">
import { setFormData } from "@/utils/util";
import { getWeChatLists } from "@/api/person_wechat";
import { useDictOptions } from "@/hooks/useDictOptions";
import { useAppStore } from "@/stores/app";
import { useUserStore } from "@/stores/user";
import { TokensSceneEnum } from "@/enums/appEnums";
enum GreetingContentSettingTypeEnum {
    ADD_FRIEND = "add_friends_prompt",
    PRIVATE_CHAT = "private_message_prompt",
}
const appStore = useAppStore();
const userStore = useUserStore();

const getOCRCloudToken = computed(() => {
    return userStore.getTokenByScene(TokensSceneEnum.SPH_OCR)?.score;
});

const getOCRLocalToken = computed(() => {
    return userStore.getTokenByScene(TokensSceneEnum.SPH_LOCAL_OCR)?.score;
});

const getWechatRemarks = computed(() => {
    return appStore.config.wechat_remarks || [];
});

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
    wechat_id: "",
    wechat_reg_type: 0,
    ocr_type: 1,
    add_remark_enable: 1,
    remarks: getWechatRemarks.value || [],
});

const showOCRTip = ref(false);
const showAddRemark = ref(false);
const editRemarkIndex = ref(-1);
const addRemarkContent = ref("");

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

const handleAddRemark = () => {
    if (!addRemarkContent.value) {
        uni.$u.toast("请输入加好友备注内容");
        return;
    }
    if (editRemarkIndex.value == -1) {
        formData.remarks.push(addRemarkContent.value);
    } else {
        formData.remarks[editRemarkIndex.value] = addRemarkContent.value;
    }
    editRemarkIndex.value = -1;
    addRemarkContent.value = "";
    showAddRemark.value = false;
};

const handleDeleteRemark = (index: number) => {
    formData.remarks.splice(index, 1);
};

const handleEditRemark = (index: number) => {
    editRemarkIndex.value = index;
    addRemarkContent.value = formData.remarks[index];
    showAddRemark.value = true;
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
