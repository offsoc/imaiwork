<template>
    <popup
        ref="popupRef"
        width="800px"
        class="choose-anchor-popup"
        :style="{ padding: '0' }"
        :append-to-body="false"
        @close="close"
        @confirm="handleConfirm">
        <div class="rounded-xl overflow-hidden flex flex-col">
            <div
                class="h-[62px] flex items-center justify-between px-4"
                style="background: linear-gradient(156.65deg, #c1fedd 0%, #aeecff 53.87%, #c7c2ff 100%)">
                <div class="text-2xl font-bold">从已有音色中选择</div>
                <div class="cursor-pointer p-1 hover:bg-primary-light-7 rounded-full leading-[0]" @click="close()">
                    <Icon name="el-icon-Close" :size="22"></Icon>
                </div>
            </div>
            <div class="px-4 my-4">
                <div class="flex items-center justify-end">
                    <ElInput
                        v-model="queryParams.name"
                        class="!w-[200px]"
                        suffix-icon="el-icon-Search"
                        clearable
                        placeholder="请输入音色名称"
                        @clear="resetPage"
                        @keyup.enter="resetPage"></ElInput>
                </div>
            </div>
            <div class="h-[500px] flex flex-col">
                <div class="grow min-h-0 cursor-pointer">
                    <ElTable :data="pager.lists" height="100%" @row-click="choose">
                        <ElTableColumn label="音色名称" prop="name"> </ElTableColumn>
                        <ElTableColumn label="音色类型">
                            <template #default="{ row }">
                                <ElTag
                                    v-if="row.voice_id == -1"
                                    type="primary"
                                    effect="dark"
                                    :disable-transitions="true"
                                    >当前视频</ElTag
                                >
                                <ElTag v-else-if="row.type == 0" type="primary" :disable-transitions="true"
                                    >内置音色(免费)</ElTag
                                >
                                <ElTag v-else type="success" :disable-transitions="true">用户音色(免费)</ElTag>
                            </template>
                        </ElTableColumn>
                        <ElTableColumn label="操作">
                            <template #default="{ row }">
                                <div
                                    class="w-5 h-5 flex items-center justify-center rounded-full border border-token-primary mx-auto"
                                    :class="{ 'bg-primary': isChoose(row.voice_id) }">
                                    <Icon
                                        name="el-icon-Select"
                                        v-if="isChoose(row.voice_id)"
                                        color="#ffffff"
                                        :size="10"></Icon>
                                </div>
                            </template>
                        </ElTableColumn>
                    </ElTable>
                </div>
                <div class="flex justify-end my-2 px-2">
                    <pagination v-model="pager" @change="changePage"></pagination>
                </div>
            </div>
            <div class="flex justify-center py-2 shadow">
                <ElButton type="primary" class="!text-white !w-[166px] !h-[40px]" @click="handleConfirm"
                    >确定选择</ElButton
                >
                <ElButton class="!w-[166px] !h-[40px]" @click="close">取消</ElButton>
            </div>
        </div>
    </popup>
</template>

<script setup lang="ts">
import { getVoiceList } from "@/api/digital_human";
import Popup from "@/components/popup/index.vue";
import { useAppStore } from "@/stores/app";

const emit = defineEmits(["close", "confirm"]);

const popupRef = ref<InstanceType<typeof Popup>>();

const appStore = useAppStore();

const systemToneLists = computed(() => {
    return [
        { voice_id: -1, name: "视频原音", type: 1 },
        ...(appStore.getDigitalHumanConfig?.voice || [])
            .filter((item: any) => item.status == "1")
            .map((item) => ({ ...item, voice_id: item.code, type: 0 })),
    ];
});

const queryParams = reactive({
    name: "",
    status: 1,
    model_version: "",
});

const { pager, getLists, resetPage } = usePaging({
    fetchFun: getVoiceList,
    params: queryParams,
});

const currTone = ref<any>({});

const isChoose = (voice_id: number) => {
    return currTone.value.voice_id === voice_id;
};

const choose = (item: any) => {
    const { voice_id, type, name } = item;
    if (isChoose(voice_id)) {
        currTone.value = {};
    } else {
        currTone.value = {
            voice_id,
            name,
            type,
        };
    }
};

const setChooseTone = (item: any) => {
    currTone.value = item;
};

const changePage = async () => {
    await getLists();
    if (pager.page == 1) {
        pager.lists.unshift(...systemToneLists.value);
    }
};

const handleConfirm = () => {
    emit("confirm", currTone.value);
    close();
};

const open = async (model_version: string) => {
    popupRef.value?.open();
    queryParams.model_version = model_version;
    await getLists();
    pager.lists.unshift(...systemToneLists.value);
};

const close = () => {
    emit("close");
};

defineExpose({
    open,
    setChooseTone,
});
</script>

<style scoped lang="scss">
.choose-anchor-popup {
    :deep() {
        .el-dialog__header,
        .el-dialog__footer {
            display: none;
            padding: 0;
        }
    }
}
</style>
