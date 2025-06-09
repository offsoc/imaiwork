<template>
	<el-container
		class="h-full flex flex-col"
		:style="[{ height: `${windowHeight}px` }]">
		<el-header height="auto" style="padding: 0">
			<LayoutHeader is-back />
		</el-header>
		<el-container class="grow min-h-0 flex flex-col">
			<el-main class="!p-0 grow min-h-0">
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
		</el-container>
	</el-container>
</template>
<script lang="ts" setup>
import { ElContainer, ElAside, ElMain, ElHeader, ElFooter } from "element-plus";
import { useWindowSize } from "@vueuse/core";
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
