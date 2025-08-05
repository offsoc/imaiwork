<template>
    <div class="p-[18px]">
        <ElPopover
            placement="right"
            width="454px"
            popper-class="!p-6 !rounded-[20px]"
            :show-arrow="false"
            :popper-options="{
                placement: 'right',
                modifiers: [
                    {
                        name: 'offset',
                        options: { offset: [-150, 30] },
                    },
                ],
            }">
            <template #reference>
                <div class="flex items-center !h-11 px-[10px] cursor-pointer bg-app-bg-1 rounded-lg">
                    <span
                        class="flex-shrink-0 flex items-center justify-center rounded w-5 h-5 shadow-[0px_0px_0px_1px_rgba(255,255,255,0.1)]">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                            <rect opacity="0.1" x="0.5" y="0.5" width="19" height="19" rx="3.5" stroke="white" />
                            <path
                                d="M12 4C13.1046 4 14 4.89543 14 6V14C14 15.1046 13.1046 16 12 16H8C6.89543 16 6 15.1046 6 14V6C6 4.89543 6.89543 4 8 4H12ZM10 12C9.44772 12 9 12.4477 9 13C9 13.5523 9.44772 14 10 14C10.5523 14 11 13.5523 11 13C11 12.4477 10.5523 12 10 12Z"
                                fill="white" />
                        </svg>
                    </span>
                    <span class="ml-3 text-white font-bold">下载客户端</span>
                </div>
            </template>
            <div>
                <div class="mb-3">
                    <svg xmlns="http://www.w3.org/2000/svg" width="46" height="46" viewBox="0 0 46 46" fill="none">
                        <rect width="46" height="46" rx="10" fill="#202020" />
                        <rect x="0.5" y="0.5" width="45" height="45" rx="9.5" stroke="white" stroke-opacity="0.1" />
                        <rect x="4" y="4" width="38" height="38" rx="6" fill="url(#paint0_linear_1078_45410)" />
                        <mask
                            id="mask0_1078_45410"
                            style="mask-type: alpha"
                            maskUnits="userSpaceOnUse"
                            x="4"
                            y="4"
                            width="38"
                            height="38">
                            <rect x="4" y="4" width="38" height="38" rx="6" fill="black" />
                        </mask>
                        <g mask="url(#mask0_1078_45410)">
                            <g filter="url(#filter0_f_1078_45410)">
                                <path
                                    d="M16 12.5H30C31.933 12.5 33.5 14.067 33.5 16V45.5H12.5V16L12.5049 15.8203C12.5984 13.9708 14.1273 12.5 16 12.5Z"
                                    stroke="url(#paint1_linear_1078_45410)" />
                            </g>
                            <g filter="url(#filter1_f_1078_45410)">
                                <ellipse cx="23" cy="3" rx="13" ry="7" fill="#FFF4C9" />
                            </g>
                        </g>
                        <path
                            d="M12 16C12 13.7909 13.7909 12 16 12H30C32.2091 12 34 13.7909 34 16V46H12V16Z"
                            fill="url(#paint2_linear_1078_45410)" />
                        <path
                            d="M34 46V16C34 13.7909 32.2091 12 30 12H16C13.7909 12 12 13.7909 12 16V46"
                            stroke="#FFE8BA"
                            stroke-width="0.5" />
                        <path
                            d="M20 15C20 14.4477 20.4477 14 21 14H25C25.5523 14 26 14.4477 26 15C26 15.5523 25.5523 16 25 16H21C20.4477 16 20 15.5523 20 15Z"
                            fill="#D28D5F" />
                        <defs>
                            <filter
                                id="filter0_f_1078_45410"
                                x="8"
                                y="8"
                                width="30"
                                height="42"
                                filterUnits="userSpaceOnUse"
                                color-interpolation-filters="sRGB">
                                <feFlood flood-opacity="0" result="BackgroundImageFix" />
                                <feBlend mode="normal" in="SourceGraphic" in2="BackgroundImageFix" result="shape" />
                                <feGaussianBlur stdDeviation="2" result="effect1_foregroundBlur_1078_45410" />
                            </filter>
                            <filter
                                id="filter1_f_1078_45410"
                                x="-2"
                                y="-16"
                                width="50"
                                height="38"
                                filterUnits="userSpaceOnUse"
                                color-interpolation-filters="sRGB">
                                <feFlood flood-opacity="0" result="BackgroundImageFix" />
                                <feBlend mode="normal" in="SourceGraphic" in2="BackgroundImageFix" result="shape" />
                                <feGaussianBlur stdDeviation="6" result="effect1_foregroundBlur_1078_45410" />
                            </filter>
                            <linearGradient
                                id="paint0_linear_1078_45410"
                                x1="23"
                                y1="4"
                                x2="23"
                                y2="42"
                                gradientUnits="userSpaceOnUse">
                                <stop stop-color="#FFAC6D" />
                                <stop offset="0.375" stop-color="#B28496" />
                                <stop offset="1" stop-color="#2755AC" />
                            </linearGradient>
                            <linearGradient
                                id="paint1_linear_1078_45410"
                                x1="23"
                                y1="19.5"
                                x2="23"
                                y2="37.5"
                                gradientUnits="userSpaceOnUse">
                                <stop stop-opacity="0" />
                                <stop offset="1" />
                            </linearGradient>
                            <linearGradient
                                id="paint2_linear_1078_45410"
                                x1="23"
                                y1="12"
                                x2="23"
                                y2="46"
                                gradientUnits="userSpaceOnUse">
                                <stop stop-color="#EFC193" />
                                <stop offset="0.528846" stop-color="#D5C1CD" />
                                <stop offset="1" stop-color="#D0DCEE" />
                            </linearGradient>
                        </defs>
                    </svg>
                </div>
                <div class="font-bold text-[#00000cc]">
                    <div>强大算力，</div>
                    <div>赋能每一次智能进化</div>
                </div>
                <div class="text-[#00000080] mt-3 text-[11px]">
                    大模型、小应用，全都轻松搞定，释放技术无限潜力，让智能更快触达每一步！轻松应对创作、办公等多元需求，让工作更高效
                </div>
                <ElDivider class="!my-4 !border-token-primary" />
                <div>
                    <div class="font-bold text-[#00000cc]">相关下载</div>
                    <div class="mt-[15px] grid grid-cols-2 gap-[10px]">
                        <DefineTemplate v-slot="{ img, title, clientKey }">
                            <div class="client-item" @click="handleDownload(clientKey)">
                                <div class="flex items-center gap-x-[10px]">
                                    <div>
                                        <img :src="img" class="w-6 h-6 rounded border border-[#333333]" />
                                    </div>
                                    <div class="text-[11px]">
                                        <div class="text-[rgba(255,255,255,0.5)] text-[10px]">下载工具</div>
                                        <div class="text-white font-bold text-[11px]">{{ title }}</div>
                                    </div>
                                </div>
                                <div class="leading-[0]">
                                    <img src="@/assets/images/qrcode_icon.png" class="w-6 h-6" />
                                </div>
                            </div>
                        </DefineTemplate>
                        <template v-for="item in downloadClient">
                            <template v-if="item.key == ClientDownloadType.MiniPrograms">
                                <ElPopover
                                    v-if="getClient.mini_programs.status === '1'"
                                    :show-arrow="false"
                                    :teleported="false"
                                    placement="right"
                                    width="172px"
                                    popper-class="!border-none !rounded-xl !p-[10px]">
                                    <div class="w-full h-full flex items-center justify-center">
                                        <div class="w-[152px] h-[152px]">
                                            <img :src="getClient.mini_programs.url" class="w-full h-full" />
                                        </div>
                                    </div>
                                    <template #reference>
                                        <div>
                                            <DownloadTemplate
                                                :clientKey="item.key"
                                                :title="item.title"
                                                :img="item.img"></DownloadTemplate>
                                        </div>
                                    </template>
                                </ElPopover>
                            </template>
                            <template v-else>
                                <DownloadTemplate
                                    v-if="getClient[item.key].status === '1'"
                                    :clientKey="item.key"
                                    :title="item.title"
                                    :img="item.img"></DownloadTemplate>
                            </template>
                        </template>
                    </div>
                </div>
                <div class="flex mt-4">
                    <div class="text-xs flex justify-center gap-2 items-center p-1 bg-primary-light-9 rounded-full">
                        <Icon name="local-icon-tips3" :size="16" color="#ffffff"></Icon>
                        <span class="text-[#0000004d]">如在扫码或下载过程中遇到任何问题 </span>
                        <ElPopover
                            trigger="click"
                            :show-arrow="false"
                            :teleported="false"
                            placement="right"
                            popper-class="!border-none !rounded-xl !p-[10px] !w-[172px]">
                            <div class="w-full h-full flex items-center justify-center">
                                <div class="w-[152px] h-[152px]">
                                    <img :src="getCustomerService.wx_image" class="w-full h-full" />
                                </div>
                            </div>
                            <template #reference>
                                <span class="text-primary underline cursor-pointer">联系客服</span>
                            </template>
                        </ElPopover>
                    </div>
                </div>
            </div>
        </ElPopover>
        <ElTooltip placement="right" :show-arrow="false">
            <div class="flex items-center !h-11 px-[10px] cursor-pointer hover:bg-[#fbfbfb] rounded-lg mt-1">
                <span class="flex-shrink-0 flex items-center justify-center rounded w-5 h-5 bg-[#F1F1F1]">
                    <Icon name="local-icon-info" :size="14"></Icon>
                </span>
                <span class="ml-3 text-bold">免责声明</span>
            </div>
            <template #content>
                <div class="text-xs w-[400px] whitespace-pre-line">
                    免责声明 1. 服务条款与使用条件
                    本网站（以下简称“本站”）提供的服务（以下简称“服务”）旨在为用户提供信息和娱乐。在使用本站服务前，请您务必仔细阅读并遵守以下条款。您的使用行为将被视为对本声明内容的接受和认可。<br />
                    2. 内容来源与版权<br />
                    本站所展示的内容，包括但不限于文本、图片、音频、视频等，均由人工智能生成（AIGC），版权归本站所有。用户在使用这些内容时，应遵守相关法律法规，不得侵犯本站或第三方的合法权益。<br />
                    3. 内容准确性与完整性<br />
                    尽管本站努力提供准确和完整的信息，但本站不保证内容的准确性、可靠性或完整性。用户在使用本站内容时，应自行判断并承担相应风险。<br />
                    4. 免责声明<br />
                    本站不对因使用或无法使用本站内容而导致的任何直接、间接、附带、特殊、惩罚性或后果性损失承担责任。<br />
                    本站不对任何第三方通过本站提供的内容或服务承担责任。<br />
                    本站不对任何因技术故障、网络问题、通讯故障、电力故障、罢工、暴动、骚乱、战争、政府行为、恐怖主义行为、天气条件、自然灾害或其他任何不可抗力因素导致的服务中断、延迟、错误、遗漏或损失承担责任。<br />
                    5. 用户行为<br />
                    用户在使用本站服务时，应遵守法律法规，不得利用本站进行非法活动，包括但不限于侵犯他人知识产权、泄露他人隐私、发布违法信息等。对于用户的违法行为，本站有权采取相应措施，包括但不限于删除内容、暂停或终止服务。<br />
                    6. 修改与更新<br />
                    本站有权随时修改本免责声明。任何修改将通过本站公布，一经公布即生效。用户应定期查看本声明的最新版本。<br />
                    7. 法律适用与管辖<br />
                    本声明的解释、适用及争议解决均适用中华人民共和国法律。如遇任何争议，首先应友好协商解决；协商不成时，可提交本站所在地人民法院诉讼解决。<br />
                    8. 其他<br />
                    本声明未尽事宜，按照国家相关法律法规执行。本站对本声明拥有最终解释权。
                </div>
            </template>
        </ElTooltip>
        <div class="text-[rgba(0,0,0,0.3)] text-xs mt-2 mx-3">
            <div v-for="(item, index) in getCopyright" :key="index">
                <a :href="item.value" target="_blank" class="hover:underline">{{ item.key }}</a>
            </div>
            <div>{{ getByName }}</div>
            <div>Version：{{ getVersionName }}</div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { useAppStore } from "@/stores/app";
import { AppKeyEnum } from "@/enums/appEnums";
import DownLoadMacImg from "@/assets/images/down_mac_icon.png";
import DownLoadWebImg from "@/assets/images/down_windows_icon.png";
import DownLoadAndImg from "@/assets/images/down_and_icon.png";
import DownLoadMiniImg from "@/assets/images/down_mini_icon.png";

const appStore = useAppStore();
const { getCopyright, getVersion, getByName } = toRefs(appStore);

const getVersionName = computed(() => {
    const { version_number } = getVersion.value;
    if (!version_number) return;
    return `v${version_number.split("").join(".")}`;
});

const websiteConfig = computed(() => appStore.getWebsiteConfig);

enum ClientDownloadType {
    MacIntel = "mac_intel",
    MacApple = "mac_apple",
    Windows = "windows",
    Android = "android",
    MiniPrograms = "mini_programs",
}

const getClient = computed(() => {
    if (websiteConfig.value.client_download) {
        const { android, mac_intel, mac_apple, mini_programs, windows } = websiteConfig.value?.client_download || {};
        return {
            android,
            mac_intel,
            mac_apple,
            mini_programs,
            windows,
        };
    }
    return {};
});

const getCustomerService = computed(() => {
    if (websiteConfig.value.customer_service) {
        const { wx_image } = websiteConfig.value.customer_service;
        return {
            wx_image,
        };
    }
    return {};
});
const handleDownload = (key: string) => {
    if (getClient.value[key]?.url && key != ClientDownloadType.MiniPrograms) {
        window.open(getClient.value[key].url, "_blank");
    }
};

let render;
const DefineTemplate = {
    setup(_, { slots }) {
        return () => {
            render = slots.default;
        };
    },
};

const DownloadTemplate = (props) => {
    return render(props);
};

const downloadClient = [
    { key: ClientDownloadType.MacIntel, title: "Mac Intel芯片端", img: DownLoadMacImg },
    { key: ClientDownloadType.MacApple, title: "Mac M芯片端", img: DownLoadMacImg },
    { key: ClientDownloadType.Windows, title: "Win 客户端", img: DownLoadWebImg },
    { key: ClientDownloadType.Android, title: "安卓端", img: DownLoadAndImg },
    { key: ClientDownloadType.MiniPrograms, title: "Mini Programs", img: DownLoadMiniImg },
];
</script>

<style lang="scss">
.client-item {
    @apply cursor-pointer rounded-lg py-[6.5px] px-[10px] bg-black gap-x-[10px] flex items-center justify-between;
}
.btn-name {
    font-weight: bold;
    color: #313131;
}
</style>
