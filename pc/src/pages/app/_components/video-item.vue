<template>
    <div class="w-full h-full flex flex-col">
        <view class="grow min-h-0 relative overflow-hidden rounded-lg">
            <div class="w-full h-full flex items-center justify-center">
                <div class="w-[70%] 3xl:w-[50%] h-[85%] flex items-center relative z-10">
                    <img :src="item.pic" class="w-full h-full rounded-lg object-cover" />
                    <div class="absolute top-2 left-2 z-[51]" v-if="modelVersionMap[item.model_version]">
                        <div class="version-tag text-xs">
                            {{ modelVersionMap[item.model_version] }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="h-full w-full absolute top-0 left-0">
                <img :src="item.pic" class="w-full h-full object-cover blur-sm" />
            </div>
            <div
                class="absolute right-2 top-2 z-[1000] invisible group-hover:visible"
                :class="[activeVideo == item.id ? '!visible' : '']">
                <ElPopover
                    :show-arrow="false"
                    popper-class="!w-[120px] !min-w-[120px] !p-[6px] !rounded-xl"
                    @show="visibleChange(true, item.id)"
                    @hide="visibleChange(false, item.id)">
                    <template #reference>
                        <div class="rotate-90 origin-center p-1">
                            <Icon name="el-icon-MoreFilled" color="#ffffff"></Icon>
                        </div>
                    </template>
                    <div class="flex flex-col gap-2">
                        <div
                            v-if="item.status == 1"
                            class="px-2 py-1 hover:bg-primary-light-8 rounded-lg cursor-pointer"
                            @click="handleDownLoad(item.video_url)">
                            <ElButton link icon="el-icon-Download" class="w-full !justify-start">下载视频</ElButton>
                        </div>
                        <div
                            v-if="[2, 5].includes(item.status)"
                            class="px-2 py-1 hover:bg-primary-light-8 rounded-lg cursor-pointer"
                            @click="handleRetry(item.id)">
                            <ElButton link icon="el-icon-Refresh" class="w-full !justify-start">重试</ElButton>
                        </div>
                        <div
                            class="px-2 py-1 hover:bg-primary-light-8 rounded-lg cursor-pointer"
                            @click="handleDelete(item.id)">
                            <ElButton link icon="el-icon-Delete" class="w-full !justify-start">删除</ElButton>
                        </div>
                    </div>
                </ElPopover>
            </div>
            <template v-if="item.status == 1">
                <div
                    class="text-center absolute top-[50%] left-[50%] translate-x-[-50%] translate-y-[-70%] z-[99]"
                    @click="handlePlay(item.video_url)">
                    <Icon name="local-icon-play" :size="48" color="#fff"></Icon>
                </div>
            </template>

            <template v-else>
                <div
                    class="absolute z-[88] px-4 bg-[#0000005E] w-full h-full top-0 flex flex-col items-center justify-center">
                    <div v-if="item.status == 2" class="flex justify-center items-center flex-col gap-2">
                        <img src="@/assets/images/image_error.png" class="w-12 h-12" />
                        <span class="text-white text-center">{{ item.remark || "生成失败" }}</span>
                    </div>
                    <div class="flex flex-col items-center justify-center w-full h-full gap-2" v-else>
                        <span class="rotation"></span>
                        <span class="text-white">正在生成中</span>
                        <span class="text-white">大约等待10分钟左右</span>
                    </div>
                </div>
            </template>

            <div
                class="absolute bottom-0 bg-[#0000005E] py-1 w-full flex justify-center z-50 invisible group-hover:visible">
                <div class="text-white text-xs">
                    {{ item.create_time }}
                </div>
            </div>
        </view>
        <div class="p-2">
            <div class="line-clamp-1 text-center">
                {{ item.name }}
            </div>
        </div>
        <preview-video v-if="showVideo" ref="videoPlayerRef" @close="showVideo = false"></preview-video>
    </div>
</template>

<script setup lang="ts">
import { ElDivider } from "element-plus";
import { useAppStore } from "@/stores/app";

const props = withDefaults(
    defineProps<{
        item: Record<string, any>;
        showVersion?: boolean;
        isCreate?: boolean;
    }>(),
    {
        item: () => ({
            id: 0,
            name: "",
            pic: "",
            status: 0,
            video_url: "",
            model_version: "",
            remark: "",
            create_time: "",
        }),
        showVersion: true,
        isCreate: true,
    }
);

const emit = defineEmits(["delete", "retry"]);
const appStore = useAppStore();
const modelChannel = computed(() => appStore.getDigitalHumanConfig?.channel);

const modelVersionMap = computed(() => {
    return modelChannel.value.reduce((acc: Record<string, string>, item: any) => {
        acc[item.id] = item.name;
        return acc;
    }, {});
});

const activeVideo = ref<number | undefined>();

const visibleChange = (flag: boolean, id: number) => {
    if (!flag) {
        activeVideo.value = undefined;
    } else {
        activeVideo.value = id;
    }
};

const handleRetry = (id: number) => {
    emit("retry", id);
};

const handleDownLoad = (url: string) => {
    feedback.loading("保存中");
    downloadFile(url)
        .then(() => {
            feedback.closeLoading();
            feedback.msgSuccess("下载成功");
        })
        .catch(() => {
            feedback.closeLoading();
            feedback.msgError("下载失败");
        });
};

const handleDelete = async (id: number) => {
    emit("delete", id);
};

const videoPlayerRef = shallowRef();
const showVideo = ref(false);
const handlePlay = async (url: string) => {
    showVideo.value = true;
    await nextTick();
    videoPlayerRef.value.open();
    videoPlayerRef.value.setUrl(url);
};
</script>

<style scoped lang="scss"></style>
