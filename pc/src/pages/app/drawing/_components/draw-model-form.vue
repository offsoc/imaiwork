<template>
    <div>
        <ElForm :model="formData" ref="formRef" label-position="top" :rules="formRules">
            <ElFormItem label="" class="!mb-1">
                <div class="w-full relative">
                    <ElTabs v-model="activeType" @tab-click="handleTabClick">
                        <ElTabPane v-for="(tab, index) in typeMap" :name="tab.id">
                            <template #label>
                                <div class="flex items-center gap-2">
                                    <Icon :name="`local-icon-${tab.icon}`" :size="24"></Icon>
                                    <span>{{ tab.label }}</span>
                                </div>
                            </template>
                        </ElTabPane>
                    </ElTabs>
                    <div class="absolute right-0 top-2">
                        <div
                            class="flex items-center gap-2 text-[#999999] hover:text-primary cursor-pointer"
                            @click="openExampleVideo">
                            <Icon name="local-icon-video"></Icon>
                            <div>查看教程示例</div>
                        </div>
                    </div>
                </div>
            </ElFormItem>
            <ElFormItem prop="image">
                <div class="flex gap-2 w-full">
                    <div class="flex-1 flex flex-col gap-6" v-if="activeType == FormModelTypeEnum.UPPER_CLOTHES">
                        <ElFormItem prop="upper_clothes">
                            <ImageUploader
                                class="w-full"
                                v-model="formData.upper_clothes"
                                label="上衣部分"
                                :placeholderImage="Shangyi"
                                :max-size="maxSize" />
                        </ElFormItem>
                        <ElFormItem prop="lower_clothes">
                            <ImageUploader
                                class="w-full"
                                v-model="formData.lower_clothes"
                                label="下装部分"
                                :placeholderImage="Qunzi"
                                :max-size="maxSize" />
                        </ElFormItem>
                    </div>
                    <div class="flex-1" v-if="activeType == FormModelTypeEnum.LOWER_CLOTHES">
                        <ElFormItem prop="upper_clothes" class="h-full">
                            <ImageUploader
                                class="w-full"
                                v-model="formData.upper_clothes"
                                label="上衣部分"
                                :placeholderImage="Lianyiqun"
                                :max-size="maxSize"
                                @update:modelValue="() => (formData.lower_clothes = formData.upper_clothes)" />
                        </ElFormItem>
                    </div>
                </div>
            </ElFormItem>
            <ElFormItem label="模特类型" prop="persons">
                <div class="w-full relative">
                    <div class="absolute top-[-35px] right-0">
                        <ElButton size="small" @click="openModelCase">
                            <div class="flex items-center">
                                <span>更多模特</span>
                                <Icon name="el-icon-ArrowRight"></Icon>
                            </div>
                        </ElButton>
                    </div>
                    <div class="text-xs text-gray-500 -mt-2">提示：上传模特请选择模特类型图片</div>
                    <div class="grow min-h-0 -mx-2">
                        <ElScrollbar>
                            <div class="grid grid-cols-3 gap-2 py-2 px-2">
                                <upload
                                    drag
                                    show-progress
                                    :show-file-list="false"
                                    :accept="'.jpg,.jpeg,.png'"
                                    @success="getUploadRefImage">
                                    <div
                                        class="h-[200px] w-full flex-col rounded-lg flex items-center justify-center bg-primary-light-8">
                                        <img src="@/assets/images/model_avatar.png" class="w-[32px]" />
                                        <span class="mt-2">点击添加模特</span>
                                    </div>
                                </upload>
                                <div
                                    v-for="(item, index) in modelList"
                                    :key="index"
                                    class="h-[200px] flex items-center justify-center rounded-lg group relative"
                                    :class="{
                                        'shadow-[0_0_0_3px_var(--el-color-primary)]': formData.persons.includes(item),
                                    }"
                                    @click="chooseModelImage(item)">
                                    <ElImage :src="item" class="!rounded-lg w-full h-full" fit="cover" lazy />
                                    <div
                                        class="absolute right-0 top-0 w-full h-full invisible group-hover:visible z-[888] flex flex-col bg-[var(--el-overlay-color-lighter)] rounded-lg">
                                        <div class="flex items-center justify-center gap-2 grow">
                                            <div class="cursor-pointer" @click.stop="previewRefImage(index)">
                                                <Icon name="el-icon-ZoomIn" color="#ffffff" :size="18"></Icon>
                                            </div>
                                            <div class="cursor-pointer" @click.stop="delRefImage(index)">
                                                <Icon name="el-icon-Delete" color="#ffffff" :size="18"></Icon>
                                            </div>
                                        </div>
                                        <div class="p-1 text-center text-white cursor-pointer">
                                            {{ formData.persons.includes(item) ? "取消选择" : "选择" }}
                                        </div>
                                    </div>
                                    <div
                                        class="absolute top-2 right-2 p-1 leading-[0] bg-primary rounded-md"
                                        v-if="formData.persons.includes(item)">
                                        <Icon name="el-icon-Select" color="#ffffff" :size="12"></Icon>
                                    </div>
                                </div>
                            </div>
                        </ElScrollbar>
                    </div>
                </div>
            </ElFormItem>
        </ElForm>
        <ElImageViewer
            v-if="showPreview"
            :initial-index="previewIndex"
            :url-list="modelList"
            @close="showPreview = false"></ElImageViewer>
        <ModelCase
            ref="modelCasePopRef"
            v-if="showModelCase"
            @close="showModelCase = false"
            @chooseModelImage="addModelImage"></ModelCase>
        <preview-video ref="videoPlayerRef" v-if="showExampleVideo" @close="showExampleVideo = false"></preview-video>
    </div>
</template>

<script setup lang="ts">
import { addModelCase, getCaseLists } from "@/api/drawing";
import { type FormInstance, type FormRules } from "element-plus";
import { Delete, Search } from "@element-plus/icons-vue";
import { FormModelTypeEnum } from "../_enums/drawEnums";
import ModelCase from "./model-case.vue";
import ImageUploader from "./image-uploader.vue";
import Shangyi from "@/assets/images/shangyi.png";
import Qunzi from "@/assets/images/qunzi.png";
import Lianyiqun from "@/assets/images/lianyiqun.png";
import Example1Video from "../_assets/video/example1.mp4";
import Example2Video from "../_assets/video/example2.mp4";

const emit = defineEmits<{
    (e: "change-img-count", data: any): void;
}>();

const maxSize = 20;

const activeType = ref<number>(FormModelTypeEnum.UPPER_CLOTHES);
const typeMap = [
    {
        id: FormModelTypeEnum.UPPER_CLOTHES,
        icon: "scene",
        label: "上下装",
    },
    {
        id: FormModelTypeEnum.LOWER_CLOTHES,
        icon: "txt",
        label: "连衣裙",
    },
];

const exampleVideo = ref(Example1Video);

const formData = reactive<any>({
    lower_clothes: "",
    upper_clothes: "",
    persons: [],
    img_count: 1,
});
const modelImage = ref("");
const modelList = ref([]);

const resetFormData = () => {
    formData.lower_clothes = "";
    formData.upper_clothes = "";
    formData.persons = [];
    formData.img_count = 1;
};

const getModelCaseList = async () => {
    try {
        const { lists } = await getCaseLists({
            page_size: 10,
            case_type: 4,
            user_type: 1,
        });
        modelList.value = lists.map((item: any) => item.result_image);
    } finally {
    }
};

const modelCasePopRef = shallowRef<InstanceType<typeof ModelCase>>();
const showModelCase = ref(false);
const openModelCase = async () => {
    showModelCase.value = true;
    await nextTick();
    modelCasePopRef.value?.open();
};

// 定义表单验证规则
const formRules: FormRules = {
    lower_clothes: [
        {
            trigger: ["change", "blur"],
            validator: (rule: any, value: any, callback: any) => {
                if (!formData.upper_clothes && !formData.lower_clothes) {
                    callback(new Error("请上传下衣图"));
                }
                callback();
            },
        },
    ],
    upper_clothes: [
        {
            trigger: ["change", "blur"],
            validator: (rule: any, value: any, callback: any) => {
                if (!formData.upper_clothes && !formData.lower_clothes) {
                    callback(new Error("请上传上衣图"));
                }
                callback();
            },
        },
    ],
    persons: [
        {
            required: true,
            message: "请选择模特类型图片",
            trigger: ["change", "blur"],
        },
    ],
};

const formRef = shallowRef<FormInstance>();

const formValidate = async () => {
    return await formRef.value?.validate();
};

const getFormData = () => {
    return { ...formData, activeType: activeType.value };
};

const setFormData = (data: any) => {
    if (data.model) {
        addModelImage(data.model);
    }
    if (data.active_type) {
        activeType.value = data.active_type;
    }
    for (const key in formData) {
        if (data[key] != null && data[key] != undefined) {
            //@ts-ignore
            formData[key] = data[key];
        }
    }
};

const handleTabClick = (tab: any) => {
    exampleVideo.value = tab.paneName == FormModelTypeEnum.UPPER_CLOTHES ? Example1Video : Example2Video;
};

// 打开示例视频
const showExampleVideo = ref(false);
const videoPlayerRef = shallowRef();
const openExampleVideo = async () => {
    showExampleVideo.value = true;
    await nextTick();
    videoPlayerRef.value?.open();
    videoPlayerRef.value?.setUrl(exampleVideo.value);
};

const getUploadRefImage = (res: any) => {
    modelList.value.unshift(res.data.uri);
    addModelCase({
        result_image: res.data.uri,
    });
};

const addModelImage = (image: any) => {
    if (modelList.value.includes(image)) {
        formData.persons = [image];
        if (!formData.persons.includes(image)) {
            formData.persons.push(image);
        } else {
            // feedback.msgError("模特已存在");
        }
        return;
    }
    modelList.value.unshift(image);
    formData.persons = [image];
};

const chooseModelImage = (image: any) => {
    formData.persons = [image];
    // if (formData.persons.includes(image)) {
    // 	formData.persons.splice(formData.persons.indexOf(image), 1);
    // } else {
    // 	// 限制最多4个模特
    // 	if (formData.persons.length >= 4) {
    // 		feedback.msgError("最多只能选择4个模特");
    // 		return;
    // 	}
    // 	formData.persons.push(image);
    // }
    formData.img_count = formData.persons.length;
    emit("change-img-count", formData.img_count);
};

const showPreview = ref(false);
const previewIndex = ref(0);
const previewRefImage = (index: number) => {
    showPreview.value = true;
    previewIndex.value = index;
};

const delRefImage = (index: number) => {
    formData.persons.splice(index, 1);
    modelList.value.splice(index, 1);
};

onMounted(() => {
    getModelCaseList();
});

defineExpose({
    formData,
    setFormData,
    getFormData,
    resetFormData,
    formValidate,
});
</script>

<style scoped lang="scss">
:deep(.el-form-item__label) {
    @apply text-base;
}
:deep(.el-upload-dragger) {
    @apply p-0;
}
:deep(.el-form-item__label) {
    @apply font-bold;
}
.tag {
    background: linear-gradient(90deg, #c1ffdd 0%, #bdfae3 45.05%, #b1eefc 75.33%, #c6c1ff 100%);
    @apply rounded-lg px-2 leading-6;
}
</style>
