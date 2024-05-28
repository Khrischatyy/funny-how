<template>
  <div :class="open ? 'py-51' : ''" class="time-picker gap-2">
    <CustomWheel @dragging="dragging" type="hour" :data="hours" :selected="selectedHour" @dateChange="hourChanged" />
    <CustomWheel @dragging="dragging" type="period" :data="periods" :selected="selectedPeriod" @dateChange="periodChanged" />
  </div>
</template>

<script setup>
import {ref, computed, watch, onMounted} from 'vue';
import {definePageMeta, useRuntimeConfig} from '#imports'
import {CustomWheel} from '~/src/features/CustomWheel';
import axios from "axios";
import {useRoute} from "nuxt/app";

const props = defineProps({
  time: {
    type: String,
    required: false
  },
  open: {
    type: Boolean,
    required: false,
    default: false
  },
  rentingForm: {
    type: Object,
    required: false
  },
  availableHours: {
    type: Array,
    required: false,
    default: []
  }
});

const openTemp = ref(false);

const emit = defineEmits(['timeChange']);
const hours = computed(() => props.availableHours);
const periods = ['AM', 'PM'];


const selectedHour = ref(parseInt(props.time?.split(':')[0]) % 12 || 12);
const selectedPeriod = ref(parseInt(props.time?.split(':')[0]) >= 12 ? 'PM' : 'AM');
const route = useRoute();


watch(() => props.time, (newTime) => {
  const hour = parseInt(newTime?.split(':')[0]);
  selectedHour.value = hour % 12 || 12;
  selectedPeriod.value = hour >= 12 ? 'PM' : 'AM';
});

function processAvailableHours() {
  const hoursSet = new Set();
  props.availableHours.forEach(slot => {
    const startHour = parseInt(slot.start_time.split(':')[0]);
    const endHour = parseInt(slot.end_time.split(':')[0]);

    for (let hour = startHour; hour <= endHour; hour++) {
      hoursSet.add(hour % 12 || 12); // Convert to 12-hour format
    }
  });
  return Array.from(hoursSet).map(hour => hour.toString().padStart(2, '0'));
}

const dragging = (isDragging) => {
  emit('dragging', isDragging);
};
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
  padding: 0px 20px;
  margin: 30px 0;
  overflow: hidden;
  width: 100%;
  color: #fff;
  background: transparent;
  transition: all 0.3s cubic-bezier(0.25, 1, 0.5, 1);
}

.hour,
.period,
.option{
  position: relative;
  height: 50px;
  margin: 0;
}

.hour::before,
.hour::after,
.period::before,
.period::after,
.option::before{
  content: '';
  position: absolute;
  left: 0;
  width: 100%;
  height: 50px;
  background-color: white;
  opacity: 0;
  border-radius: 10px;
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
  width: 100%;
  height: 20px;
  user-select: none;
}
.py-51 {
  padding: 51px 0;
}
</style>