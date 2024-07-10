<template>
  <div>
    <FSelect @change="optionChanged" placeholder="Choose Day" :options="options" />

    <div class="mt-5 w-full focus:border-white border border-white bg-transparent text-white text-sm font-medium tracking-wide" v-if="selectedOption === 'custom'">
      <DatePicker :date="customDate" @dateChange="customDateChanged"/>
      <input type="date" ref="systemDatePicker" @change="systemDateChanged" class="hidden"/>
    </div>
  </div>
</template>

<script setup>
import {ref, computed, watch} from 'vue';
import DatePicker from './DatePicker.vue';
import {IconDown} from '~/src/shared/ui/common';
import FSelect from "../../../shared/ui/common/Input/FSelect.vue";

const today = new Date();
const tomorrow = new Date();
tomorrow.setDate(today.getDate() + 1);

const options = ref([
  {id:1, name: 'today', label: 'Today', date: today},
  {id: 2, name: 'tomorrow', label: 'Tomorrow', date: tomorrow},
  {id: 3, name: 'custom', label: 'Another Date', date: new Date()}
]);

const date = ref(today);

const optionLabels = computed(() => options.value.map(option => option.label));
const optionNames = computed(() => options.value.map(option => option.name));
const selectedOptionIndex = ref(0);
const selectedOptionLabel = computed(() => optionLabels.value[selectedOptionIndex.value]);

const selectedOption = computed(() => options.value[selectedOptionIndex.value].name);
const customDate = ref(new Date());

const emit = defineEmits(['dateSelected']);

const optionChanged = (value) => {
  const newLabel = value.name;
  const index = optionNames.value.indexOf(newLabel);
  selectedOptionIndex.value = index;
console.log('newLabel', newLabel)

  if(newLabel === 'today') {
    customDateChanged('date', today);
  }
  else if(newLabel === 'tomorrow') {
    customDateChanged('date', tomorrow);
  }
  else if(newLabel === 'custom') {
    customDateChanged('date', new Date());
  }
  else {
    customDateChanged('date', new Date());
  }


  // if (newLabel !== 'custom') {
  //   customDate.value = null;
  //   updateDate();
  // }
};

const updateDate = () => {
  const selected = options.value[selectedOptionIndex.value];
  if (selected.name === 'custom' && customDate.value) {
    emit('dateSelected', {date: customDate.value.toISOString().split('T')[0]});
  } else {
    emit('dateSelected', {date: selected.date.toISOString().split('T')[0]});
  }
};

const customDateChanged = (type, newDate) => {
  customDate.value = new Date(newDate);
  updateDate();
};

const systemDateChanged = (event) => {
  const selectedDate = new Date(event.target.value);
  selectedDate.setHours(selectedDate.getHours() + selectedDate.getTimezoneOffset() / 60);
  customDate.value = selectedDate;
  updateDate();
};

const systemDatePicker = ref(null);

const triggerSystemPicker = () => {
  systemDatePicker.value.showPicker();
};

watch(() => selectedOption, (newVal) => {
  if(newVal) {
    updateDate();
  }
});
</script>

<style scoped>
.select-container {
  display: flex;
  padding: 0px 20px;
  overflow: hidden;
  width: 100%;
  color: #fff;
  background: transparent;
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
.option::before,
.option::after {
  content: '';
  position: absolute;
  left: 0;
  width: 100%;
  height: 50px;
  background-color: white;
  border-radius: 10px;
  opacity: 0;
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
  height: 61px;
  user-select: none;
}

.border-double {
  height: 61px;
}

.border-double::before {
  content: '';
  position: absolute;
  bottom: 2px;
  left: 0;
  width: 100%;
  height: 100%;
  border-bottom: 1px solid white;
  pointer-events: none;
  z-index: 1;
}

.border-double::after {
  content: '';
  position: absolute;
  bottom: 4px;
  left: 0;
  width: 100%;
  height: 100%;
  border-bottom: 1px solid white;
  pointer-events: none;
  z-index: 1;
}
</style>