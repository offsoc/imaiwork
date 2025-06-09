<template>
    <u-popup
        v-model="visible"
        mode="center"
        :closeable="true"
        border-radius="20"
        :z-index="99999"
        @close="emit('close')"
    >
        <view class="w-[640rpx] h-[980rpx]">
            <view class="p-[30rpx] text-lg text-main text-center"
            >关键词推荐</view
            >
            <view class="h-[80rpx]">
                <u-tabs
                    v-if="current !== -1"
                    :list="keywordCateList"
                    :is-scroll="true"
                    v-model="current"
                    :active-color="$theme.primaryColor"
                    @change="tabsChange"
                ></u-tabs>
            </view>
            <scroll-view class="h-[650rpx]" :scroll-y="true">
                <template
                    v-if="
                        keywordPromptData.prompt.length ||
                        keywordPromptData.cate_prompt.length
                    "
                >
                    <view
                        v-for="(item, index) in keywordPromptData.cate_prompt"
                        :key="index"
                        class="keyword-container"
                    >
                        <view class="keyword-title text-base text-[#666]">
                            {{ item.name }}({{ item?.prompt.length }})
                        </view>
                        <view
                            v-for="citem in item.prompt"
                            :key="citem.text"
                            class="keyword-item"
                            :class="{
                                'keyword-item-active': currentPrompt.includes(
                                    citem.prompt_en
                                )
                            }"
                            @click="onChoicePrompt(citem.prompt_en)"
                        >
                            {{ citem.prompt }}
                        </view>
                    </view>
                    <view
                        v-if="keywordPromptData.prompt.length"
                        class="keyword-container"
                    >
                        <view
                            v-if="keywordPromptData.cate_prompt.length"
                            class="keyword-title text-base text-[#666]"
                        >
                            其它({{ keywordPromptData.prompt.length }})
                        </view>
                        <view
                            v-for="item in keywordPromptData.prompt"
                            :key="item.text"
                            class="keyword-item"
                            :class="{
                                'keyword-item-active': currentPrompt.includes(
                                    item.prompt_en
                                )
                            }"
                            @click="onChoicePrompt(item.prompt_en)"
                        >
                            {{ item.prompt }}
                        </view>
                    </view>
                </template>
                <view
                    v-else
                    class="h-full flex flex-col justify-center items-center"
                >
                    <image
                        class="w-[300rpx] h-[300rpx]"
                        src="@/packages/static/images/drawing/empty.png"
                    ></image>
                    <view class="text-muted text-center">暂无关键词推荐</view>
                </view>
            </scroll-view>
            <view class="flex mt-[20rpx] px-[30rpx]">
                <button class="footer_btn full_btn" @click="onPromptAdd()">
                    添加到文本描述
                </button>
                <button
                    class="footer_btn plain_btn ml-[20rpx]"
                    @click="onAlternatePrompt()"
                >
                    替换当前文本
                </button>
            </view>
        </view>
    </u-popup>
</template>

<script lang="ts" setup>
import { nextTick, ref, watchEffect } from 'vue'
import { keywordCate, keywordPrompt } from '@/api/drawing'

const emit = defineEmits<{
    (event: 'update:modelValue', value: string): void
    (event: 'close'): void
}>()

const props = withDefaults(
    defineProps<{
        modelValue?: any
    }>(),
    {
        modelValue: ''
    }
)

const visible = ref<boolean>(false)
const current = ref<number>(-1)
const keywordCateList = ref<any[]>([])
const keywordPromptData = ref<any>({
    prompt: [],
    cate_prompt: []
})
const currentPrompt = ref<string[]>([])

watchEffect(() => {
    const prompt = props.modelValue
    if (prompt == '') {
        currentPrompt.value = []
    }
})

const onChoicePrompt = (prompt: string) => {
    const index = currentPrompt.value.findIndex(
        (item: string) => item == prompt
    )
    if (index >= 0) {
        currentPrompt.value.splice(index, 1)
        return
    }
    console.log(prompt)
    currentPrompt.value.push(prompt)
}

const open = () => {
    visible.value = true
    getKeywordCate()
}

const tabsChange = (index: number) => {
    current.value = index
    getKeywordPrompt()
}

const getKeywordCate = async () => {
    try {
        keywordCateList.value = await keywordCate()
        await nextTick()
        setTimeout(async () => {
            current.value = 0
            await getKeywordPrompt()
        }, 500)
    } catch (error) {
        console.log('获取关键词分类错误', error)
    }
}

const getKeywordPrompt = async () => {
    try {
        keywordPromptData.value = await keywordPrompt({
            id: keywordCateList.value[current.value].id
        })
    } catch (error) {
        console.log('获取关键词错误', error)
    }
}

// 添加关键词文本
const onPromptAdd = () => {
    visible.value = false
    if (props.modelValue.trim() == '') {
        const prompt = currentPrompt.value.length ? currentPrompt.value.join(',') : ''
        emit('update:modelValue', prompt)
    } else {
        const prompt = currentPrompt.value.length ? currentPrompt.value.join(',') : ''
        const keyword = `${props.modelValue} ${prompt ? `,${prompt}` : ''}`
        emit('update:modelValue', keyword)
    }
    currentPrompt.value = []
}

// 替换关键词文本
const onAlternatePrompt = () => {
    visible.value = false
    const prompt = currentPrompt.value.length ? currentPrompt.value.join(',') : ''
    emit('update:modelValue', prompt)
    currentPrompt.value = []
}

defineExpose({
    open
})
</script>

<style lang="scss" scoped>
.keyword-container {
    padding: 0 30rpx;
    .keyword-title {
        position: relative;
        padding: 20rpx;
        &::before {
            content: '';
            position: absolute;
            left: 0;
            top: 48%;
            transform: translateY(-52%);
            width: 8rpx;
            height: 28rpx;
            @apply bg-primary;
        }
    }
    .keyword-item {
        color: #101010;
        display: inline-block;
        padding: 12rpx 24rpx;
        margin-right: 20rpx;
        margin-bottom: 20rpx;
        border-radius: 8rpx;
        background-color: #f5f5f5;
    }
    .keyword-item-active {
        @apply text-primary bg-primary-light-9;
    }
}
.footer_btn {
    flex: 1;
    height: 80rpx;
    line-height: 80rpx;
    font-size: 28rpx;
    border-radius: 8rpx;
}
.full_btn {
    color: #ffffff;
    border: 1px solid;
    @apply bg-primary border-primary;
}
.plain_btn {
    background: #ffffff;
    border: 1px solid;
    @apply text-primary border-primary;
}
</style>
