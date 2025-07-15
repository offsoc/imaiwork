<template>
    <div class="w-full h-full flex flex-col bg-black rounded-xl overflow-hidden border border-[rgba(255,255,255,0.2)]">
        <div
            class="grow min-h-0 relative"
            :style="{
                background: `linear-gradient(180deg, rgba(0,0,0,0.00) 50%, #000 100%), url(${item.pic}) no-repeat `,
                backgroundSize: 'cover',
            }">
            <div class="w-full px-3 mt-1">
                <div class="line-clamp-1 text-white">
                    {{ item.name }}
                </div>
            </div>
            <div
                class="absolute right-2 top-2 z-[1000] w-9 h-9 flex items-center justify-center bg-[rgba(255,255,255,0.2)] rounded-full invisible group-hover:visible"
                :class="[activeVideo == item.id ? '!visible' : '']"
                style="backdrop-filter: blur(5px)">
                <ElPopover
                    popper-class="!w-[212px] !min-w-[212px] !p-2 !rounded-xl !border-[#333333] !bg-digital-human"
                    :show-arrow="false"
                    :popper-options="{
                        modifiers: [{ name: 'offset', options: { offset: [100, 20] } }],
                    }"
                    @show="visibleChange(true, item.id)"
                    @hide="visibleChange(false, item.id)">
                    <template #reference>
                        <div class="rotate-90 origin-center mr-1">
                            <Icon name="el-icon-MoreFilled" color="#ffffff"></Icon>
                        </div>
                    </template>
                    <div class="flex flex-col gap-2 text-white">
                        <DefineTemplate v-slot="{ label, icon }">
                            <div
                                class="h-11 px-3 rounded-lg cursor-pointer flex items-center gap-3 hover:shadow-[0_0_0_1px_rgba(42,42,42,1)] hover:bg-digital-human-bg">
                                <span
                                    class="flex w-5 h-5 rounded bg-[rgba(255,255,255,0.05)] items-center justify-center">
                                    <Icon :name="icon" color="#ffffff"></Icon>
                                </span>
                                <span class="text-[rgba(255,255,255,0.8)]">{{ label }}</span>
                            </div>
                        </DefineTemplate>
                        <div v-if="item.status == 1" @click="handleDownLoad(item.video_url)">
                            <SelectItemTemplate label="下载视频" icon="el-icon-Download" />
                        </div>
                        <div v-if="[2, 5].includes(item.status)" @click="handleRetry(item.id)">
                            <SelectItemTemplate label="重试视频" icon="el-icon-Refresh" />
                        </div>
                        <div @click="handleDelete(item.id)">
                            <SelectItemTemplate label="删除视频" icon="el-icon-Delete" />
                        </div>
                    </div>
                </ElPopover>
            </div>
            <template v-if="item.status == 1">
                <div
                    class="absolute top-[50%] left-[50%] translate-x-[-50%] translate-y-[-70%] z-[99]"
                    @click="handlePlay(item.video_url)">
                    <div
                        class="w-12 h-12 rounded-full flex items-center justify-center bg-[#ffffff33]"
                        style="backdrop-filter: blur(5px)">
                        <Icon name="local-icon-play2" :size="40"></Icon>
                    </div>
                </div>
            </template>
            <template v-else>
                <div
                    class="absolute z-[88] px-4 bg-[#0000005E] w-full h-full top-0 flex flex-col items-center justify-center">
                    <div class="flex justify-center items-center flex-col gap-2">
                        <DefineTemplate></DefineTemplate>
                        <template v-if="item.status == 2">
                            <span class="w-6 h-6 flex items-center justify-center rounded-full bg-error">
                                <Icon name="local-icon-video2" color="#fff"></Icon>
                            </span>
                            <span class="text-white text-center">{{ item.remark || "生成失败" }}</span>
                            <span class="text-[rgba(255,255,255,0.5)]"> （请检查训练的视频文件） </span>
                        </template>
                        <template v-else>
                            <span class="w-6 h-6 flex items-center justify-center rounded-full bg-primary">
                                <Icon name="local-icon-pic2" color="#fff"></Icon>
                            </span>
                            <span class="text-white">正在生成中</span>
                            <span class="text-[rgba(255,255,255,0.5)]">几分钟即可生成视频</span>
                        </template>
                    </div>
                </div>
            </template>
            <div class="absolute bottom-0 left-0 w-full z-[51]">
                <div class="flex justify-center" v-if="modelVersionMap[item.model_version]">
                    <div class="digital-human-tag !py-1.5 !px-5" :class="`digital-human-tag-${item.model_version}`">
                        {{ modelVersionMap[item.model_version] }}
                    </div>
                </div>
                <div class="py-1 w-full flex justify-center z-50">
                    <div class="text-base text-[rgba(255,255,255,0.5)]">
                        {{ item.create_time }}
                    </div>
                </div>
            </div>
        </div>
        <preview-video v-if="showVideo" ref="videoPlayerRef" @close="showVideo = false"></preview-video>
    </div>
</template>

<script setup lang="ts">
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

let render;
const DefineTemplate = {
    setup(_, { slots }) {
        return () => {
            render = slots.default;
        };
    },
};

const SelectItemTemplate = (props) => {
    return render && render(props);
};
</script>
