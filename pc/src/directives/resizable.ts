import type { Directive, DirectiveBinding } from "vue";

interface ResizableOptions {
    minWidth?: number;
    maxWidth?: number;
    minHeight?: number;
    maxHeight?: number;
    direction?: "horizontal" | "vertical" | "both";
}

const defaultOptions: ResizableOptions = {
    minWidth: 100,
    maxWidth: 2000,
    minHeight: 100,
    maxHeight: 2000,
    direction: "both",
};

export const vResizable: Directive = {
    mounted(el: HTMLElement, binding: DirectiveBinding<ResizableOptions>) {
        const options = { ...defaultOptions, ...binding.value };
        let isResizing = false;
        let startX = 0;
        let startY = 0;
        let startWidth = 0;
        let startHeight = 0;

        // 创建拖拽手柄
        const handle = document.createElement("div");
        handle.className = "resize-handle";
        handle.style.cssText = `
      position: absolute;
      right: 0;
      bottom: 0;
      width: 10px;
      height: 10px;
      cursor: se-resize;
      z-index: 100;
    `;
        el.appendChild(handle);

        // 设置目标元素样式
        el.style.position = "relative";

        const handleMouseDown = (e: MouseEvent) => {
            isResizing = true;
            startX = e.clientX;
            startY = e.clientY;
            startWidth = el.offsetWidth;
            startHeight = el.offsetHeight;

            // 添加事件监听
            document.addEventListener("mousemove", handleMouseMove);
            document.addEventListener("mouseup", handleMouseUp);
        };

        const handleMouseMove = (e: MouseEvent) => {
            if (!isResizing) return;

            if (options.direction === "horizontal" || options.direction === "both") {
                const newWidth = Math.min(
                    Math.max(startWidth + (e.clientX - startX), options.minWidth!),
                    options.maxWidth!
                );
                el.style.width = `${newWidth}px`;
            }

            if (options.direction === "vertical" || options.direction === "both") {
                const newHeight = Math.min(
                    Math.max(startHeight + (e.clientY - startY), options.minHeight!),
                    options.maxHeight!
                );
                el.style.height = `${newHeight}px`;
            }
        };

        const handleMouseUp = () => {
            isResizing = false;
            document.removeEventListener("mousemove", handleMouseMove);
            document.removeEventListener("mouseup", handleMouseUp);
        };

        handle.addEventListener("mousedown", handleMouseDown);
    },

    unmounted(el: HTMLElement) {
        // 清理事件监听
        const handle = el.querySelector(".resize-handle");
        if (handle) {
            handle.removeEventListener("mousedown", () => {});
        }
    },
};
