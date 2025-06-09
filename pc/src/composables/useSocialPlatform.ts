import { AppTypeEnum } from "@/enums/appEnums";

import RedBookIcon from "@/assets/images/redbook_icon.png";
import DouyinIcon from "@/assets/images/douyin_icon.png";
import KuaishouIcon from "@/assets/images/kuaishou_icon.png";
import BossIcon from "@/assets/images/boss_icon.png";
import WechatIcon from "@/assets/images/wechat_icon.png";

export enum SocialPlatformType {
    redbook = 3,
    douyin = 2,
    kuaishou = 1,
    boss = 4,
    wechat = 5,
}

export const useSocialPlatform = () => {
    // 社媒平台列表
    const socialPlatformList = [
        { name: "小红书", icon: RedBookIcon, type: AppTypeEnum.REDBOOK },
        // { name: "抖音", icon: DouyinIcon, type: SocialPlatformType.douyin },
        // { name: "快手", icon: KuaishouIcon, type: SocialPlatformType.kuaishou },
        // { name: "BOSS直聘", icon: BossIcon, type: SocialPlatformType.boss },
        // { name: "微信", icon: WechatIcon, type: SocialPlatformType.wechat },
    ];

    // 当前社媒平台
    const currentSocialPlatform = ref<AppTypeEnum>(AppTypeEnum.REDBOOK);

    return {
        socialPlatformList,
        currentSocialPlatform,
    };
};
