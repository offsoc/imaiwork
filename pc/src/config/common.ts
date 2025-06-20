import { AppKeyEnum, appKeyNameMap, FollowTypeEnum } from "@/enums/appEnums";

export const punctuationOptions = [
    { label: "换行符", value: "\\n" },
    { label: "中文逗号", value: "，" },
    { label: "英文逗号", value: "," },
    { label: "中文句号", value: "。" },
    { label: "英文句号", value: "." },
    { label: "中文叹号", value: "！" },
    { label: "英文叹号", value: "!" },
    { label: "中文分号", value: "；" },
    { label: "英文分号", value: ";" },
    { label: "中文问号", value: "？" },
    { label: "英文问号", value: "?" },
];

const applicationImages = {
    ...import.meta.glob("../assets/images/app/*.png", { eager: true }),
};

export const applicationsData = {
    [AppKeyEnum.LADDER_PLAYER]: {
        desc: "量身定制训练方案",
        desc2: "量身定制训练方案，智能纠错、即时反馈，让你的每一次练习都更高效。",
        followType: FollowTypeEnum.INTERNAL,
        is_online: true,
    },
    [AppKeyEnum.INTERVIEW]: {
        desc: "轻松筛选优质人才",
        desc2: "让企业管理更高效，智能招聘、自动简历筛选。",
        followType: FollowTypeEnum.INTERNAL,
        is_online: true,
    },
    [AppKeyEnum.MEETING_MINUTES]: {
        desc: "一键生成会议纪要",
        desc2: "省时、省力、更专业，智能纪要一步到位。",
        followType: FollowTypeEnum.INTERNAL,
        is_online: true,
    },
    [AppKeyEnum.MIND_MAP]: {
        desc: "快速打破思维惯性",
        desc2: "激活灵感源泉，快速打破思维惯性，生成多元、创新、可落地的解决方案。",
        followType: FollowTypeEnum.INTERNAL,
        is_online: true,
    },
    [AppKeyEnum.DOUBYIN]: {
        desc: "精准锁定潜在客户",
        desc2: "精准锁定潜在客户，自动跟进，全流程降本增效，帮助企业实现自动化增长，提高成交率。",
        followType: FollowTypeEnum.SMART_MARKETING,
        is_online: false,
    },
    [AppKeyEnum.KUAISHOU]: {
        desc: "短视频生成创意",
        desc2: "用智能（AI）驱动你的短视频创意，轻松生成爆款内容，告别繁琐剪辑，省时省力更高效！",
        followType: FollowTypeEnum.SMART_MARKETING,
        is_online: false,
    },
    [AppKeyEnum.SPH]: {
        desc: "提升曝光助力变现",
        desc2: "提升曝光，助力变现，开启你的AI视频创作之旅，从这里开始！",
        followType: FollowTypeEnum.SMART_MARKETING,
        is_online: false,
    },
    [AppKeyEnum.REDBOOK]: {
        desc: "智能分析受众",
        desc2: "一键生成爆款笔记，涨粉效率翻倍！",
        followType: FollowTypeEnum.SMART_MARKETING,
        is_online: true,
    },
    [AppKeyEnum.PERSON_WECHAT]: {
        desc: "轻松实现自动化管理",
        desc2: "高效回复、智能提醒、自动归类，助你轻松管理微信消息，告别漏消息与重复操作。",
        followType: FollowTypeEnum.CUSTOMER_MANAGEMENT,
        is_online: true,
    },
    [AppKeyEnum.TELEMARKETING]: {
        desc: "大幅提升销售转化率",
        desc2: "全天候高效运转，精准筛选意向客户，自动跟进、智能对话，大幅提升销售转化率。",
        followType: FollowTypeEnum.CUSTOMER_MANAGEMENT,
        is_online: false,
    },
    [AppKeyEnum.SERVICE]: {
        desc: "智能解答客户疑问",
        desc2: "智能应答每一个问题，释放人力成本，让您的团队专注于更重要的业务拓展。",
        followType: FollowTypeEnum.CUSTOMER_MANAGEMENT,
        is_online: true,
    },
    [AppKeyEnum.DIGITAL_HUMAN]: {
        desc: "专属于您的数字伙伴",
        desc2: "打造专属于您的数字伙伴，AI数字人带来无限可能。随着它们不断学习与成长，将为您的业务、生活、娱乐带来全新体验。",
        followType: FollowTypeEnum.CONTENT_MARKETING,
        is_online: true,
    },
    [AppKeyEnum.DRAWING]: {
        desc: "自动生成多样化方案",
        desc2: "为品牌注入新活力，助力企业实现更具影响力的视觉表达。",
        followType: FollowTypeEnum.CONTENT_MARKETING,
        is_online: true,
    },
    [AppKeyEnum.TAX]: {
        desc: "自动识别发票、智能报税申报",
        desc2: "自动识别发票、智能报税申报、实时风险监控，助力企业轻松应对税务合规挑战。",
        followType: FollowTypeEnum.OTHER,
        is_online: false,
    },
    [AppKeyEnum.LAW]: {
        desc: "合同审查、合规检测、风险预警",
        desc2: "合同审查、合规检测、风险预警到智能咨询，智能法务让法律变得更简单、更快速、更可靠。",
        followType: FollowTypeEnum.OTHER,
        is_online: false,
    },
    [AppKeyEnum.WORD]: {
        desc: "快速生成高质量文本",
        desc2: "快速生成高质量文本，帮你节省时间，提升效率。让写作不再枯燥，创意灵感随时闪现。",
        followType: FollowTypeEnum.OTHER,
        is_online: false,
    },
    [AppKeyEnum.PPT]: {
        desc: "告别繁琐排版和设计",
        desc2: "告别繁琐排版和设计，智能（AI）智能助力，让你的演示文稿瞬间焕然一新。",
        followType: FollowTypeEnum.OTHER,
        is_online: false,
    },
    [AppKeyEnum.COMPANY_WECHAT]: {
        desc: "多渠道消息统一管理",
        desc2: "多渠道消息统一管理，团队协作无缝连接，工作效率倍增！",
        followType: FollowTypeEnum.OTHER,
        is_online: false,
    },
    [AppKeyEnum.STATEMENT]: {
        desc: "轻松一键，自动为您提取关键指标",
        desc2: "轻松一键，自动为您提取关键指标，实时生成专业、精准的图表和总结。",
        followType: FollowTypeEnum.OTHER,
        is_online: false,
    },
    [AppKeyEnum.POSTER]: {
        desc: "精准捕捉视觉灵魂",
        desc2: "精准捕捉视觉灵魂，让每一张海报都成为吸睛利器，助你轻松赢得关注与转化。",
        followType: FollowTypeEnum.OTHER,
        is_online: false,
    },
    [AppKeyEnum.CONTRACT]: {
        desc: "精准地识别合同中的关键条款、潜在风险及法律漏洞",
        desc2: "精准地识别合同中的关键条款、潜在风险及法律漏洞，帮助您节省大量时间和人力成本。",
        followType: FollowTypeEnum.OTHER,
        is_online: false,
    },
    [AppKeyEnum.LIVE]: {
        desc: "替传统直播方式，24小时在线，随时为您带来精彩内容",
        desc2: "无需人工值守，大幅降低运营成本，轻松实现高效、可持续的内容传播。",
        followType: FollowTypeEnum.OTHER,
        is_online: true,
    },
};

export const applications = Object.keys(applicationImages).reduce((acc, key) => {
    const fileName = key.split("/").pop()?.split(".")[0] as AppKeyEnum;
    if (fileName && applicationsData[fileName]) {
        acc[fileName] = {
            key: fileName,
            name: appKeyNameMap[fileName],
            src: (applicationImages[key] as any).default,
            ...applicationsData[fileName],
        };
    }
    return acc;
}, {} as Record<AppKeyEnum, { name: string; src: string; desc: string; desc2: string; followType: FollowTypeEnum; key: AppKeyEnum }>);
