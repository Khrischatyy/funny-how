<template>
  <div class="date-picker">
    <CustomWheel type="day" :data="days" :selected="date.getDate()" @dateChange="dateChanged" />
    <CustomWheel type="month" :data="months" :selected="date.getMonth() + 1" @dateChange="dateChanged" />
    <CustomWheel type="year" :data="years" :selected="date.getFullYear() - 1899" @dateChange="dateChanged" />
  </div>
</template>

<script setup>
import { computed, watch, ref } from 'vue';
import { CustomWheel } from '~/src/features/CustomWheel';

const props = defineProps({
  date: {
    type: Date,
    required: true
  }
});

const emit = defineEmits(['dateChange']);

const days = computed(() => {
  return Array.from({ length: new Date(props.date.getFullYear(), props.date.getMonth() + 1, 0).getDate() }, (_, i) => i + 1);
});

const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'July', 'Aug', 'Sept', 'Oct', 'Nov', 'Dec'];

const years = Array.from({ length: 201 }, (_, i) => 1900 + i);

const dateChanged = (type, changedData) => {
  let newDate;

  if (type === 'day') {
    newDate = new Date(props.date.getFullYear(), props.date.getMonth(), changedData);
  } else if (type === 'month') {
    const maxDayInSelectedMonth = new Date(props.date.getFullYear(), changedData, 0).getDate();
    const day = Math.min(props.date.getDate(), maxDayInSelectedMonth);
    newDate = new Date(props.date.getFullYear(), changedData - 1, day);
  } else if (type === 'year') {
    const maxDayInSelectedMonth = new Date(changedData + 1900, props.date.getMonth() + 1, 0).getDate();
    const day = Math.min(props.date.getDate(), maxDayInSelectedMonth);
    newDate = new Date(changedData + 1900, props.date.getMonth(), day);
  }

  emit('dateChange', newDate);
};
</script>

<style scoped>
.center {
  text-align: center;
  font: 16px 'Roboto', sans-serif;
}

.date {
  font-size: 30px;
  font-weight: 300;
}

.date-picker {
  display: flex;
  padding: 50px 20px;
  margin: 30px 0;
  overflow: hidden;
  width: 100%;
  background: white;
}

.day,
.month,
.year {
  position: relative;
  height: 50px;
  margin: 0px;
  border-top: 1px solid #e0e0e0;
  border-bottom: 1px solid #e0e0e0;
  border-radius: 0;
}

.day::before,
.day::after,
.month::before,
.month::after,
.year::before,
.year::after {
  content: '';
  position: absolute;
  left: 0;
  width: 80px;
  height: 50px;
  background-color: white;
  opacity: 0.8;
  pointer-events: none;
  z-index: 1;
}

.day::before,
.month::before,
.year::before {
  top: -51px;
}

.day::after,
.month::after,
.year::after {
  bottom: -51px;
}

li {
  display: flex;
  justify-content: center;
  align-items: center;
  width: 80px;
  height: 50px;
  user-select: none;
}

.reset {
  width: 100px;
  height: 30px;
  border-radius: 15px;
  border: none;
  display: none;
  outline: none;
  color: white;
  background-color: #2466fb;
  box-shadow: 0 1px 10px -2px #2466fb;
  font-weight: 300;
}

.reset:active {
  transform: scale(0.95);
}
</style>