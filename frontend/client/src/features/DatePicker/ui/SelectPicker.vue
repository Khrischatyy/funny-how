<template>
  <div>
    <div class="select-container">
      <CustomWheel type="option" :data="optionLabels" :selected="selectedOptionLabel" @dateChange="optionChanged" />
    </div>
    <div v-if="selectedOption === 'custom'">
      <DatePicker :date="new Date()" @dateChange="customDateChanged" />
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import DatePicker from './DatePicker.vue';
import {CustomWheel} from '~/src/features/CustomWheel';

const today = new Date();
const tomorrow = new Date();
tomorrow.setDate(today.getDate() + 1);

const options = ref([
  { name: 'today', label: 'Today', date: today },
  { name: 'tomorrow', label: 'Tomorrow', date: tomorrow },
  { name: 'custom', label: 'Custom Date', date: null }
]);

const optionLabels = computed(() => options.value.map(option => option.label));
const selectedOptionIndex = ref(0);
const selectedOptionLabel = computed(() => optionLabels.value[selectedOptionIndex.value]);

const selectedOption = computed(() => options.value[selectedOptionIndex.value].name);
const customDate = ref();

const emit = defineEmits(['dateSelected']);
const optionChanged = (type, newLabel) => {
  const index = optionLabels.value.indexOf(newLabel);
  if (index !== -1 && newLabel !== 'custom') {
    selectedOptionIndex.value = index;
    updateDate();
  }
};


const updateDate = () => {
  const selected = options.value[selectedOptionIndex.value];
  if (selected.name === 'custom') {
    emit('dateSelected', { date: customDate.value });
  } else {
    emit('dateSelected', { date: selected.date.toISOString().split('T')[0] });
  }
};

const formatDateToYYYYMMDD = (date) => {
  const month = String(date.getMonth() + 1).padStart(2, '0');
  const day = String(date.getDate()).padStart(2, '0');
  const year = date.getFullYear();
  return `${year}-${month}-${day}`;
}

const customDateChanged = (type, newDate) => {
  let dateFormatted = formatDateToYYYYMMDD(newDate);
  // newDate is already a string in the format 'YYYY-MM-DD'
  customDate.value = dateFormatted;
  if (selectedOption.value === 'custom') {
    emit('dateSelected', { date: newDate.toISOString().split('T')[0] });
  }
};

watch(selectedOption, updateDate);
</script>

<style scoped>
.select-container {
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
