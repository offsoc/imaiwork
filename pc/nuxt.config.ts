import { URL, fileURLToPath } from "node:url";
import { createSvgIconsPlugin } from "vite-plugin-svg-icons";
import { getEnvConfig } from "./nuxt/env";
const envConfig = getEnvConfig();
export default defineNuxtConfig({
    srcDir: "src/",
    css: ["@/assets/styles/index.scss"],
    modules: ["@pinia/nuxt", "@nuxtjs/tailwindcss", "@element-plus/nuxt"],
    app: {
        baseURL: envConfig.baseUrl,
        head: {
            title: "AI数字员工开源系统",
            meta: [
                {
                    name: "description",
                    content:
                        "专注于AI数字员工解决方案，为企业提供智能化的虚拟助手和自动化员工，提升工作效率，降低人力成本。支持多场景应用，助力企业数字化转型。",
                },
                {
                    name: "keywords",
                    content: "AI数字员工, 虚拟助手, 自动化员工, 数字化转型, 智能化办公, 人工智能员工, 企业AI解决方案",
                },
                {
                    name: "og:title",
                    content: "AI数字员工系统开源AI应用解决方案",
                },
                {
                    name: "og:robots",
                    content: "noarchive, max-image-preview:large, max-video-preview:-1",
                },
            ],
        },
    },
    spaLoadingTemplate: "spa-loading.html",
    runtimeConfig: {
        public: {
            ...envConfig,
        },
    },
    ssr: !!envConfig.ssr,

    vite: {
        plugins: [
            createSvgIconsPlugin({
                iconDirs: [fileURLToPath(new URL("./src/assets/icons", import.meta.url))],
                symbolId: "local-icon-[dir]-[name]",
            }),
        ],
    },
});
