<template>
  <div class="time-picker">
    <CustomWheel type="hour" :data="hours" :selected="selectedHour" @dateChange="hourChanged" />
    <CustomWheel type="period" :data="periods" :selected="selectedPeriod" @dateChange="periodChanged" />
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import {CustomWheel} from '~/src/features/CustomWheel';

const props = defineProps({
  time: {
    type: String,
    required: true
  }
});

const emit = defineEmits(['timeChange']);

const hours = computed(() => Array.from({ length: 12 }, (_, i) => (i + 1).toString().padStart(2, '0')));
const periods = ['AM', 'PM'];

const selectedHour = ref(parseInt(props.time.split(':')[0]) % 12 || 12);
const selectedPeriod = ref(parseInt(props.time.split(':')[0]) >= 12 ? 'PM' : 'AM');

watch(() => props.time, (newTime) => {
  const hour = parseInt(newTime.split(':')[0]);
  selectedHour.value = hour % 12 || 12;
  selectedPeriod.value = hour >= 12 ? 'PM' : 'AM';
});

const hourChanged = (type, changedData) => {
  selectedHour.value = parseInt(changedData);
  emit('timeChange', formatTime(selectedHour.value, selectedPeriod.value));
};

const periodChanged = (type, changedData) => {
  selectedPeriod.value = changedData;
  emit('timeChange', formatTime(selectedHour.value, selectedPeriod.value));
};

const formatTime = (hour, period) => {
  let formattedHour;
  if (period === 'AM') {
    formattedHour = hour === 12 ? 0 : hour;
  } else {
    formattedHour = hour === 12 ? 12 : hour + 12;
  }
  return `${formattedHour.toString().padStart(2, '0')}:00`;
};
</script>

<style scoped>
.time-picker {
  display: flex;
  padding: 50px 20px;
  margin: 30px 0;
  overflow: hidden;
  width: 100%;
  background: white;
}

.hour,
.period,
.option{
  position: relative;
  height: 50px;
  margin: 0;
  border-top: 1px solid #e0e0e0;
  border-bottom: 1px solid #e0e0e0;
}

.hour::before,
.hour::after,
.period::before,
.period::after,
.option::before{
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

.hour::before,
.period::before,
.option::before {
  top: -51px;
}

.hour::after,
.period::after,
.option::after {
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
</style>