<template>
	<div class="px-3">
		<div class="h-14 flex justify-between items-center">
			<div
				class="flex justify-between items-center"
				:class="[
					hideSidebar
						? 'fixed z-30 top-0 left-0 px-3 h-14'
						: 'w-full',
				]"></div>
		</div>
	</div>
</template>

<script setup lang="ts">
import { useAppStore } from "@/stores/app";

const router = useRouter();

const { sessionActive } = useSessionList();

const { toggleSidebar } = useAppStore();
const hideSidebar = computed(() => useAppStore().hideSidebar);
</script>

<style scoped></style>
