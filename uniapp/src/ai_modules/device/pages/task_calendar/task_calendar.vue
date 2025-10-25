<template>
    <view class="task-calendar-container">
        <view class="task-calendar">
            <view class="calendar-header">
                <view class="header-left">
                    <view class="month-year">{{ currentYear }}年 {{ currentMonth }}月</view>
                    <view class="back-to-today" @click="backToToday">今天</view>
                </view>
                <view class="arrow-group">
                    <view class="arrow" @click="prevMonth">
                        <view class="arrow-icon arrow-left"></view>
                    </view>
                    <view class="arrow" @click="nextMonth">
                        <view class="arrow-icon arrow-right"></view>
                    </view>
                </view>
            </view>
            <view class="calendar-weekdays">
                <view class="weekday" v-for="day in weekDays" :key="day">{{ day }}</view>
            </view>
            <view class="calendar-grid">
                <view
                    class="grid-item"
                    :class="{
                        'not-current-month': !day.isCurrentMonth,
                        selected: day.date === selectedDate,
                        today: day.isToday,
                    }"
                    v-for="(day, index) in days"
                    :key="index"
                    @click="selectDate(day)">
                    <view class="day-number">{{ day.day }}</view>
                </view>
            </view>
        </view>
    </view>
</template>

<script setup lang="ts">
import { ref, onMounted } from "vue";

interface Day {
    date: string;
    day: number;
    isCurrentMonth: boolean;
    isToday: boolean;
}

const weekDays = ["日", "一", "二", "三", "四", "五", "六"];
const days = ref<Day[]>([]);
const currentYear = ref(new Date().getFullYear());
const currentMonth = ref(new Date().getMonth() + 1);
const selectedDate = ref("");

const generateCalendar = (year: number, month: number) => {
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    const todayDate = `${today.getFullYear()}-${today.getMonth() + 1}-${today.getDate()}`;

    const date = new Date(year, month - 1, 1);
    const firstDay = date.getDay();
    const daysInMonth = new Date(year, month, 0).getDate();

    const calendarDays: Day[] = [];

    const prevMonthEndDate = new Date(year, month - 1, 0);
    const prevMonthLastDay = prevMonthEndDate.getDate();
    const prevMonthYear = prevMonthEndDate.getFullYear();
    const prevMonth = prevMonthEndDate.getMonth() + 1;
    for (let i = firstDay; i > 0; i--) {
        const day = prevMonthLastDay - i + 1;
        calendarDays.push({
            date: `${prevMonthYear}-${prevMonth}-${day}`,
            day: day,
            isCurrentMonth: false,
            isToday: false,
        });
    }

    for (let i = 1; i <= daysInMonth; i++) {
        const dateStr = `${year}-${month}-${i}`;
        const isToday = dateStr === todayDate;
        calendarDays.push({
            date: dateStr,
            day: i,
            isCurrentMonth: true,
            isToday: isToday,
        });
    }

    const gridCells = 35;
    if (calendarDays.length < gridCells) {
        const nextMonthStartDate = new Date(year, month, 1);
        const nextMonthYear = nextMonthStartDate.getFullYear();
        const nextMonth = nextMonthStartDate.getMonth() + 1;
        let nextDay = 1;
        while (calendarDays.length < gridCells) {
            calendarDays.push({
                date: `${nextMonthYear}-${nextMonth}-${nextDay}`,
                day: nextDay,
                isCurrentMonth: false,
                isToday: false,
            });
            nextDay++;
        }
    }

    days.value = calendarDays;
};

const selectDate = (day: Day) => {
    selectedDate.value = day.date;
    if (!day.isCurrentMonth) {
        const [year, month] = day.date.split("-").map(Number);
        currentYear.value = year;
        currentMonth.value = month;
        generateCalendar(currentYear.value, currentMonth.value);
    }
};

const prevMonth = () => {
    currentMonth.value--;
    if (currentMonth.value < 1) {
        currentMonth.value = 12;
        currentYear.value--;
    }
    generateCalendar(currentYear.value, currentMonth.value);
};

const nextMonth = () => {
    currentMonth.value++;
    if (currentMonth.value > 12) {
        currentMonth.value = 1;
        currentYear.value++;
    }
    generateCalendar(currentYear.value, currentMonth.value);
};

const backToToday = () => {
    const today = new Date();
    currentYear.value = today.getFullYear();
    currentMonth.value = today.getMonth() + 1;
    selectedDate.value = `${today.getFullYear()}-${today.getMonth() + 1}-${today.getDate()}`;
    generateCalendar(currentYear.value, currentMonth.value);
};

onMounted(() => {
    const today = new Date();
    currentYear.value = today.getFullYear();
    currentMonth.value = today.getMonth() + 1;
    selectedDate.value = `${today.getFullYear()}-${today.getMonth() + 1}-${today.getDate()}`;
    generateCalendar(currentYear.value, currentMonth.value);
});
</script>

<style scoped>
.task-calendar-container {
    background-color: #f4f6f9;
    padding: 20rpx;
    min-height: 100vh;
}

.task-calendar {
    background-color: #ffffff;
    border-radius: 24rpx;
    padding: 30rpx;
    box-shadow: 0 8rpx 24rpx rgba(0, 0, 0, 0.05);
}

.calendar-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30rpx;
}

.header-left {
    display: flex;
    align-items: center;
}

.back-to-today {
    margin-left: 20rpx;
    padding: 8rpx 20rpx;
    background-color: #f0f2f5;
    color: #606266;
    border-radius: 30rpx;
    font-size: 24rpx;
    font-weight: 500;
    cursor: pointer;
    transition: background-color 0.3s;
}

.back-to-today:hover {
    background-color: #e4e7ed;
}

.month-year {
    font-size: 40rpx;
    font-weight: 600;
    color: #2c3e50;
}

.arrow-group {
    display: flex;
    align-items: center;
}

.arrow {
    width: 60rpx;
    height: 60rpx;
    display: flex;
    justify-content: center;
    align-items: center;
    cursor: pointer;
    border-radius: 50%;
    transition: background-color 0.3s;
}

.arrow:hover {
    background-color: #f0f0f0;
}

.arrow-icon {
    width: 16rpx;
    height: 16rpx;
    border-style: solid;
    border-color: #2c3e50;
    border-width: 0 4rpx 4rpx 0;
}

.arrow-left {
    transform: rotate(135deg);
}

.arrow-right {
    transform: rotate(-45deg);
}

.calendar-weekdays {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    margin-bottom: 20rpx;
}

.weekday {
    text-align: center;
    font-size: 28rpx;
    font-weight: 500;
    color: #909399;
}

.calendar-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 10rpx;
}

.grid-item {
    position: relative;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 80rpx;
    border-radius: 50%;
    cursor: pointer;
    transition: background-color 0.3s, color 0.3s;
}

.day-number {
    font-size: 30rpx;
    font-weight: 500;
    color: #606266;
}

.not-current-month .day-number {
    color: #c0c4cc;
}

.grid-item:hover:not(.selected) {
    background-color: #f2f6fc;
}

.today .day-number {
    color: #409eff;
    font-weight: 600;
}

.today:before {
    content: "";
    position: absolute;
    bottom: 10rpx;
    left: 50%;
    transform: translateX(-50%);
    width: 8rpx;
    height: 8rpx;
    border-radius: 50%;
    background-color: #409eff;
}

.selected {
    background-color: #409eff;
    color: #fff;
    box-shadow: 0 4rpx 12rpx rgba(64, 158, 255, 0.4);
}

.selected .day-number {
    color: #fff;
}

.selected.today:before {
    background-color: #fff;
}
</style>
