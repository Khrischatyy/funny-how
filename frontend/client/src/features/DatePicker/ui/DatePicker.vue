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
/* Add your styles here */
</style>