<template>
	<popup
		ref="promptPopRef"
		width="650"
		async
		title="快速组装提示词"
		cancel-button-text=""
		confirm-button-text="">
		<div>
			<div class="mt-4" v-if="optionsData.assembleLists.length > 0">
				<div class="mt-3">
					<ElTabs v-model="categoryVal" type="card">
						<ElTabPane
							:name="item.id"
							:label="item.title"
							v-for="(item, index) in optionsData.assembleLists">
							<div class="h-[26rem]">
								<ElScrollbar>
									<div class="grid grid-cols-4 gap-2">
										<div
											class="relative overflow-hidden cursor-pointer border-[2px] border-[transparent] bg-[#f5f5f5] rounded-lg hover:"
											v-for="(
												value, sub_index
											) in item.subs"
											:key="index"
											:class="[
												isNotIncluded(value)
													? 'border-primary'
													: '',
											]"
											@click="handleAssembleItem(value)">
											<img
												:src="value.pic"
												class="w-40 h-40 object-contain p-2" />
											<div
												class="line-clamp-3 text-xs mx-3 my-2 text-center">
												{{ value.title }}
											</div>
										</div>
									</div>
								</ElScrollbar>
							</div>
						</ElTabPane>
					</ElTabs>
				</div>
				<div
					class="bg-[#FAFAFA] border border-[#E8E8E8FF] rounded-lg px-4 py-2 min-h-[40px] relative mt-4">
					<div>
						{{ `${getAssemblePrompt}` }}
					</div>
					<div class="absolute bottom-1 right-1">
						<ElButton
							class="hover:!bg-token-sidebar-surface-secondary !p-2"
							link
							@click="assembleItemValue = []"
							:disabled="assembleItemValue.length == 0">
							<template #icon>
								<Icon
									name="el-icon-Delete"
									color="#FF5733"></Icon>
							</template>
						</ElButton>
					</div>
				</div>
				<div class="flex justify-center mt-4">
					<ElButton
						class="w-[50%]"
						type="primary"
						:disabled="assembleItemValue.length == 0"
						@click="useAssemblePrompt()"
						>使用此描述</ElButton
					>
				</div>
			</div>
			<ElEmpty v-else />
		</div>
	</popup>
</template>

<script setup lang="ts">
import { getQuickComposeList } from "@/api/drawing";

const emit = defineEmits(["on-assemble"]);

const categoryVal = ref();

const promptPopRef = shallowRef();
const assembleItemValue = ref<any[]>([]);

const { optionsData } = useDictOptions<{
	assembleLists: any[];
}>({
	assembleLists: {
		api: getQuickComposeList,
		transformData: (data) => {
			if (data.length) {
				categoryVal.value = data[0].id;
			}
			return data;
		},
	},
});

const getAssemblePrompt = computed(() => {
	return assembleItemValue.value.map((item) => item.title);
});

const isNotIncluded = (data: any) => {
	return assembleItemValue.value.some((item) => item.id == data.id);
};

const handleAssembleItem = (data: any) => {
	const isExist = assembleItemValue.value.some((item) => item.id == data.id);
	if (isExist) {
		assembleItemValue.value = assembleItemValue.value.filter(
			(item) => item.id != data.id
		);
	} else {
		assembleItemValue.value.push(data);
	}
};

const useAssemblePrompt = () => {
	emit("on-assemble", getAssemblePrompt.value);
	close();
};

const open = () => {
	promptPopRef.value.open();
};

const close = () => {
	promptPopRef.value.close();
};

defineExpose({
	open,
	close,
});
</script>

<style scoped></style>
