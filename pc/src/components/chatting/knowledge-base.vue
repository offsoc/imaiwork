<template>
	<ElDrawer
		v-model="visible"
		size="424px"
		class="knowledge-base-drawer"
		:close-on-click-modal="false"
		@close="close()">
		<template #header>
			<div class="text-lg font-bold text-black">关联知识库</div>
		</template>
		<div class="flex flex-col gap-2 h-full">
			<div class="px-4">
				<ElInput
					v-model="search"
					placeholder="请输入搜索内容"
					clearable
					@clear="handleSearch">
					<template #append>
						<ElButton link @click="handleSearch">
							<Icon name="el-icon-Search" />
						</ElButton>
					</template>
				</ElInput>
			</div>
			<div class="grow min-h-0 mt-4">
				<ElScrollbar class="h-full">
					<div class="flex flex-col gap-4 mx-4 pb-10">
						<div
							class="flex items-center gap-4 justify-between bg-primary-light-8 p-4 rounded-lg"
							v-for="(item, index) in lists"
							:key="index"
							@click="handleSelect(item)">
							<div class="flex items-center gap-4">
								<img
									src="@/assets/images/kn_logo.png"
									class="w-8 h-8" />
								<div>
									<div class="font-bold">测试</div>
									<div class="text-[10px] text-[#AAA6B9]">
										知识数：45
									</div>
								</div>
							</div>
							<div>
								<Checkbox :is-checked="item.isChecked" />
							</div>
						</div>
					</div>
				</ElScrollbar>
			</div>
			<div class="flex justify-end gap-2 p-4">
				<ElButton @click="close">取消</ElButton>
				<ElButton type="primary">确定</ElButton>
			</div>
		</div>
	</ElDrawer>
</template>

<script setup lang="ts">
const visible = ref(false);
const search = ref("");

const lists = ref<any[]>([
	{
		id: 1,
		name: "测试",
		knowledgeCount: 45,
		isChecked: false,
	},
	{
		id: 2,
		name: "测试2",
		knowledgeCount: 45,
		isChecked: false,
	},
]);
const handleSearch = () => {};

const handleSelect = (item: any) => {
	item.isChecked = !item.isChecked;
};

const open = () => {
	visible.value = true;
};

const close = () => {
	visible.value = false;
};

defineExpose({
	open,
});
</script>

<style lang="scss">
.knowledge-base-drawer {
	.el-drawer__body {
		padding: 0;
	}
}
</style>
