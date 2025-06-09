<template>
	<div class="flex items-center gap-2">
		<div
			class="cursor-pointer flex items-center p-2 hover:bg-token-sidebar-surface-secondary rounded-lg"
			@click="router.back()">
			<Icon name="el-icon-ArrowLeftBold" :size="16"></Icon>
			<div class="">返回</div>
		</div>
		<div v-if="title" class="font-bold">{{ title }}</div>
	</div>
</template>

<script setup lang="ts">
const router = useRouter();
const route = useRoute();
const title = ref<any>(route.meta.title);

watch(
	() => route.meta.title,
	(newVal) => {
		if (newVal) {
			title.value = newVal;
		}
	},
	{
		deep: true,
	}
);
</script>

<style scoped></style>
