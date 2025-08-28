<template>
    <div class="h-full flex flex-col p-4">
        <div
            class="rounded-[20px] flex items-center gap-3 px-[30px]"
            style="
                background: linear-gradient(152deg, rgba(0, 101, 251, 0.88) -42.44%, rgba(255, 255, 255, 0) 12.19%)
                    rgb(255, 255, 255);
            ">
            <img src="@/assets/images/agent.svg" class="w-11 mt-7" />
            <div>
                <div class="text-[#000000cc]">{{ ToolEnumMap[ToolEnum.AGENT] }}</div>
                <div class="text-[#00000080]">
                    一键激活模块化智能体，精准执行流程、分析等多类任务，化身全能数字员工。
                </div>
            </div>
        </div>
        <div
            class="grow min-h-0 flex flex-col overflow-y-auto -mx-4"
            :infinite-scroll-immediate="false"
            :infinite-scroll-disabled="!pager.isLoad"
            :infinite-scroll-distance="10"
            v-infinite-scroll="load">
            <div class="grid grid-cols-3 gap-4 mt-4 px-4">
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
                        <ElImage :src="item.image" lazy fit="cover" class="w-10 h-10 rounded-[5px]" />
                        <span class="text-lg">{{ item.name }}</span>
                    </div>
                    <div class="grow">
                        <div class="line-clamp-3 text-xs text-[#524B6B] mt-3 leading-5">
                            {{ item.intro }}
                        </div>
                    </div>
                    <div class="text-[10px] flex items-center justify-between text-[#AAA6B9] mt-3">
                        <div>
                            {{ dayjs(item.create_time).format("YYYY/MM/DD") }}
                            创建
                        </div>
                        <!-- <div>绑定数：{{ item.wechat_count || 0 }}</div> -->
                    </div>
                    <div class="absolute right-2 top-2 z-[1000] invisible group-hover:visible">
                        <handle-menu :data="item" :menu-list="handleMenuList" />
                    </div>
                </div>
            </div>
            <div v-if="!pager.isLoad" class="text-center mt-4 py-4 text-gray-500">暂无更多了</div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { getAgentList, deleteAgent, addAgent } from "@/api/agent";
import { ToolEnumMap, ToolEnum } from "@/enums/appEnums";
import { HandleMenuType } from "@/components/handle-menu/typings";

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

const handleMenuList: HandleMenuType[] = [
    {
        label: "删除智能体",
        icon: "local-icon-delete",
        click: ({ id }: any) => {
            nuxtApp.$confirm({
                message: "确定删除吗？",
                onConfirm: async () => {
                    try {
                        await deleteAgent({ id });
                        const index = pager.lists.findIndex((item) => item.id == id);
                        pager.lists.splice(index, 1);
                        feedback.msgSuccess("删除成功");
                    } catch (error) {
                        feedback.msgError(error || "删除失败");
                    }
                },
            });
        },
    },
];

const handleAccountSetting = async (id?: number) => {
    let query = {
        type: "add",
        id: "",
    };
    if (!id) {
        try {
            const data = await addAgent({});
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
    @apply h-[168px] rounded-[20px] bg-white px-6 py-4  relative cursor-pointer flex flex-col hover:scale-[1.02] transition-all duration-300;
}
</style>
