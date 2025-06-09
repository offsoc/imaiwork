<template>
    <div>
        <el-card shadow="never" class="!border-none mt-4">
            <div v-perms="['recharge.package/add', 'recharge.package/add:edit']">
                <router-link :to="getRoutePath('recharge.package/add:edit')">
                    <el-button type="primary">
                        <template #icon>
                            <icon name="el-icon-Plus" />
                        </template>
                        新增充值套餐
                    </el-button>
                </router-link>
            </div>
            <el-table size="large" :data="pager.lists" v-loading="pager.loading" class="mt-4">
                <el-table-column label="套餐价格" min-width="120">
                    <template #default="{ row }"> ¥{{ row.price }}</template>
                </el-table-column>
                <el-table-column label="套餐名称" prop="name" min-width="120"></el-table-column>
                <el-table-column label="算力值" prop="package_info.tokens" min-width="120"> </el-table-column>
                <el-table-column label="排序" prop="sort"> </el-table-column>
                <el-table-column label="操作" width="120" fixed="right">
                    <template #default="{ row }">
                        <el-button type="primary" link>
                            <router-link
                                v-perms="['recharge.package/edit', 'recharge.package/add:edit']"
                                :to="{
                                    path: getRoutePath('recharge.package/add:edit'),
                                    query: {
                                        id: row.id,
                                        mode: 'edit',
                                    },
                                }">
                                编辑
                            </router-link>
                        </el-button>
                        <el-button v-perms="['recharge.package/del']" type="danger" link @click="handleDelete(row.id)">
                            删除
                        </el-button>
                    </template>
                </el-table-column>
            </el-table>
        </el-card>
    </div>
</template>
<script setup lang="ts">
import { getRechargeLists, rechargeDelete } from "@/api/marketing/recharge";

import { usePaging } from "@/hooks/usePaging";
import { getRoutePath } from "@/router";
import feedback from "@/utils/feedback";
const { pager, getLists } = usePaging({
    fetchFun: getRechargeLists,
});

//删除
const handleDelete = async (id: number) => {
    await feedback.confirm("确定要删除？");
    await rechargeDelete({ id });
    getLists();
};

getLists();
</script>
