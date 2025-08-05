<template>
    <div class="h-full flex flex-col p-4">
        <div class="bg-white rounded-lg h-[107px] flex justify-between items-center px-[30px] flex-shrink-0">
            <div>
                <div class="text-2xl">机器人列表</div>
                <div class="text-[#74798C] mt-1">创建你的机器人，接替对应的客服岗位</div>
            </div>
        </div>
        <div
            class="grow min-h-0 flex flex-col overflow-y-auto -mx-4"
            :infinite-scroll-immediate="false"
            :infinite-scroll-disabled="!pager.isLoad"
            :infinite-scroll-distance="10"
            v-infinite-scroll="load">
            <div class="grid grid-cols-3 gap-6 mt-4 px-4">
                <div class="card-item items-center justify-center" @click="handleAccountSetting()">
                    <span class="w-10 h-10 rounded-full bg-[#F2F4F7] flex items-center justify-center">
                        <Icon name="el-icon-Plus" :size="18"></Icon>
                    </span>
                    <span class="mt-4"> 添加机器人 </span>
                </div>
                <div
                    v-for="(item, index) in pager.lists"
                    :key="index"
                    class="card-item group"
                    @click="handleAccountSetting(item.id)">
                    <div class="flex items-center gap-4">
                        <ElImage :src="item.logo" lazy fit="cover" class="w-10 h-10 rounded-[5px]" />
                        <span class="text-lg">{{ item.name }}</span>
                    </div>
                    <div class="grow">
                        <div class="line-clamp-3 text-xs text-[#524B6B] mt-3 leading-5">
                            {{ item.description }}
                        </div>
                    </div>
                    <div class="text-[10px] flex items-center justify-between text-[#AAA6B9] mt-3">
                        <div>
                            {{ dayjs(item.create_time).format("YYYY/MM/DD") }}
                            创建
                        </div>
                        <!-- <div>绑定数：{{ item.wechat_count || 0 }}</div> -->
                    </div>
                    <div
                        class="absolute right-2 top-2 z-[1000] invisible group-hover:visible"
                        :class="[activeRobot == item.id ? '!visible' : '']">
                        <ElPopover
                            :show-arrow="false"
                            popper-class="!w-[130px] !min-w-[130px] !p-[6px] !rounded-xl"
                            @show="visibleChange(true, item.id)"
                            @hide="visibleChange(false, item.id)">
                            <template #reference>
                                <div class="rotate-90 origin-center p-1">
                                    <Icon name="el-icon-MoreFilled"></Icon>
                                </div>
                            </template>
                            <div class="flex flex-col gap-2">
                                <div
                                    class="px-2 py-1 hover:bg-primary-light-9 rounded-lg cursor-pointer"
                                    @click="handleAccountSetting(item.id)">
                                    <ElButton link icon="el-icon-Setting" class="w-full !justify-start"
                                        >编辑智能体</ElButton
                                    >
                                </div>
                                <div
                                    class="px-2 py-1 hover:bg-primary-light-9 rounded-lg cursor-pointer"
                                    @click="handleDelete(item.id, index)">
                                    <ElButton link icon="el-icon-Delete" class="w-full !justify-start">删除</ElButton>
                                </div>
                            </div>
                        </ElPopover>
                    </div>
                </div>
            </div>
            <div v-if="!pager.isLoad" class="text-center mt-4 py-4 text-gray-500">暂无更多了</div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { getAgentList, deleteAgent, addDefaultAgent } from "@/api/agent";
import dayjs from "dayjs";

const router = useRouter();
const nuxtApp = useNuxtApp();
const queryParams = reactive({
    page_no: 1,
});

const { pager, getLists, resetPage } = usePaging({
    fetchFun: getAgentList,
    params: queryParams,
    isScroll: true,
});

const activeRobot = ref<number | undefined>();
const visibleChange = (flag: boolean, id: number) => {
    if (!flag) {
        activeRobot.value = undefined;
    } else {
        activeRobot.value = id;
    }
};

const handleDelete = async (id: number, index: number) => {
    nuxtApp.$confirm({
        message: "确定删除吗？",
        onConfirm: async () => {
            try {
                await deleteAgent({ id });
                pager.lists.splice(index, 1);
                feedback.msgSuccess("删除成功");
            } catch (error) {
                feedback.msgError(error || "删除失败");
            }
        },
    });
};

const handleAccountSetting = async (id?: number) => {
    let query = {
        type: "add",
        id: "",
    };
    if (!id) {
        try {
            const data = await addDefaultAgent();
            query.type = "edit";
            query.id = data.id;
        } catch (error) {
            query.type = "add";
            query.id = "";
        }
    } else {
        query.type = "edit";
        query.id = String(id);
    }
    router.replace({
        query,
    });
};

const load = async () => {
    queryParams.page_no += 1;
    await getLists();
};

getLists();
</script>

<style scoped lang="scss">
.card-item {
    @apply h-[168px] rounded-[6px] bg-white px-6 py-4  relative cursor-pointer flex flex-col hover:scale-[1.02] transition-all duration-300;
}
</style>
