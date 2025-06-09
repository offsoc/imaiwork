import img_meituan from "../_assets/images/meituan.png";
import img_xianyu from "../_assets/images/xianyu.png";
import img_shopify from "../_assets/images/shopify.png";
import img_vinted from "../_assets/images/vinted.png";
import img_xiaohongshu from "../_assets/images/xiaohongshu.png";
import img_taobao from "../_assets/images/taobao.png";
import img_ebay from "../_assets/images/ebay.png";
import img_amazon from "../_assets/images/amazon.png";
import { ScenePromptEnum } from "../../_enums/chatEnum";

export enum DrawTypeEnum {
	GOODS = "商品图助手",
	MODEL = "试衣助手",
	TXT2IMAGE = "文生图助手",
	IMAGE2IMAGE = "图生图助手",
}

export const drawTypeEnumMap = {
	[DrawTypeEnum.GOODS]: 1,
	[DrawTypeEnum.MODEL]: 2,
	[DrawTypeEnum.TXT2IMAGE]: 3,
	[DrawTypeEnum.IMAGE2IMAGE]: 4,
};

export const drawRatio = [
	{
		name: "小红书",
		ratio: "300*300",
		value: [300, 300],
		image: img_xiaohongshu,
	},
	{
		name: "咸鱼",
		ratio: "800*600",
		value: [800, 600],
		image: img_xianyu,
	},
	{
		name: "亚马逊",
		ratio: "2000*2000",
		value: [2000, 2000],
		image: img_amazon,
	},
	{
		name: "淘宝",
		ratio: "970*600",
		value: [970, 600],
		image: img_taobao,
	},
	{
		name: "美团",
		ratio: "1080*1080",
		value: [1080, 1080],
		image: img_meituan,
	},
	{
		name: "shopify",
		ratio: "1600*2000",
		value: [1600, 2000],
		image: img_shopify,
	},
	{
		name: "vinted",
		ratio: "2000*1800",
		value: [2000, 1800],
		image: img_vinted,
	},
	{
		name: "ebay",
		ratio: "1600*1600",
		value: [1600, 1600],
		image: img_ebay,
	},
];

// 生成风格
export enum GenerateEnum {
	AMOZON = "amozon",
	DEFAULT = "default",
}

// 生成风格映射
export const generateEnumMap = {
	[GenerateEnum.AMOZON]: "简介风格",
	[GenerateEnum.DEFAULT]: "经典风格",
};

export enum PreviewTypeEnum {
	RESULT = "result",
	EXAMPLE = "example",
}

export enum FormGoodsTypeEnum {
	TEMP = 1,
	TEXT = 2,
}

export enum FormModelTypeEnum {
	UPPER_CLOTHES = 1,
	LOWER_CLOTHES = 2,
}
