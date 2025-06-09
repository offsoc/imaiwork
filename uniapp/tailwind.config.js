import cssMacro from "weapp-tailwindcss/css-macro";
/** @type {import('tailwindcss').Config} */
module.exports = {
	content: ["./index.html", "./src/**/*.{html,js,ts,jsx,tsx,vue}"],
	theme: {
		colors: {
			white: "var(--color-white, #ffffff)",
			black: "var(--color-black, #000000)",
			main: "var(--color-main, #1D2129)",
			content: "var(--color-content, #666666)",
			muted: "var(--color-muted, #999999)",
			page: "var(--color-bg, #f7f7f7)",
			light: "var(--color-light, #E2E2E2)",
			disabled: "var(--color-disabled, #c8c9cc)",
			minor: "var(--color-minor, #0d0d0d)",
			"btn-text": "var(--color-btn-text, white)",
			voice: "var(--color-voice, #4AA061)",
			primary: {
				DEFAULT: "var(--color-primary, #2353f4)",
				"light-1": "var(--color-primary-light-1, #3d66f7)",
				"light-2": "var(--color-primary-light-2, #5879f9)",
				"light-3": "var(--color-primary-light-3, #729cfc)",
				"light-5": "var(--color-primary-light-5, #8cb0fd)",
				"light-6": "var(--color-primary-light-6, #a6c4fe)",
				"light-7": "var(--color-primary-light-7, #c1d8ff)",
				"light-8": "var(--color-primary-light-8, #f1f6ff)",
				"light-9": "var(--color-primary-light-9, #dbeeff)",
				"dark-2": "var(--color-primary-dark-2, #a6c4fe)",
			},
			success: {
				DEFAULT: "var(--color-success, #19be6b)",
				"light-3": "var(--color-success-light-3, rgb(94, 210, 151))",
				"light-5": "var(--color-success-light-5, rgb(140, 223, 181))",
				"light-7": "var(--color-success-light-7, rgb(186, 236, 211))",
				"light-9": "var(--color-success-light-9, rgb(232, 249, 240))",
				"dark-2": "var(--color-success-dark-2, rgb(20, 152, 86))",
			},
			warning: {
				DEFAULT: "var(--color-warning, #ff9900)",
				"light-3": "var(--color-warning-light-3, rgb(255, 184, 77))",
				"light-5": "var(--color-warning-light-5, rgb(255, 204, 128))",
				"light-7": "var(--color-warning-light-7, rgb(255, 224, 179))",
				"light-9": "var(--color-warning-light-9, rgb(255, 245, 230))",
				"dark-2": "var(--color-warning-dark-2, rgb(204, 122, 0))",
			},
			error: {
				DEFAULT: "var(--color-error, #ff2c3c)",
				"light-3": "var(--color-error-light-3, rgb(255, 107, 119))",
				"light-5": "var(--color-error-light-5, rgb(255, 150, 158))",
				"light-7": "var(--color-error-light-7, rgb(255, 192, 197))",
				"light-9": "var(--color-error-light-9, rgb(255, 234, 236))",
				"dark-2": "var(--color-error-dark-2, rgb(204, 35, 48))",
			},
			info: {
				DEFAULT: "var(--color-info, #909399)",
				"light-3": "var(--color-info-light-3, rgb(177, 179, 184))",
				"light-5": "var(--color-info-light-5, rgb(200, 201, 204))",
				"light-7": "var(--color-info-light-7, rgb(222, 223, 224))",
				"light-9": "var(--color-info-light-9, rgb(244, 244, 245))",
				"dark-2": "var(--color-info-dark-2, rgb(115, 118, 122))",
			},
		},
		fontSize: {
			xs: "24rpx",
			sm: "26rpx",
			base: "28rpx",
			lg: "30rpx",
			xl: "32rpx",
			"2xl": "34rpx",
			"3xl": "38rpx",
			"4xl": "40rpx",
			"5xl": "44rpx",
		},
		fontFamily: {
			sans: [
				"Source Han Sans CN",
				"Helvetica Neue",
				"Arial",
				"sans-serif",
			],
		},
	},
	corePlugins: {
		// 小程序不需要 preflight，因为这主要是给 h5 的，如果你要同时开发小程序和 h5 端，你应该使用环境变量来控制它
		preflight: false,
	},
	plugins: [
		cssMacro({
			variantsMap: {
				wx: "MP-WEIXIN",
				"-wx": {
					value: "MP-WEIXIN",
					negative: true,
				},
			},
		}),
	],
};
