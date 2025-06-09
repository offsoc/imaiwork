<template>
    <div>
        <ElCollapseItem :name="3">
            <template #title>
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <Icon name="local-icon-function_fill" :size="18" color="var(--color-redbook)"></Icon>
                        <div class="text-lg font-bold ml-8">音色选择</div>
                        <ElTag :color="voiceList.length > 0 ? '#67C239' : '#E6A23D'" class="ml-8 !text-white">
                            {{ voiceList.length > 0 ? "配置完成" : "配置未完成" }}
                        </ElTag>
                        <ElTag type="info" class="ml-8">音色数量（{{ voiceList.length }}/{{ count }}）</ElTag>
                    </div>
                </div>
            </template>
            <div class="mt-2">
                <div class="flex items-center gap-4">
                    <ElButton
                        color="#F45D5D"
                        class="!text-white"
                        :disabled="voiceList.length >= count"
                        @click="openVoiceMaterial">
                        <Icon name="local-icon-folder_image_fill" :size="16"></Icon>
                        <div class="ml-2 font-bold">从素材库中选择</div>
                    </ElButton>
                </div>
                <div class="mt-4">
                    <div class="flex flex-wrap gap-4" v-if="voiceList.length > 0">
                        <div v-for="(item, index) in voiceList" :key="item.id" class="relative w-[100px]">
                            <div class="absolute -top-2 -right-2 z-20">
                                <div class="cursor-pointer" @click="deleteVoice(index)">
                                    <Icon
                                        name="el-icon-CircleCloseFilled"
                                        color="var(--color-redbook)"
                                        :size="16"></Icon>
                                </div>
                            </div>
                            <div class="absolute top-1 left-2 z-30">
                                <div class="text-[#11205e] text-[10px]">{{ item.duration }}</div>
                            </div>
                            <div
                                class="w-full h-[100px] bg-[#FAFAFA] rounded-lg relative overflow-hidden group flex items-center justify-center border border-[#E5E5E5]">
                                <img src="../../../../_assets/images/audio.png" class="w-12 h-12" />
                                <div
                                    class="absolute bottom-0 left-0 w-full h-full bg-black/5 flex items-center justify-center z-10 invisible group-hover:visible">
                                    <div class="cursor-pointer" @click="togglePlayVoice(item.url, item.id)">
                                        <Icon
                                            :name="isPlaying ? 'local-icon-music_pause' : 'local-icon-music_play'"
                                            color="#fff"
                                            :size="26"></Icon>
                                    </div>
                                </div>
                            </div>
                            <div class="text-center w-full mt-1 line-clamp-1 break-all">
                                {{ item.name }}
                            </div>
                        </div>
                    </div>
                    <div v-else class="">
                        <ElEmpty description="暂无音色内容" :image-size="100"></ElEmpty>
                    </div>
                </div>
            </div>
        </ElCollapseItem>
    </div>
    <VoiceMaterial
        v-if="showVoiceMaterial"
        ref="voiceMaterialRef"
        :voice-list="voiceList"
        @close="showVoiceMaterial = false"
        @confirm="confirmVoiceMaterial"></VoiceMaterial>
</template>

<script setup lang="ts">
import VoiceMaterial from "../../../../_components/voice-material.vue";
const props = defineProps<{
    collapseName: number;
    voiceList: { id: number; name: string; url: string; duration: string }[];
    count: number;
}>();

const emit = defineEmits<{
    (event: "update:voiceType", value: number): void;
    (event: "update:voiceList", value: { id: number; name: string; url: string; duration: string }[]): void;
}>();

const voiceList = computed({
    get() {
        return props.voiceList;
    },
    set(val) {
        emit("update:voiceList", val);
    },
});

const showVoiceMaterial = ref(false);
const voiceMaterialRef = ref<InstanceType<typeof VoiceMaterial>>();
const openVoiceMaterial = async () => {
    showVoiceMaterial.value = true;
    await nextTick();
    voiceMaterialRef.value?.open();
};

const confirmVoiceMaterial = (res: any) => {
    voiceList.value = res;
};

const deleteVoice = async (index: number) => {
    await feedback.confirm("确定删除该音色吗？");
    voiceList.value.splice(index, 1);
};

const { setUrl, play, pause, pauseAll, isPlaying } = useAudio();
const currAudioId = ref<number>();
const togglePlayVoice = (url: string, id: number) => {
    if (isPlaying.value && currAudioId.value !== id) {
        pauseAll();
    }
    if (!isPlaying.value) {
        if (currAudioId.value !== id) {
            setUrl(url);
        }
        play();
        currAudioId.value = id;
    } else {
        pause();
    }
};
</script>

<style scoped lang="scss">
:deep(.el-radio__input.is-checked .el-radio__inner) {
    background: var(--color-redbook);
    border-color: var(--color-redbook);
}
:deep(.el-radio__input.is-checked + .el-radio__label) {
    color: #000000;
}
</style>
