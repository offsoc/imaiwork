<template>
    <DefineMaterialMenuTemplate>
        <div class="flex flex-col gap-y-2">
            <div class="material-menu-item" @click="handleImportMaterial">
                <span class="flex items-center justify-center rounded p-1 bg-[#ffffff0d]">
                    <Icon name="local-icon-import" color="#ffffff"></Icon>
                </span>
                <span class="text-[#ffffffcc]"> 素材库导入 </span>
            </div>
            <upload
                class="w-full"
                show-progress
                v-bind="getUploadProps"
                :show-file-list="false"
                @success="getUploadSuccess">
                <div class="material-menu-item">
                    <span class="flex items-center justify-center rounded p-1 bg-[#ffffff0d]">
                        <Icon name="local-icon-upload" color="#ffffff"></Icon>
                    </span>
                    <span class="text-[#ffffffcc]"> 本地上传</span>
                </div>
            </upload>
        </div>
    </DefineMaterialMenuTemplate>
    <div class="grid grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4 gap-2">
        <ElPopover
            v-if="materialList.length < getUploadProps.limit"
            trigger="click"
            width="212"
            popper-class="!rounded-xl !bg-app-bg-2 !border-app-border-2 !p-2 choose-material-popover"
            :show-arrow="false">
            <template #reference>
                <div
                    class="material-item p-[6px] cursor-pointer hover:!border-[#ffffff33]"
                    @click="handleReplaceMaterial(-1)">
                    <div
                        class="w-8 h-8 rounded-xl flex items-center justify-center border border-dashed border-[#ffffff1a] hover:border-[#ffffff33] cursor-pointer mb-4">
                        <Icon name="el-icon-Plus" color="#ffffff"></Icon>
                    </div>
                    <div class="absolute bottom-2 w-full z-[33] px-2">
                        <div class="change-material-btn">添加素材</div>
                    </div>
                </div>
            </template>
            <MaterialTemplate />
        </ElPopover>
        <div v-for="(item, index) in materialList" :key="index" class="material-item">
            <video
                :src="item"
                class="w-full h-full object-cover rounded-md"
                v-if="type == PublishTaskType.VIDEO"
                @click="handlePreviewVideo(item)"></video>
            <ElImage
                v-else-if="type == PublishTaskType.IMAGE"
                :src="item"
                class="w-full h-full rounded-md"
                fit="cover"
                preview-teleported
                :preview-src-list="[item]"></ElImage>
            <div class="absolute top-1 right-1 z-[22] w-4 h-4 rounded-full" @click="handleDeleteMaterial(index)">
                <close-btn :icon-size="10"></close-btn>
            </div>
            <ElPopover
                trigger="click"
                width="212"
                popper-class="!rounded-xl !bg-app-bg-2 !border-app-border-2 !p-2 choose-material-popover"
                :show-arrow="false">
                <template #reference>
                    <div class="absolute bottom-2 w-full z-[33] px-2">
                        <div
                            class="change-material-btn cursor-pointer"
                            style="backdrop-filter: blur(6px)"
                            @click="handleReplaceMaterial(index)">
                            替换
                        </div>
                    </div>
                </template>
                <MaterialTemplate />
            </ElPopover>
        </div>
    </div>
</template>

<script setup lang="ts">
import { PublishTaskType, MaterialActionType } from "../_enums";

const props = withDefaults(
    defineProps<{
        type: PublishTaskType;
        materialList: any[];
        maxVideoCount?: number;
        maxImageCount?: number;
        videoSize?: number;
        videoMinResolution?: number;
        videoMaxResolution?: number;
        videoMinDuration?: number;
        videoMaxDuration?: number;
    }>(),
    {
        type: PublishTaskType.VIDEO,
        materialList: () => [],
        maxVideoCount: 30,
        maxImageCount: 18,
        videoSize: 100,
        videoMinResolution: 480,
        videoMaxResolution: 1080,
        videoMinDuration: 5,
        videoMaxDuration: 600,
    }
);

const emit = defineEmits(["update:materialList", "previewVideo", "importMaterial", "changeMaterial"]);

const {
    type,
    maxVideoCount,
    maxImageCount,
    videoSize,
    videoMinResolution,
    videoMaxResolution,
    videoMinDuration,
    videoMaxDuration,
} = toRefs(props);

const materialList = defineModel<any[]>("materialList");
const replaceMaterialIndex = ref();

const getUploadProps = computed(() => {
    return type.value == PublishTaskType.VIDEO
        ? {
              type: "video",
              limit: maxVideoCount.value,
              maxSize: videoSize.value,
              videoMinWidth: videoMinResolution.value,
              videoMaxWidth: videoMaxResolution.value,
              minDuration: videoMinDuration.value,
              maxDuration: videoMaxDuration.value,
          }
        : { type: "image", limit: maxImageCount.value, maxSize: 10 };
});

const getUploadSuccess = (result: any) => {
    const { uri } = result.data;
    if (type.value == PublishTaskType.VIDEO) {
        if (replaceMaterialIndex.value > -1) {
            materialList.value[replaceMaterialIndex.value] = uri;
        } else {
            materialList.value.push(uri);
        }
    } else {
        if (replaceMaterialIndex.value > -1) {
            materialList.value[replaceMaterialIndex.value] = uri;
        } else {
            materialList.value.push(uri);
        }
    }
    replaceMaterialIndex.value = -1;
    emit("update:materialList", materialList.value);
    emit("changeMaterial", {
        type: MaterialActionType.ADD,
    });
};

const handleImportMaterial = () => {
    emit("importMaterial", {
        index: replaceMaterialIndex.value,
        type: MaterialActionType.REPLACE,
    });
};

const handleReplaceMaterial = (index: number) => {
    replaceMaterialIndex.value = index;
};

const handleDeleteMaterial = (index: number) => {
    useNuxtApp().$confirm({
        message: "确定要删除该素材吗？",
        theme: "dark",
        onConfirm: () => {
            if (type.value == PublishTaskType.VIDEO) {
                materialList.value.splice(index, 1);
            } else if (type.value == PublishTaskType.IMAGE) {
                materialList.value.splice(index, 1);
            }
            emit("update:materialList", materialList.value);
            emit("changeMaterial", {
                type: MaterialActionType.DELETE,
            });
        },
    });
};

const handlePreviewVideo = (url: string) => {
    emit("previewVideo", url);
};

const { DefineTemplate: DefineMaterialMenuTemplate, UseTemplate: MaterialTemplate } = useTemplate();
</script>

<style scoped lang="scss">
.material-item {
    @apply cursor-pointer rounded-md border border-app-border-1 flex flex-col items-center justify-center h-[130px] 2xl:!h-[120px] relative;
}
.change-material-btn {
    @apply text-white text-[11px] mt-8 border border-[rgba(255,255,255,0.1)] shadow-[0_0_0_1px_rgba(0,0,0,0.24)] rounded-md w-full h-[26px] flex items-center justify-center;
}
</style>
<style lang="scss">
.choose-material-popover {
    .material-menu-item {
        @apply h-11 w-full rounded-md p-4 flex items-center gap-x-2 hover:bg-app-bg-1 hover:shadow-[0_0_0_1px_var(--app-border-color-2)] cursor-pointer;
    }
}
</style>
