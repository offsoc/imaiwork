import { defineStore } from "pinia";
import { getConfig } from "@/api/app";
import { getChatConfig } from "@/api/chat";
import { getVoiceChatWssUrl } from "@/api/voice_chat";
interface AppSate {
    config: Record<string, any>;
    wssConfig: Record<string, any>;
    chatConfig: Record<string, any>;
    uploadAssistantId: string;
}
export const useAppStore = defineStore({
    id: "appStore",
    state: (): AppSate => ({
        config: {},
        wssConfig: {},
        chatConfig: {},
        uploadAssistantId: "",
    }),
    getters: {
        getWebsiteConfig: (state) => state.config.website || {},
        getLoginConfig: (state) => state.config.login || {},
        getVersion: (state) => state.config.version || "",
        getTabbarConfig: (state) => state.config.tabbar || {},
        getH5Config: (state) => state.config.webPage || {},
        getDigitalHumanModels: (state) => state.config.model_list || [],
        getWssConfig: (state) => state.wssConfig || {},
        getShareConfig: (state) => state.config.share || {},
        getMeetingConfig: (state) => state.config.meeting_config || {},
        getLadderConfig: (state) => state.config.lianlian || {},
        getDigitalHumanPrivacy: (state) => state.config.digital_human?.privacy || "",
    },
    actions: {
        getImageUrl(url: string) {
            return url.indexOf("http") ? `${this.config.domain}${url}` : url;
        },
        async getConfig(payload?: any) {
            const data = await getConfig(payload);
            this.config = data;
        },
        async getWssConfigParams() {
            const data = await getVoiceChatWssUrl();
            this.wssConfig = data;
        },
        async getChatConfig() {
            const data = await getChatConfig();
            this.uploadAssistantId = data.assistants_id;
            this.chatConfig = data;
        },
    },
});
