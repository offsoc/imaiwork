<template>
    <div>
        <el-card class="!border-none" shadow="never">
            <el-page-header :content="headerTitle" @back="$router.back()" />
        </el-card>
        <el-card class="!border-none mt-4" shadow="never">
            <el-form class="ls-form" ref="formRef" :rules="rules" :model="formData" label-width="120px">
                <el-form-item label="图标" prop="logo">
                    <material-picker v-model="formData.logo" :limit="1" />
                </el-form-item>
                <el-form-item label="名称" prop="name">
                    <div class="w-[380px]">
                        <el-input placeholder="请输入名称" v-model="formData.name" />
                    </div>
                </el-form-item>
                <el-form-item label="简介" prop="description">
                    <div class="w-[380px]">
                        <el-input placeholder="添加有关于此机器人的功能简短描述" v-model="formData.description" />
                    </div>
                </el-form-item>
                <el-form-item label="指令" prop="instructions">
                    <div class="w-[380px]">
                        <el-input
                            placeholder="此机器人能做些什么?它的行为是怎么样的？它应该避免些什么?"
                            :rows="8"
                            type="textarea"
                            v-model="formData.instructions" />
                    </div>
                </el-form-item>
                <el-form-item label="表单词提问配置" prop="form_info" required>
                    <div class="w-[380px]">
                        <el-input
                            v-model="formData.form_info"
                            type="textarea"
                            :rows="6"
                            ref="elInputRef"
                            maxlength="2000"
                            resize="none"></el-input>
                        <div class="mt-2 flex flex-wrap gap-2">
                            <div
                                v-for="item in formData.template_info.form"
                                @click="insertAFormField(item.props.field)">
                                <ElButton>{{ item.props.title }}</ElButton>
                            </div>
                        </div>
                    </div>
                </el-form-item>

                <el-form-item label="对话开场白">
                    <div class="w-[380px] flex flex-col gap-y-1">
                        <div class="w-full" v-for="(item, index) in formData.preliminary_ask" :key="index">
                            <el-input
                                v-model="item.value"
                                placeholder=""
                                maxlength="50"
                                @input="inputDol($event, index)">
                                <template #append>
                                    <div @click="delDol(index)">
                                        <Icon name="el-icon-Close"></Icon>
                                    </div>
                                </template>
                            </el-input>
                        </div>
                    </div>
                </el-form-item>
                <el-form-item label="所属类目" prop="scene_id">
                    <div class="w-[380px]">
                        <el-select v-model="formData.scene_id" placeholder="请选择所属类目" class="w-full">
                            <el-option
                                v-for="(item, index) in getCategoryList"
                                :key="index"
                                :label="item.name"
                                :value="item.id" />
                        </el-select>
                    </div>
                </el-form-item>
                <el-form-item label="填写表单" prop="template_info.form">
                    <div class="flex-1 min-w-0 max-w-[1000px]">
                        <div class="flex">
                            <el-button type="primary" @click="handleAdd">添加</el-button>
                        </div>
                        <div class="mt-4">
                            <el-table
                                ref="tableRef"
                                size="large"
                                class="mt-4"
                                row-key="id"
                                :data="formData.template_info.form"
                                v-draggable="draggableOptions">
                                <el-table-column width="50">
                                    <template #default>
                                        <div class="move-icon cursor-move">
                                            <Icon name="el-icon-Rank" />
                                        </div>
                                    </template>
                                </el-table-column>
                                <el-table-column label="字段值" show-overflow-tooltip>
                                    <template #default="{ row }">
                                        <span>{{ row.props.field }}</span>
                                    </template>
                                </el-table-column>
                                <el-table-column label="字段名称" prop="props[title]" show-overflow-tooltip />
                                <el-table-column
                                    label="占位文字"
                                    prop="props[placeholder]"
                                    show-overflow-tooltip></el-table-column>
                                <el-table-column label="填写类型" prop="title" />
                                <el-table-column label="是否必填" prop="props[isRequired]">
                                    <template #default="{ row }">
                                        <el-switch v-model="row.props.isRequired" />
                                    </template>
                                </el-table-column>
                                <el-table-column label="操作" width="120" fixed="right">
                                    <template #default="{ row, $index }">
                                        <el-button type="primary" link @click="handleEdit(row, $index)">
                                            编辑
                                        </el-button>
                                        <el-button type="primary" link @click="handleDelete($index)"> 删除 </el-button>
                                    </template>
                                </el-table-column>
                            </el-table>
                        </div>
                    </div>
                </el-form-item>
                <el-form-item label="排序" prop="sort">
                    <div>
                        <el-input-number v-model="formData.sort" />
                        <div class="form-tips">默认为0，数值越大越排前面</div>
                    </div>
                </el-form-item>
                <el-form-item label="状态" prop="sort">
                    <el-switch v-model="formData.status" :active-value="1" :inactive-value="0" />
                </el-form-item>
            </el-form>
        </el-card>
        <form-designer-popup ref="formDesignerRef" @add="handleFormAdd" @edit="handleFormEdit" />
        <footer-btns>
            <el-button type="primary" @click="handleSave">保存</el-button>
        </footer-btns>
    </div>
</template>
<script lang="ts" setup>
import type { FormInstance, FormRules, InputInstance } from "element-plus";
import { getAssistantCategoryList } from "@/api/ai_assistant/category";
import { useThrottleFn } from "@vueuse/core";
import feedback from "@/utils/feedback";
import Sortable from "sortablejs";
import { setRangeText } from "@/utils/dom";
import { useDictOptions } from "@/hooks/useDictOptions";
const formRef = shallowRef<FormInstance>();

const props = defineProps<{
    headerTitle: string;
    modelValue: Record<string, any>;
    isEdit?: boolean;
}>();
const emit = defineEmits<{
    (event: "update:modelValue", value: Record<string, any>): void;
    (event: "submit"): void;
}>();

const formData = computed({
    get() {
        return props.modelValue;
    },
    set(value) {
        emit("update:modelValue", value);
    },
});
const tableRef = shallowRef();

const draggableOptions = [
    {
        selector: "tbody",
        options: {
            animation: 150,
            handle: ".move-icon",
            onEnd: ({ newIndex, oldIndex }: any) => {
                const arr = formData.value.template_info.form;
                const currRow = arr.splice(oldIndex, 1)[0];
                arr.splice(newIndex, 0, currRow);
                formData.value.form = [];
                nextTick(() => {
                    formData.value.template_info.form = arr;
                });
            },
        },
    },
];

const elInputRef = shallowRef<InputInstance>();
const insertAFormField = (field: string) => {
    formData.value.form_info = setRangeText(elInputRef.value?.textarea!, `\${${field}}`);
};

const inputDol = (e: any, index: number) => {
    if (formData.value.preliminary_ask.length > 5) return;
    const laseIndex = formData.value.preliminary_ask.length - 1;
    if (formData.value.preliminary_ask.length === 1 && !formData.value.preliminary_ask[0].value) return;
    if (formData.value.preliminary_ask[index].value && formData.value.preliminary_ask[laseIndex].value != "") {
        formData.value.preliminary_ask.push({ value: "" });
    } else if (!formData.value.preliminary_ask[index].value) {
        delDol(index);
    }
};

const delDol = (index: number) => {
    if (formData.value.preliminary_ask.length === 1) return;
    const laseIndex = formData.value.preliminary_ask.length - 1;
    if (formData.value.preliminary_ask[laseIndex].value != "") {
        formData.value.preliminary_ask.push({ value: "" });
    }
    formData.value.preliminary_ask.splice(index, 1);
};

//表单校验规则
const rules: FormRules = {
    name: [
        {
            required: true,
            message: "请输入名称",
        },
    ],
    description: [
        {
            required: true,
            message: "请输入助理简介",
        },
    ],
    instructions: [
        {
            required: true,
            message: "请输入全局指令",
        },
    ],
    form_info: [
        {
            required: true,
            message: "请配置表单词提问",
        },
    ],
    scene_id: [
        {
            required: true,
            message: "请选择所属类目",
        },
    ],
    logo: [
        {
            required: true,
            message: "请选择模型图标",
        },
    ],
    "template_info.form": [
        {
            type: "array",
            required: true,
            message: "请添加表单内容",
        },
    ],
};
const formDesignerRef = shallowRef();
//添加
const handleAdd = async () => {
    await nextTick();
    formDesignerRef.value?.open("add");
};
const handleFormAdd = useThrottleFn((value: any) => {
    const isSameField = !!formData.value.template_info.form.find((item: any) => item.props.field === value.props.field);
    const isSameType = !!formData.value.template_info.form.find((item: any) => item.name === "WidgetFile");
    if (isSameType) {
        return feedback.msgError("只能添加一个上传文件字段");
    }
    if (isSameField) {
        return feedback.msgError("字段值重复，请修改字段值");
    }
    formData.value.template_info.form.push(value);
    formDesignerRef.value?.close();
});
const currentIndex = ref(0);
const handleEdit = (value: any, index: number) => {
    currentIndex.value = index;
    formDesignerRef.value?.open("edit");
    formDesignerRef.value?.setFormData(value);
};

const handleDelete = async (index: number) => {
    await feedback.confirm("确定删除当前项？");
    formData.value.template_info.form.splice(index, 1);
};

const handleFormEdit = useThrottleFn((value: any) => {
    formData.value.template_info.form[currentIndex.value] = value;
    formDesignerRef.value?.close();
});

const { optionsData } = useDictOptions<{ categoryList: any[]; kbList: any[] }>({
    categoryList: {
        api: getAssistantCategoryList,
        transformData(data) {
            return data.lists;
        },
    },
});

const getCategoryList = computed(() => {
    const { categoryList = [] } = optionsData;
    const lists: any = [];
    categoryList.forEach((item) => {
        if (item.sub_list.length) {
            lists.push(...item.sub_list);
        }
    });
    return lists;
});

//提交
const handleSave = async () => {
    await formRef.value?.validate();
    emit("submit");
};
</script>
