import { ChooseResult, chooseFile } from "@/components/file-upload/choose-file";
import { uploadImage, uploadFile } from "@/api/app";
import { DigitalHumanModelVersionEnum } from "../enums";

interface Options {
    size?: number; // 单位M
    resolution?: number[];
    duration?: number[];
    extension?: any[];
    maxDuration?: number; // 最大时长，单位秒
    sizeType?: Array<"compressed" | "original">;
    onSuccess?: (res: any) => void;
    onProgress?: (res: any) => void;
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
    [DigitalHumanModelVersionEnum.CHANJING]: {
        size: 2000,
        minResolution: 360,
        maxResolution: 2048,
        videoMinDuration: 30,
        videoMaxDuration: 300,
    },
    //闪剪
    [DigitalHumanModelVersionEnum.SHANJIAN]: {
        size: 100,
        minResolution: 360,
        maxResolution: 2000,
        videoMinDuration: 10,
        videoMaxDuration: 30,
    },
};

export const useUpload = (options: Options) => {
    const {
        size = 100,
        resolution = [640, 2048],
        duration = [15, 60],
        extension = ["mp4", "mov"],
        maxDuration = 10,
        sizeType = ["original"],
        onSuccess,
        onError,
        onProgress,
    } = options;
    const uploadResult = reactive<Record<string, any>>({
        url: "",
        pic: "",
        seconds: 0,
        duration: "00:00",
        width: 0,
        height: 0,
    });

    const upload = async () => {
        try {
            const filesResult = await chooseFile({
                count: 1,
                type: "video",
                camera: "front",
                sourceType: ["album"],
                extension,
                maxDuration,
                sizeType,
            });
            chooseFileCallback(filesResult);
        } catch (error) {
            onError?.(error);
        }
    };

    const chooseFileCallback = async (filesResult: ChooseResult) => {
        const { tempFiles } = filesResult;
        const file = tempFiles[0];
        const fileSize = file.size;
        const fileWidth = file.width;
        const fileHeight = file.height;

        uploadResult.width = fileWidth;
        uploadResult.height = fileHeight;
        const fileDuration = file.duration;
        const isResolutionValid = fileWidth >= resolution[0] && fileWidth <= resolution[1];
        const isDurationValid = fileDuration >= duration[0] && fileDuration <= duration[1];
        if (fileSize > size * 1024 * 1024) {
            uni.showToast({
                title: `视频大小不能超过${size}M`,
                icon: "none",
                duration: 4000,
            });
            return;
        } else if (!isResolutionValid) {
            uni.showToast({
                title: `上传视频分辨率不能满足${resolution[0]}-${resolution[1]}`,
                icon: "none",
                duration: 4000,
            });
            return;
        } else if (!isDurationValid) {
            uni.showToast({
                title: `上传视频时长不能小于${duration[0]}秒或大于${duration[1]}秒`,
                icon: "none",
                duration: 4000,
            });
            return;
        }
        try {
            await uploadImageFn(file.thumbTempFilePath);
            await uploadVideo(file.tempFilePath);
            onSuccess?.(uploadResult);
        } catch (error) {
            onError?.({
                type: "video",
                error: error,
            });
        }
    };

    const uploadVideo = async (file: string) => {
        const { uri }: any = await uploadFile(
            "video",
            {
                filePath: file,
            },
            (e) => {
                onProgress?.({ type: "video", progress: e });
            }
        );
        uploadResult.url = uri;
    };

    const uploadImageFn = async (file: string) => {
        const { uri }: any = await uploadImage(file, "", "", (e) => {
            onProgress?.({ type: "image", progress: e });
        });
        uploadResult.pic = uri;
    };
    return {
        upload,
        uploadResult,
    };
};
