/**
 * 校验时间段列表
 * @param {Array<{start_time?:string, end_time?:string}>} list
 * @returns {{valid:boolean, errorType?:'empty'|'order'|'overlap'|'selfInvalid', indexes:number[]}}
 */
export function validateSchedule(list) {
    const toMin = (t) => {
        const [h, m] = (t || "").split(":").map(Number);
        return h * 60 + m;
    };

    for (let i = 0; i < list.length; i++) {
        const cur = list[i];

        // 1. 空值检查
        if (!cur || cur.start_time == null || cur.start_time === "" || cur.end_time == null || cur.end_time === "") {
            return { valid: false, errorType: "empty", indexes: [i] };
        }

        const s = toMin(cur.start_time);
        const e = toMin(cur.end_time);

        // 2. 自己倒序
        if (s >= e) {
            return { valid: false, errorType: "selfInvalid", indexes: [i] };
        }

        // 3. 与上一段比较
        if (i > 0) {
            const prev = list[i - 1];
            const pe = toMin(prev.end_time);

            if (s < pe) {
                // 重叠 / 顺序错误
                return { valid: false, errorType: "overlap", indexes: [i - 1, i] };
            }
        }
    }
    return { valid: true, indexes: [] };
}

/**
 * 检查相邻 publish_time 间隔是否 ≥ minGapMinutes
 * @param {Array<{publish_time:string}>} arr
 * @param {number} minGapMinutes  允许的最小间隔（分钟）
 * @returns {{valid:boolean, errorIndexes?:number[], gapMinutes?:number}}
 */
export function checkMinGap(arr, minGapMinutes = 15) {
    if (!Number.isFinite(minGapMinutes) || minGapMinutes <= 0) {
        throw new TypeError("minGapMinutes 必须是正数");
    }

    // 生成带原始索引的新数组，方便最后取回真正的下标
    const indexed = arr.map((item, idx) => ({ ...item, _idx: idx }));
    // 按时间升序
    indexed.sort((a, b) => new Date(a.publish_time).getTime() - new Date(b.publish_time).getTime());

    for (let i = 1; i < indexed.length; i++) {
        const prev = indexed[i - 1];
        const curr = indexed[i];
        const gap = (new Date(curr.publish_time).getTime() - new Date(prev.publish_time).getTime()) / (1000 * 60);

        if (gap < minGapMinutes) {
            return {
                valid: false,
                errorIndexes: [prev._idx, curr._idx], // 两条错误项的原始索引
                gapMinutes: gap,
            };
        }
    }
    return { valid: true };
}
