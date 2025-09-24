/**
 * 通用工具函数
 */

import type { Component } from "vue";

/**
 * 防抖函数
 * @param fn 需要防抖的函数
 * @param delay 延迟时间（毫秒）
 */
export const debounce = <T extends (...args: any[]) => any>(
    fn: T,
    delay: number
): ((...args: Parameters<T>) => void) => {
    let timeoutId: NodeJS.Timeout;
    return (...args: Parameters<T>) => {
        clearTimeout(timeoutId);
        timeoutId = setTimeout(() => fn(...args), delay);
    };
};

/**
 * 节流函数
 * @param fn 需要节流的函数
 * @param limit 时间限制（毫秒）
 */
export const throttle = <T extends (...args: any[]) => any>(
    fn: T,
    limit: number
): ((...args: Parameters<T>) => void) => {
    let inThrottle: boolean;
    return (...args: Parameters<T>) => {
        if (!inThrottle) {
            fn(...args);
            inThrottle = true;
            setTimeout(() => (inThrottle = false), limit);
        }
    };
};

/**
 * 格式化日期
 * @param date 日期对象或时间戳
 * @param format 格式化模板
 */
export const formatDate = (date: Date | number, format = "YYYY-MM-DD HH:mm:ss"): string => {
    const d = new Date(date);
    const year = d.getFullYear();
    const month = String(d.getMonth() + 1).padStart(2, "0");
    const day = String(d.getDate()).padStart(2, "0");
    const hours = String(d.getHours()).padStart(2, "0");
    const minutes = String(d.getMinutes()).padStart(2, "0");
    const seconds = String(d.getSeconds()).padStart(2, "0");

    return format
        .replace("YYYY", String(year))
        .replace("MM", month)
        .replace("DD", day)
        .replace("HH", hours)
        .replace("mm", minutes)
        .replace("ss", seconds);
};

/**
 * 异步加载组件的错误处理包装
 * @param asyncComponent 异步组件
 * @param fallback 加载失败时显示的组件
 */
export const withAsyncLoadingError = (asyncComponent: () => Promise<Component>, fallback: Component) => {
    return defineAsyncComponent({
        loader: asyncComponent,
        errorComponent: fallback,
        onError(error, retry, fail, attempts) {
            if (attempts <= 3) {
                retry();
            } else {
                fail();
                console.error("组件加载失败:", error);
            }
        },
    });
};

/**
 * 深度合并对象
 * @param target 目标对象
 * @param source 源对象
 */
export const deepMerge = <T extends object>(target: T, source: Partial<T>): T => {
    const result = { ...target };
    for (const key in source) {
        const targetValue = target[key];
        const sourceValue = source[key];
        if (
            sourceValue &&
            typeof sourceValue === "object" &&
            targetValue &&
            typeof targetValue === "object" &&
            !Array.isArray(sourceValue)
        ) {
            result[key] = deepMerge(targetValue, sourceValue);
        } else if (sourceValue !== undefined) {
            result[key] = sourceValue;
        }
    }
    return result;
};

/**
 * 创建带类型的事件处理器
 * @param handler 事件处理函数
 */
export const createEventHandler = <T extends Event>(handler: (event: T) => void) => {
    return (event: T) => {
        handler(event);
    };
};
