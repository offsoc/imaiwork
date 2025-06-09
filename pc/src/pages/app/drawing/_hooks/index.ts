import { ref, onUnmounted } from "vue";
import { drawingStatus } from "@/api/drawing";
import { DrawTypeEnum, drawTypeEnumMap } from "../_enums/drawEnums";

interface TaskParams {
	task_id: string;
	drawType: number;
	callback: (taskResultList: any[]) => void;
	onsuccess?: (data: any) => void;
}

export function useDrawingTask() {
	const loopTimer = ref<any>(null);
	const taskResultList = ref<any[]>([]);

	const initializeTaskResultList = (img_count: number, formData: any) => {
		for (let i = 0; i < img_count; i++) {
			taskResultList.value.push({
				image: "",
				loading: true,
				error: false,
				index: i,
				progress: 0,
				status: 0,
				formData,
			});
		}
	};

	const updateTaskResults = async ({
		task_id,
		drawType,
		callback,
	}: TaskParams) => {
		try {
			var isEnd = "";
			const res = await drawingStatus({ task_id, type: drawType });
			const { result = {} } = res.result;
			result.sub_task_results.forEach((item, index) => {
				const task_index =
					taskResultList.value.length +
					index -
					result.sub_task_results.length;
				if (item.image) {
					taskResultList.value[task_index].image = item.image;
					taskResultList.value[task_index].loading = false;
				}
				if (item.task_status === 3) {
					taskResultList.value[task_index].loading = false;
					if (drawTypeEnumMap[DrawTypeEnum.GOODS] !== drawType) {
						clearInterval(loopTimer.value);
					}
				}
				if (item.task_status === 4) {
					taskResultList.value[task_index].error = true;
					taskResultList.value[task_index].loading = false;
				}
				taskResultList.value[task_index].progress =
					// @ts-ignore
					parseInt(parseFloat(item.task_completion) * 100);

				taskResultList.value[task_index].status = item.task_status;
			});
			// 判断任务结束，当生成图片时，任务结束
			isEnd = result.sub_task_results.every(
				(item) => item.image || [3, 4].includes(item.task_status)
			);
			callback(taskResultList.value);

			if (isEnd) {
				clearInterval(loopTimer.value);
				return true;
			}
		} catch (err) {
			console.log(err);
			throw err;
		}
	};

	const handleError = (error: any, reject: (reason?: any) => void) => {
		feedback.msgError(error);
		taskResultList.value.forEach((item) => {
			if (!item.image) {
				item.error = true;
			}
		});
		clearInterval(loopTimer.value);
		reject(false);
	};

	const startLoopTimer = ({
		task_id,
		drawType,
		resolve,
		reject,
		callback,
		onsuccess,
	}: TaskParams & { resolve: Function; reject: Function }) => {
		// 标记是否正在执行请求
		let isRequesting = false;

		const executeTask = async () => {
			// 如果正在请求中，跳过本次执行
			if (isRequesting) {
				return;
			}

			try {
				isRequesting = true;
				const isEnd = await updateTaskResults({
					task_id,
					drawType,
					callback,
				});
				isRequesting = false;

				if (isEnd) {
					onsuccess?.(taskResultList.value);
					resolve(true);
				} else {
					// 等待上一个请求完成后，延迟1秒再执行下一次请求
					setTimeout(executeTask, 1000);
				}
			} catch (err) {
				isRequesting = false;
				reject(err);
			}
		};

		executeTask();
	};

	const processDrawingTask = async ({
		task_id,
		drawType,
		formData,
		callback,
		onsuccess,
	}: TaskParams & { formData: any }) => {
		initializeTaskResultList(formData.img_count || 1, formData);

		await new Promise((resolve, reject) => {
			startLoopTimer({
				task_id,
				drawType,
				resolve,
				reject,
				callback,
				onsuccess,
			});
		});
	};

	onUnmounted(() => {
		if (loopTimer.value) {
			clearInterval(loopTimer.value);
		}
	});

	return {
		taskResultList,
		processDrawingTask,
	};
}
