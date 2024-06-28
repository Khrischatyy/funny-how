<template>
  <div :class="open ? 'py-51' : ''" class="time-picker gap-2">
    <CustomWheel
        @touchstart="preventTouch"
        @touchmove="preventTouch"
        @touchend="preventTouch"
        type="hour"
        :data="hours"
        :selected="selectedHour"
        @dateChange="hourChanged"
    />
    <CustomWheel
        @touchstart="preventTouch"
        @touchmove="preventTouch"
        @touchend="preventTouch"
        type="period"
        :data="periods"
        :selected="selectedPeriod"
        @dateChange="periodChanged"
    />
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { CustomWheel } from '~/src/features/CustomWheel';
import { useRoute } from "nuxt/app";

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
const processedHour = ref(0);
const preventTouch = (event) => {
  event.preventDefault();
};

const emit = defineEmits(['timeChange']);
const periods = ref(['AM', 'PM']);
const selectedHour = ref(0);
const selectedPeriod = ref('AM');
const dragging = (isDragging) => {
  emit('dragging', isDragging);
};

watch(() => props.time, (newTime) => {
  const hour = parseInt(newTime?.split(':')[0]);
  selectedHour.value = hour % 12 || 12;
  selectedPeriod.value = hour >= 12 ? 'PM' : 'AM';

});

const hours = computed(() => processAvailableHours());

function processAvailableHours() {
  const amHours = new Set();
  const pmHours = new Set();

  props.availableHours.forEach(time => {
    const hour = parseInt(time.split(':')[0]);
    if (hour < 12) {
      amHours.add(hour % 12 || 12);
    } else {
      pmHours.add(hour % 12 || 12);
    }
  });

  // Update periods based on available hours
  if (amHours.size === 0) periods.value = ['PM'];
  if (pmHours.size === 0) periods.value = ['AM'];

  return Array.from(new Set([...amHours, ...pmHours])).map(hour => hour.toString().padStart(2, '0'));
}

// onMounted(() => {
//   console.log('props.availableHours', props.availableHours)
//   // Initialize selected hour and period based on available hours
//   if (hours.value.length > 0) {
//     selectedHour.value = parseInt(hours.value[0]);
//     selectedPeriod.value = periods.value.includes('AM') ? (parseInt(hours.value[0]) < 12 ? 'AM' : 'PM') : 'PM';
//   }
//   console.log('selectedHour', selectedHour.value);
//   console.log('hours', hours.value);
// });

watch(() => props.availableHours, (newHours) => {
  if (hours.value.length > 0) {
    selectedHour.value = hours.value[0];
    selectedPeriod.value = periods.value.includes('AM') ? (parseInt(hours.value[0]) < 12 ? 'AM' : 'PM') : 'PM';
    emit('timeChange', props.availableHours[0]);
  }
  console.log('selectedHour', selectedHour.value);
  console.log('hours', hours.value);
});

const hourChanged = (type, changedDataIndex) => {
  console.log('changedDataIndex', changedDataIndex)
  processedHour.value = props.availableHours[changedDataIndex];
  console.log('periodChanged.selectedPeriod', selectedPeriod.value, processedHour.value);
  emit('timeChange', processedHour.value);
};

const periodChanged = (type, changedData) => {
  selectedPeriod.value = changedData;
  console.log('periodChanged.selectedPeriod', selectedPeriod.value, processedHour.value);
  emit('timeChange', formatTime(processedHour.value, selectedPeriod.value));
};

const formatTime = (hour, period) => {
  console.log('formatTime.hour', hour);
  let formattedHour;
  let [hourPart] = hour.split(':').map(Number);

  if (period === 'AM') {
    formattedHour = hourPart === 12 ? 0 : hourPart;
  } else {
    formattedHour = hourPart === 12 ? 12 : hourPart + 12;
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
.option {
  position: relative;
  height: 50px;
  margin: 0;
}

.hour::before,
.hour::after,
.period::before,
.period::after,
.option::before {
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