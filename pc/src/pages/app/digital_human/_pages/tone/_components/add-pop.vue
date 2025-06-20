<template>
    <div>
        <popup
            title="上传本地音频"
            ref="popupRef"
            :async="true"
            width="500px"
            confirm-button-text=""
            cancel-button-text=""
            @close="handleClose">
            <div class="text-[#B4B4B4] text-sm mb-4">
                <ElAlert title="注意：请根据性别上传对应的音频文件" type="warning" show-icon closable />
            </div>
            <ElForm :model="formData" :rules="formRules" ref="formRef" label-position="top" :disabled="isLock">
                <ElFormItem label="音色名称" prop="name">
                    <ElInput v-model="formData.name" placeholder="请输入音色名称" />
                </ElFormItem>
                <ElFormItem label="性别" prop="gender">
                    <ElSelect v-model="formData.gender" placeholder="请选择性别">
                        <ElOption value="male" label="男"></ElOption>
                        <ElOption value="female" label="女"></ElOption>
                    </ElSelect>
                </ElFormItem>
                <ElFormItem label="使用模型" prop="model_version">
                    <ElSelect v-model="formData.model_version" placeholder="请选择模型">
                        <ElOption
                            v-for="item in modelChannel"
                            :key="item.id"
                            :value="item.id"
                            :label="item.name"></ElOption>
                    </ElSelect>
                </ElFormItem>
                <ElFormItem label="上传音频" prop="url">
                    <upload
                        class="w-full"
                        drag
                        type="audio"
                        list-type="text"
                        :limit="1"
                        :size="20"
                        :accept="getAccept"
                        @success="handleFileSuccess">
                        <div class="h-[90px] rounded-lg flex flex-col justify-center items-center">
                            <img src="@/assets/images/audio.png" class="w-[48px] h-[48px]" />
                            <div class="text-primary text-xl mt-2">上传录音文件</div>
                            <div class="text-xs text-[#B4B4B4] text-center mt-4">
                                文件不超过20MB,不支持gif/avif等音频格式以外的文件
                            </div>
                        </div>
                    </upload>
                </ElFormItem>
            </ElForm>

            <div class="flex justify-end mt-3">
                <ElButton type="primary" :loading="isLock" @click="lockSubmit"
                    >开始转写
                    <template v-if="tokensValue">（消耗{{ tokensValue }}算力）</template>
                </ElButton>
            </div>
        </popup>
    </div>
</template>

<script setup lang="ts">
import Popup from "@/components/popup/index.vue";
import { voiceClone } from "@/api/digital_human";
import type { FormInstance } from "element-plus";
import { useAppStore } from "@/stores/app";
import { useUserStore } from "@/stores/user";
import { TokensSceneEnum } from "@/enums/appEnums";
import { DigitalHumanModelVersionEnum } from "../../../_enums";

const emit = defineEmits<{
    (event: "success"): void;
    (event: "close"): void;
}>();

const appStore = useAppStore();
const userStore = useUserStore();
const { userTokens } = toRefs(userStore);
const modelChannel = computed(() => appStore.getDigitalHumanConfig?.channel);

const tokensValue = computed(() => {
    return {
        [DigitalHumanModelVersionEnum.STANDARD]: userStore.getTokenByScene(TokensSceneEnum.HUMAN_VOICE)?.score,
        [DigitalHumanModelVersionEnum.SUPER]: userStore.getTokenByScene(TokensSceneEnum.HUMAN_VOICE_PRO)?.score,
        [DigitalHumanModelVersionEnum.ADVANCED]: userStore.getTokenByScene(TokensSceneEnum.HUMAN_VOICE_ADVANCED)?.score,
        [DigitalHumanModelVersionEnum.ELITE]: userStore.getTokenByScene(TokensSceneEnum.HUMAN_VOICE_ELITE)?.score,
    }[formData.model_version];
});

const popupRef = shallowRef<InstanceType<typeof Popup>>();
const formRef = ref<FormInstance>();
const formData = reactive({
    url: "",
    name: "",
    gender: "male" as "male" | "female",
    model_version: DigitalHumanModelVersionEnum.STANDARD,
});

const formRules = {
    name: [{ required: true, message: "请输入音色名称" }],
    url: [{ required: true, message: "请上传音频" }],
    model_version: [{ required: true, message: "请选择模型" }],
};

const fileLists = ref<any[]>([]);

const getAccept = computed(() => {
    return ".mp3,.m4a,.wav";
});

const handleFileSuccess = (result: any) => {
    const {
        data: { uri },
    } = result;
    formData.url = uri;
};

const open = () => {
    popupRef.value.open();
};

const handleSubmit = async () => {
    if (userTokens.value < tokensValue.value) {
        feedback.msgPowerInsufficient();
        return;
    }
    await formRef.value.validate();
    try {
        await voiceClone(formData);
        popupRef.value?.close();
        userStore.getUser();
        emit("success");
    } catch (error) {
        feedback.msgError(error || "转写失败!");
    }
};

const handleClose = () => {
    emit("close");
};

const { lockFn: lockSubmit, isLock } = useLockFn(handleSubmit);

watch(
    () => modelChannel.value,
    (newVal) => {
        if (newVal && newVal.length > 0) {
            formData.model_version = newVal[0].id;
        }
    },
    {
        immediate: true,
    }
);

defineExpose({
    open,
});
</script>

<style scoped></style>
