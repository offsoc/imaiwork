import { defineStore } from "pinia";
import { robotCategory } from "@/api/robot";
import { getConfig } from "@/api/app";
import { getChatConfig } from "@/api/chat";
import { checkSurvey } from "@/api/app";
interface AppSate {
    config: Record<string, any>;
    menuList: any[];
    hideSidebar: boolean;
    chatConfig: Record<string, any>;
    showSurvey: boolean;
}
export const useAppStore = defineStore("appStore", {
    state: (): AppSate => ({
        config: {},
        hideSidebar: false,
        menuList: [],
        // 通用聊天配置
        chatConfig: {},
        showSurvey: false,
    }),
    getters: {
        getWebsiteConfig: (state) => state.config.website || {},
        getCopyright: (state) => state.config.copyright || "",
        getVersion: (state) => state.config.version || "",
        getIndexConfig: (state) => state.config.index_config || [],
        getDigitalHumanConfig: (state) => state.config.digital_human || {},
        getMeetingConfig: (state) => state.config.meeting_config || {},
        getCardCodeConfig: (state) => state.config.card_code || {},
        getCopyRightConfig: (state) => state.config.copyright || [],
        getAppLiveConfig: (state) => state.config.ai_live || {},
        getHdConfig: (state) => state.config.draw || {},
        getAppConfig: (state) => state.config.app_config || {},
    },
    actions: {
        async getConfig() {
            const config = await getConfig();
            this.config = config;
        },
        async getMenu() {
            const data = await robotCategory({ page_size: 9999, pid: 0 });
            this.menuList = data.lists;
        },
        async getChatConfig() {
            const data = await getChatConfig();
            this.uploadAssistantId = data.assistants_id;
            this.chatConfig = data;
        },
        async getSurvey() {
            const { remind } = await checkSurvey();
            this.showSurvey = remind == 1;
        },
        toggleSidebar(toggle?: boolean) {
            this.hideSidebar = toggle ?? !this.hideSidebar;
        },
    },
});
