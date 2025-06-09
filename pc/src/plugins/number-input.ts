/**
 * 数字输入框
 *
 * 使用方法：
 * <ElInput v-model="formData.keyword" type="number" v-number-input="{ max: 100, min: 0, decimal: 2, positive: true }" />
 *
 * 参数：
 * max: 最大值
 * min: 最小值
 * decimal: 小数位数
 * positive: 是否只能输入正数
 */
import type { DirectiveBinding } from "vue";

interface NumberInputOptions {
    max?: number;
    min?: number;
    decimal?: number;
    positive?: boolean;
}

export default defineNuxtPlugin((nuxtApp) => {
    nuxtApp.vueApp.directive("number-input", {
        mounted(el: HTMLElement | any, binding: DirectiveBinding<NumberInputOptions>) {
            const input = el.tagName === "INPUT" ? el : el.querySelector("input");
            if (!input) return;

            const options = binding.value || {};
            const { max, min, decimal = 0, positive = false } = options;

            const handleInput = (e: Event) => {
                const target: any = e.target as HTMLInputElement;
                let value = target.value;

                // 防止重复触发
                if (target._processing) return;
                target._processing = true;

                // 格式化处理
                value = value.replace(/[^\d.]/g, "");

                // 处理小数点
                const parts = value.split(".");
                if (parts.length > 2) {
                    value = parts[0] + "." + parts.slice(1).join("");
                }

                // 处理小数位数
                if (parts[1] && decimal >= 0) {
                    value = parts[0] + "." + parts[1].slice(0, decimal);
                }

                // 处理正负数
                if (positive && parseFloat(value) < 0) {
                    value = "0";
                }

                // 处理最大最小值
                let numValue = parseFloat(value);
                if (!isNaN(numValue)) {
                    if (max !== undefined && numValue > max) {
                        numValue = max;
                    }
                    if (min !== undefined && numValue < min) {
                        numValue = min;
                    }
                    value = decimal > 0 ? numValue.toFixed(decimal) : String(numValue);
                }

                // 只有当值真正改变时才更新
                if (target.value !== value) {
                    target.value = value;
                    // 使用 nextTick 确保值更新后再触发事件
                    nextTick(() => {
                        target.dispatchEvent(new Event("input"));
                    });
                }

                // 清除处理标记
                target._processing = false;
            };

            const handlePaste = (e: ClipboardEvent) => {
                e.preventDefault();
                const text = e.clipboardData?.getData("text");
                if (!text) return;
                const filtered = text.replace(/[^\d.]/g, "");
                document.execCommand("insertText", false, filtered);
            };

            const handleKeydown = (e: KeyboardEvent) => {
                const allowedKeys = [
                    "0",
                    "1",
                    "2",
                    "3",
                    "4",
                    "5",
                    "6",
                    "7",
                    "8",
                    "9",
                    ".",
                    "Backspace",
                    "Delete",
                    "ArrowLeft",
                    "ArrowRight",
                    "Tab",
                ];

                if (!allowedKeys.includes(e.key)) {
                    e.preventDefault();
                }

                if (e.key === "." && ((input as HTMLInputElement).value.includes(".") || decimal === 0)) {
                    e.preventDefault();
                }
            };

            input.addEventListener("input", handleInput);
            input.addEventListener("paste", handlePaste);
            input.addEventListener("keydown", handleKeydown);
            el._handlers = {
                input: handleInput,
                paste: handlePaste,
                keydown: handleKeydown,
            };
        },

        unmounted(el: HTMLElement | any) {
            const input = el.tagName === "INPUT" ? el : el.querySelector("input");
            if (!input || !(el as any)._handlers) return;

            input.removeEventListener("input", el._handlers.input);
            input.removeEventListener("paste", el._handlers.paste);
            input.removeEventListener("keydown", el._handlers.keydown);
            delete el._handlers;
        },
    });
});
