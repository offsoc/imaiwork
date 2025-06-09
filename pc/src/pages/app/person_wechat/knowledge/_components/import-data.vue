<template>
    <popup
        ref="popupRef"
        async
        width="656px"
        :title="props.title"
        :confirm-loading="isLock"
        @confirm="lockConfirm"
        @close="close">
        <div>
            <div>
                <ElSteps simple :active="-1">
                    <ElStep title="上传文件" :icon="FolderOpened"></ElStep>
                    <ElStep title="导入数据" :icon="UploadFilled"></ElStep>
                    <ElStep title="导入完成" :icon="SuccessFilled"></ElStep>
                </ElSteps>
            </div>
            <div class="mt-6">
                <div class="font-bold">
                    <span>一、请按照数据模板的格式准备要导入的数据。</span>
                    <a class="text-primary cursor-pointer" @click="handleDownloadTemplate">
                        点击此处下载《话术导入模板》
                    </a>
                </div>
                <div class="text-xs text-[#C4C6C8] indent-7 mt-1">导入文件请勿超过10MB（约100条数据）</div>
            </div>
            <div class="mt-6">
                <div class="font-bold">
                    <span>二、请选择需要导入的文件</span>
                </div>
                <div class="mt-2">
                    <upload type="file" accept=".csv" list-type="text" :limit="1" @success="getFile">
                        <ElButton type="primary">上传文件</ElButton>
                    </upload>
                </div>
            </div>
        </div>
    </popup>
</template>

<script setup lang="ts">
import { batchAddRobotKeywords } from "@/api/person_wechat";
import Popup from "@/components/popup/index.vue";
import { FolderOpened, SuccessFilled, UploadFilled } from "@element-plus/icons-vue";
const props = defineProps({
    title: {
        type: String,
        default: "",
    },
});

const emit = defineEmits(["success", "close"]);

const route = useRoute();

const file = ref<string>("");

const popupRef = ref<InstanceType<typeof Popup>>();
const open = () => {
    popupRef.value?.open();
};

const close = () => {
    emit("close");
};

const handleDownloadTemplate = () => {
    const origin = window.location.origin;
    const url = `${origin}/static/file/template/speech.csv`;
    window.open(url, "_blank");
};

const getFile = (result: any) => {
    const {
        data: { uri },
    } = result;
    file.value = uri;
};

const handleConfirm = async () => {
    if (!file.value) {
        feedback.notifyError("请上传文件");
        return;
    }
    try {
        await batchAddRobotKeywords({
            file: file.value,
            robot_id: route.query.id,
        });
        emit("success");
        popupRef.value?.close();
        feedback.notifySuccess("导入成功");
    } catch (error) {
        feedback.notifyError(error || "导入失败");
    }
};

const { lockFn: lockConfirm, isLock } = useLockFn(handleConfirm);

defineExpose({
    open,
});
</script>

<style scoped></style>
