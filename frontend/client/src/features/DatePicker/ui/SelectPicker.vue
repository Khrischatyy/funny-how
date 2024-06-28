<template>
  <div>
    <div class="relative w-full flex items-center">
      <div class="flex items-center">
        <select @change="optionChanged" class="w-full border-double opacity-0 absolute top-0 px-3 h-[61px] outline-none rounded-[10px] focus:border-white border border-white border-opacity-20 bg-transparent text-white text-sm font-medium tracking-wide" name="workday">
          <option v-for="day in options" :value="day.name" :key="day.name">
            {{ day.label }}
          </option>
        </select>
      </div>
      <div class="relative border-double w-full flex items-center pointer-events-none">
        <input disabled :value="selectedOptionLabel" placeholder="Day"
               class="border-double w-full px-3 outline-none focus:border-white border border-white border-opacity-100 bg-transparent text-white text-sm font-medium tracking-wide"
               name="workday"/>
        <span class="absolute right-5 text-neutral-700 cursor-pointer">Day</span>
        <span class="absolute right-0 cursor-pointer">
          <IconDown/>
        </span>
      </div>
    </div>

    <div class="mt-5 w-full focus:border-white border border-white bg-transparent text-white text-sm font-medium tracking-wide" v-if="selectedOption === 'custom'">
      <DatePicker :date="customDate" @dateChange="customDateChanged"/>
      <input type="date" ref="systemDatePicker" @change="systemDateChanged" class="hidden"/>
      <div @click="triggerSystemPicker" class="font-[BebasNeue] cursor-pointer">
        Use system picker
      </div>
    </div>
  </div>
</template>

<script setup>
import {ref, computed, watch} from 'vue';
import DatePicker from './DatePicker.vue';
import {IconDown} from '~/src/shared/ui/common';

const today = new Date();
const tomorrow = new Date();
tomorrow.setDate(today.getDate() + 1);

const options = ref([
  {name: 'today', label: 'Today', date: today},
  {name: 'tomorrow', label: 'Tomorrow', date: tomorrow},
  {name: 'custom', label: 'Custom Date', date: null}
]);

const date = ref(today);

const optionLabels = computed(() => options.value.map(option => option.label));
const optionNames = computed(() => options.value.map(option => option.name));
const selectedOptionIndex = ref(0);
const selectedOptionLabel = computed(() => optionLabels.value[selectedOptionIndex.value]);

const selectedOption = computed(() => options.value[selectedOptionIndex.value].name);
const customDate = ref(new Date());

const emit = defineEmits(['dateSelected']);

const optionChanged = (event) => {
  const newLabel = event.target.value;
  const index = optionNames.value.indexOf(newLabel);
  selectedOptionIndex.value = index;

  if (newLabel !== 'custom') {
    customDate.value = null;
    updateDate();
  }
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

watch(selectedOption, updateDate);
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