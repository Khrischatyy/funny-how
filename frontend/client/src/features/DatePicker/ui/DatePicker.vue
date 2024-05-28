<template>
  <div class="date-picker w-full">
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

const currentDate = ref(props.date);

const dateChanged = (type, changedData) => {
  let newDate;
  if (type === 'day') {
    newDate = new Date(currentDate.value.getFullYear(), currentDate.value.getMonth(), changedData);
  } else if (type === 'month') {
    const maxDayInSelectedMonth = new Date(currentDate.value.getFullYear(), months.indexOf(changedData), 0).getDate();
    const day = Math.min(currentDate.value.getDate(), maxDayInSelectedMonth);
    newDate = new Date(currentDate.value.getFullYear(), months.indexOf(changedData), day);
  } else if (type === 'year') {
    const maxDayInSelectedMonth = new Date(changedData, currentDate.value.getMonth(), 0).getDate();
    const day = Math.min(currentDate.value.getDate(), maxDayInSelectedMonth);
    newDate = new Date(changedData, currentDate.value.getMonth(), day);
  }
  currentDate.value = newDate;

  emit('dateChange', type, newDate);
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
  padding: 40px 20px;
  margin: 0px 0;
  overflow: hidden;
  width: 100%;
  background: transparent;
  color: #fff;
  justify-content: center;
  cursor: pointer;
}

.day,
.month,
.year {
  position: relative;
  height: 50px;
  margin: 0px;
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
  width: 100%;
  height: 50px;
  background-color: white;
  opacity: 0;
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