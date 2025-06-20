import { defineStore } from "pinia";
import { FOLLOW_LIST_KEY } from "@/enums/cacheEnums";
import { AppKeyEnum } from "@/enums/appEnums";
import { applications } from "@/config/common";
interface followState {
    followLists: AppKeyEnum[];
}

export const useFollowStore = defineStore("followStore", {
    state: (): followState => {
        const FOLLOW_LIST = useCookie<AppKeyEnum[]>(FOLLOW_LIST_KEY);
        return {
            followLists: FOLLOW_LIST.value ?? [],
        };
    },
    getters: {
        getFollowList: (state) => {
            return Object.values(applications).filter((item) => state.followLists.includes(item.key));
        },
    },
    actions: {
        toggleFollow(key: AppKeyEnum) {
            const oneYear = 360 * 24 * 60 * 60 * 1000;
            const followCookie = useCookie<AppKeyEnum[]>(FOLLOW_LIST_KEY, {
                expires: new Date(Date.now() + oneYear),
            });

            if (this.followLists.includes(key)) {
                this.followLists = this.followLists.filter((item) => item !== key);
            } else {
                this.followLists = [...this.followLists, key];
            }

            followCookie.value = this.followLists;
        },

        isFollow(key: AppKeyEnum): boolean {
            return this.followLists.includes(key);
        },
    },
});
