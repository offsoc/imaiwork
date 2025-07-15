<template>
    <popup
        ref="popupRef"
        title=""
        style="padding: 0"
        width="800px"
        :show-close="false"
        cancel-button-text=""
        confirm-button-text="">
        <!-- 头部 -->
        <div class="px-4 h-[71px] bg-redbook flex items-center justify-between -mt-4 rounded-t-2xl">
            <div class="text-white text-xl font-bold">从已有音色中选择</div>
            <div class="text-white text-[16px] font-bold cursor-pointer" @click="close">
                <Icon name="local-icon-close" :size="24"></Icon>
            </div>
        </div>
        <!-- 搜搜 -->
        <div class="px-4 mt-4">
            <div class="flex items-center justify-between">
                <div class="text-[16px] font-bold">全部（共{{ props.voiceList.length }}）</div>
                <div class="flex items-center gap-2">
                    <ElInput
                        v-model="searchValue"
                        placeholder="请输入音色名称"
                        class="w-[200px] h-[32px]"
                        suffix-icon="el-icon-Search"
                        clearable
                        @clear="resetParams"
                        @keyup.enter="getLists"></ElInput>
                    <ElButton :icon="Refresh" @click="resetParams()" />
                </div>
            </div>
            <div class="mt-2">已选择数量：{{ chooseList.length }}</div>
        </div>
        <!-- 内容 -->
        <div class="mt-4 px-4">
            <div class="h-[400px]">
                <ElTable
                    ref="tableRef"
                    v-loading="pager.loading"
                    :data="pager.lists"
                    height="100%"
                    stripe
                    row-key="id"
                    :row-style="{ height: '40px' }"
                    @selection-change="handleSelectionChange">
                    <ElTableColumn label="选择" type="selection" width="55" />
                    <ElTableColumn label="音色ID" prop="name" />
                    <ElTableColumn label="音色名称" prop="name" />
                    <ElTableColumn label="创建时间" prop="create_time" />
                    <ElTableColumn label="音色模型">
                        <template #default="{ row }">
                            {{ DigitalHumanModelVersionEnumMap[row.model_version] }}
                        </template>
                    </ElTableColumn>
                    <template #empty>
                        <ElEmpty description="暂无数据" :image-size="100"></ElEmpty>
                        <router-link to="/app/digital_human?type=3" class="hover:underline" target="_blank">
                            还么有音色？点击这里
                        </router-link>
                    </template>
                </ElTable>
            </div>
            <div class="flex justify-end mt-4">
                <pagination v-model="pager" @change="getLists"></pagination>
            </div>
        </div>
        <!-- 底部 -->
        <div class="flex justify-center mt-4">
            <ElButton color="#F45D5D" class="!text-white !w-[166px] !h-[40px]" @click="handleConfirm"
                >确定选择</ElButton
            >
            <ElButton class="!w-[166px] !h-[40px]" @click="close">取消</ElButton>
        </div>
    </popup>
</template>

<script setup lang="ts">
import { getVoiceList } from "@/api/digital_human";
import { Refresh } from "@element-plus/icons-vue";
import { DigitalHumanModelVersionEnum, DigitalHumanModelVersionEnumMap } from "~/pages/app/digital_human/_enums";
import Popup from "@/components/popup/index.vue";
import { ElTable } from "element-plus";
import { cloneDeep } from "lodash-es";

const props = defineProps<{
    voiceList: any[];
}>();

const emit = defineEmits<{
    (e: "close"): void;
    (e: "confirm", voiceList: any[]): void;
}>();

const popupRef = ref<InstanceType<typeof Popup>>();
const tableRef = ref<InstanceType<typeof ElTable>>();

const searchValue = ref("");

const queryParams = reactive({
    name: "",
    status: 1,
    model_version: DigitalHumanModelVersionEnum.ADVANCED,
});

const { pager, getLists, resetParams, resetPage } = usePaging({
    fetchFun: getVoiceList,
    params: queryParams,
});

const chooseList = ref<any[]>(cloneDeep(props.voiceList));
const handleSelectionChange = (list: any[]) => {
    chooseList.value = list;
};

const handleConfirm = () => {
    if (chooseList.value.length === 0) {
        feedback.msgError("请选择音色");
        return;
    }
    emit(
        "confirm",
        chooseList.value.map((item) => ({
            model_version: item.model_version,
            voice_id: item.voice_id,
            voice_urls: item.voice_urls,
            name: item.name,
        }))
    );
    popupRef.value?.close();
};

const open = async () => {
    popupRef.value?.open();
    await getLists();
    await nextTick();
    props.voiceList.forEach((item) => {
        tableRef.value?.toggleRowSelection(item, true);
    });
};

const close = () => {
    emit("close");
    popupRef.value?.close();
};

defineExpose({
    open,
    close,
});
</script>

<style scoped></style>
