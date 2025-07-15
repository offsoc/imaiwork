<template>
    <popup ref="popupRef" title="模拟发布视频任务" width="600px" async @close="close" @confirm="publishTest">
        <div class="flex flex-col gap-4">
            <ElAlert
                title="模拟发布添加完成之后，请勿重复添加，否则会重复发布，期间手机也不要操作，静静等待10-30s完成"
                type="warning"
                :closable="false" />
            <ElTabs v-model="testType">
                <ElTabPane label="在线链接" name="url"> </ElTabPane>
                <ElTabPane label="上传文件" name="upload"> </ElTabPane>
            </ElTabs>
            <ElForm ref="formRef" :model="formData" :rules="rules" label-position="top">
                <ElFormItem label="视频链接" prop="url" v-if="testType === 'url'">
                    <ElInput v-model="formData.url" class="w-full" placeholder="请输入视频链接地址" />
                </ElFormItem>
                <ElFormItem label="视频文件" prop="url" v-if="testType === 'upload'">
                    <div class="">
                        <upload
                            type="video"
                            v-model="formData.url"
                            show-progress
                            :show-file-list="false"
                            @change="getVideo">
                            <div
                                class="w-20 h-20 flex items-center justify-center gap-2 flex-col border border-dashed border-[#d9d9d9] bg-[#F5F5F5] rounded-lg">
                                <Icon
                                    name="el-icon-CirclePlusFilled"
                                    color="var(--color-primary)"
                                    :size="18"
                                    v-if="!formData.url"></Icon>
                                <div v-else class="relative w-full h-full">
                                    <video :src="formData.url" class="w-full h-full rounded-lg"></video>
                                    <div
                                        class="absolute -top-3 -right-3 z-[1000] w-6 h-6 bg-white rounded-full"
                                        @click.stop="formData.url = ''">
                                        <Icon
                                            name="el-icon-CircleCloseFilled"
                                            color="var(--el-color-error)"
                                            :size="18"></Icon>
                                    </div>
                                </div>
                            </div>
                        </upload>
                        <ElButton type="primary" class="mt-2" @click="openVideoMaterial">从素材库选择</ElButton>
                    </div>
                </ElFormItem>
                <ElFormItem label="正文内容">
                    <ElInput v-model="formData.title" placeholder="请输入标题" maxlength="20" />
                    <ElInput
                        v-model="formData.subtitle"
                        type="textarea"
                        placeholder="请输入正文描述"
                        class="mt-2"
                        :rows="5" />
                </ElFormItem>
                <ElFormItem label="话题" prop="topic">
                    <div class="flex items-center gap-2">
                        <DesignerTag v-model="formData.topic" />
                    </div>
                </ElFormItem>
                <ElFormItem label="添加地点" prop="poi">
                    <ElInput v-model="formData.poi" placeholder="请输入添加地点" maxlength="20" />
                </ElFormItem>
                <ElFormItem label="发布账号" prop="accounts">
                    <ElSelect v-model="formData.accounts" placeholder="请选择发布账号" filterable multiple>
                        <ElOption
                            v-for="item in optionsData.accountLists"
                            :key="item.id"
                            :label="item.nickname"
                            :value="item.account" />
                    </ElSelect>
                </ElFormItem>
            </ElForm>
        </div>
    </popup>
    <video-material
        v-if="showVideoMaterial"
        type="video-result"
        ref="videoMaterialRef"
        :video-list="formData.url ? [formData.url] : []"
        @confirm="getMaterialVideo"
        @close="showVideoMaterial = false" />
</template>

<script setup lang="ts">
import { mockPublishTask } from "@/api/redbook";
import { getAccountList } from "@/api/service";
import { AppTypeEnum } from "@/enums/appEnums";
import DesignerTag from "@/components/form-designer/setters/tags.vue";
import VideoMaterial from "../../../_components/video-material.vue";

const emit = defineEmits(["close", "success"]);

const testType = ref("url");
const formRef = ref();

const formData = reactive({
    url: "",
    accounts: "",
    title: "",
    subtitle: "",
    topic: [],
    poi: "",
    material_type: 1,
});

const rules = {
    url: [
        { required: true, message: "请输入视频链接地址" },
        {
            validator: (rule, value, callback) => {
                if (!/^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/i.test(value)) {
                    callback(new Error("请输入正确的视频链接地址"));
                }
                callback();
            },
        },
    ],
    accounts: [{ required: true, message: "请选择发布账号" }],
};

const { optionsData } = useDictOptions<{
    accountLists: any[];
}>({
    accountLists: {
        api: getAccountList,
        params: {
            page_size: 1000,
            app_type: AppTypeEnum.REDBOOK,
        },
        transformData: (data) => data.lists,
    },
});

const popupRef = ref();

const videoMaterialRef = ref();
const showVideoMaterial = ref(false);

const openVideoMaterial = async () => {
    showVideoMaterial.value = true;
    await nextTick();
    videoMaterialRef.value.open();
};

const getVideo = (result: any) => {
    const { data } = result.response;
    formData.url = data.uri;
};

const getMaterialVideo = (result: any) => {
    const { video_result_url } = result[0];
    formData.url = video_result_url;
    showVideoMaterial.value = false;
};

const publishTest = async () => {
    await formRef.value.validate();
    try {
        await mockPublishTask({
            ...formData,
            topic: formData.topic.join(","),
        });
        feedback.msgSuccess("添加成功");
        close();
        emit("success");
    } catch (error) {
        feedback.msgError(error || "添加失败");
    }
};

const open = () => {
    popupRef.value.open();
};

const close = () => {
    emit("close");
};

defineExpose({
    open,
    setFormData: (data: any) => {
        setFormData(data, formData);
    },
});
</script>

<style scoped></style>
