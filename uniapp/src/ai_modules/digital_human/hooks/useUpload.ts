import {
    ChooseResult,
    FileData,
    chooseFile,
    getFilesByExtname,
    normalizeFileData,
} from "@/components/file-upload/choose-file";
import { uploadImage, uploadFile } from "@/api/app";
import { formatAudioTime } from "@/utils/util";
import { DigitalHumanModelVersionEnum } from "../enums";

interface Options {
    size?: number; // 单位M
    resolution?: number[];
    duration?: number[];
    extension?: any[];
    onSuccess?: (res: any) => void;
    onError?: (e: any) => void;
}

export const commonUploadLimit = {
    size: 300,
    // 最小分辨率
    minResolution: 360,
    // 最大分辨率
    maxResolution: 1080,
    // 最小时长
    videoMinDuration: 5,
    // 最大时长
    videoMaxDuration: 600,
};

// 上传限制
export const uploadLimit: any = {
    [DigitalHumanModelVersionEnum.STANDARD]: {
        size: 100,
        // 最小分辨率
        minResolution: 480,
        // 最大分辨率
        maxResolution: 1080,
        // 最小时长
        videoMinDuration: 15,
        // 最大时长
        videoMaxDuration: 60,
    },
    [DigitalHumanModelVersionEnum.SUPER]: {
        size: 500,
        // 最小分辨率
        minResolution: 640,
        // 最大分辨率
        maxResolution: 2048,
        // 最小时长
        videoMinDuration: 2,
        // 最大时长
        videoMaxDuration: 120,
    },
    [DigitalHumanModelVersionEnum.ADVANCED]: commonUploadLimit,
    [DigitalHumanModelVersionEnum.ELITE]: commonUploadLimit,
};

export const useUpload = (options: Options) => {
    const {
        size = 100,
        resolution = [640, 2048],
        duration = [15, 60],
        extension = ["mp4", "mov"],
        onSuccess,
        onError,
    } = options;
    const uploadResult = reactive<Record<string, any>>({
        url: "",
        pic: "",
        seconds: 0,
        duration: "00:00",
    });

    const upload = async () => {
        const filesResult = await chooseFile({
            type: "video",
            camera: "front",
            sourceType: ["album"],
            extension,
            compressed: false,
        });
        chooseFileCallback(filesResult);
    };

    const chooseFileCallback = async (filesResult: ChooseResult) => {
        const { tempFilePaths, tempFiles } = filesResult;
        const file = tempFiles[0];
        // 判断是否大于100M
        const fileSize = file.size;
        const fileWidth = file.width;
        if (fileSize > size * 1024 * 1024) {
            uni.showToast({
                title: `视频大小不能超过${size}M`,
                icon: "none",
                duration: 4000,
            });
            return;
        } else if (fileWidth < resolution[0] || fileWidth > resolution[1]) {
            uni.showToast({
                title: `上传视频分辨率不能满足${resolution[0]}*${resolution[1]}`,
                icon: "none",
                duration: 4000,
            });
            return;
        } else if (file.duration < duration[0] || file.duration > duration[1]) {
            uni.showToast({
                title: `上传视频时长不能小于${duration[0]}秒或大于${duration[1]}秒`,
                icon: "none",
                duration: 4000,
            });
            return;
        }
        uni.openVideoEditor({
            filePath: tempFilePaths[0],
        })
            .then(async (res) => {
                const resDuration = res.duration / 1000;
                console.log("resDuration", resDuration);
                console.log("res.duration", res.duration);
                if (resDuration < duration[0] || resDuration > duration[1]) {
                    uni.showToast({
                        title: `裁剪视频时长不能小于${duration[0]}秒或大于${duration[1]}秒`,
                        icon: "none",
                        duration: 4000,
                    });
                    return;
                }
                uploadResult.duration = formatAudioTime(resDuration);
                uploadResult.seconds = Math.floor(resDuration);
                await Promise.allSettled([uploadImageFn(res.tempThumbPath), uploadVideo(res.tempFilePath)]);
                onSuccess?.(uploadResult);
            })
            .catch((error) => {
                const { errCode } = error;
                if (errCode === 803) return;
                uni.$u.toast(error?.errMsg || "视频编辑失败");
                onError?.(error);
            });
    };

    const uploadVideo = async (file: string) => {
        uni.showLoading({
            title: "上传中",
            mask: true,
        });
        try {
            const { uri }: any = await uploadFile("video", {
                filePath: file,
            });
            uploadResult.url = uri;
        } catch (error) {
            uni.$u.toast("上传失败");
        } finally {
            uni.hideLoading();
        }
    };

    const uploadImageFn = async (file: string) => {
        const { uri }: any = await uploadImage(file);
        uploadResult.pic = uri;
    };
    return {
        upload,
        uploadResult,
    };
};
