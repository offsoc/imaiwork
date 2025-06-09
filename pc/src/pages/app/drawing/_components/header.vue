<template>
	<div
		class="relative flex h-14 w-full items-center justify-between gap-2 border-b border-token-border-medium px-3 flex-shrink-0">
		<div class="flex items-center gap-2">
			<div
				class="cursor-pointer flex items-center p-2 hover:bg-token-sidebar-surface-secondary rounded-lg"
				@click="$router.back()">
				<Icon name="el-icon-ArrowLeftBold" :size="18"></Icon>
				<div class="">返回</div>
			</div>
		</div>
	</div>
</template>

<script setup lang="ts"></script>

<style scoped></style>
