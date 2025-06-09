<template>
	<el-container
		class="layout-default h-full flex flex-col"
		:style="[{ height: `${windowHeight}px` }]">
		<el-header height="auto" style="padding: 0">
			<LayoutHeader />
		</el-header>
		<el-container class="min-h-0 grow">
			<el-aside
				:width="
					windowWidth < 400
						? '0px'
						: windowWidth < 768
						? '100px'
						: '219px'
				">
				<LayoutAside>
					<template v-if="$slots?.panel" #panel>
						<slot name="panel" />
					</template>
				</LayoutAside>
			</el-aside>
			<el-main class="!p-0">
				<div class="flex flex-col h-full relative">
					<el-main class="flex-1 !p-0">
						<LayoutMain>
							<template v-if="$slots?.mainLeft" #mainLeft>
								<slot name="mainLeft" />
							</template>
							<slot />
							<template v-if="$slots?.mainRight" #mainRight>
								<slot name="mainRight" />
							</template>
						</LayoutMain>
					</el-main>
				</div>
			</el-main>
		</el-container>
	</el-container>
</template>
<script lang="ts" setup>
import { ElContainer, ElAside, ElMain, ElHeader, ElFooter } from "element-plus";
import { useWindowSize } from "@vueuse/core";
import LayoutAside from "./components/aside/index.vue";
import LayoutHeader from "./components/header/index.vue";
import LayoutMain from "./components/main/index.vue";
import { useAppStore } from "@/stores/app";

const appStore = useAppStore();

const { height: windowHeight, width: windowWidth } = useWindowSize({
	includeScrollbar: false,
});
</script>
<style lang="scss" scoped>
.el-aside {
	transition: all 0.3s;
}
</style>
