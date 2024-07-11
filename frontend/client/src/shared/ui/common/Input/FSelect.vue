<script setup lang="ts">
import {IconDown} from "~/src/shared/ui/common";
import {computed, ref, watch} from "vue";

const props = defineProps<{
  label?: string;
  placeholder?: string;
  options: { id: number | string; name: string; label: string}[];
  modelValue: string | number | null;
  size?: 'sm' | 'md' | 'lg';
  error?: string | boolean;
  modelKey?: string;
}>();

const value = ref<string | number | null>(props.modelValue);

const emit = defineEmits({
  change: (value: string | number) => true,
  'update:modelValue': (value: string | number) => true,
});

const handleChange = (option) => {

  if(props.modelKey) {
    value.value = option[props.modelKey];
  } else {
    value.value = option;
  }
  emit('change', value.value);
  emit('update:modelValue', value.value);
  showOptions.value = false;
};

const showOptions = ref(false);
const toggleDropdown = () => {
  showOptions.value = !showOptions.value;
};

watch(() => props.modelValue, (newValue) => {
  if(props.modelKey) {
    value.value = newValue[props.modelKey];
  } else {
    value.value = newValue;
  }
});

const valueShow = computed(() => {
  console.log('value', value.value)
  console.log('props.modelKey', props.modelKey)
  console.log('props.options', props.options)
  if(props.modelKey) {
    return props.options.find(option => option[props.modelKey] == value.value)?.label || props.placeholder;
  }

  return value.value?.label || props.placeholder;
});

const optionsContainer = ref<HTMLElement | null>(null);
const optionsChoose = ref<HTMLElement | null>(null);
watch(showOptions, (newValue) => {
  if (newValue) {
    optionsContainer.value.classList.add('max-h-64');
    optionsContainer.value.classList.remove('opacity-100');
    optionsChoose.value.classList.add('border-b');
    optionsChoose.value.classList.remove('border-double');
    setTimeout(() => {
      optionsContainer.value.classList.add('opacity-100');
    }, 300); // Match the duration of the max-height transition
  } else {
    optionsContainer.value.classList.remove('max-h-64');
    optionsContainer.value.classList.remove('opacity-100');
    setTimeout(() => {
      optionsChoose.value.classList.add('border-double');
      optionsChoose.value.classList.remove('border-b');
    }, 100); // Match the duration of the max-height transition
  }
});
</script>

<template>
  <div class="w-full max-w-96 relative">
    <div class="label-action flex justify-between items-center w-full">
      <div v-if="label"
           :class="{ 'opacity-20': props.size === 'sm', 'opacity-100': props.size === 'md' || props.size === 'lg' }"
           class="text-white mb-1.5 text-sm font-normal tracking-wide">{{ label }}</div>
      <div v-if="error" class="text-right mb-1.5 text-red-500 text-sm font-normal tracking-wide">{{ error }}</div>
      <div v-if="slots?.action" class="action mb-1.5">
        <slot name="action" />
      </div>
    </div>
    <div class="flex items-center">
      <select
        @change="handleChange"
        v-model="value"
        class="opacity-0 absolute top-0 cursor-pointer h-full w-full outline-none focus:border-white border border-white border-opacity-20 bg-transparent text-white text-sm font-medium tracking-wide"
        name="workday"
      >
        <option value="" disabled>Choose {{ props.label }}</option>
        <option v-for="option in props.options" :key="option.id" :value="option.id">{{ option.name }}</option>
      </select>
    </div>

    <div class="relative">
      <div
          ref="optionsChoose"
          :class="['relative w-full flex items-center', showOptions ? '' : '']"
          class="h-[61px] cursor-pointer border-white border-double border-t border-l border-r" @click="toggleDropdown">
        <div class="w-full flex justify-start items-center gap-2 px-3 min-h-[61px] h-full outline-none bg-transparent text-white font-[BebasNeue] text-2xl font-medium tracking-wide">
          <slot name="icon" />
          {{ valueShow }}
        </div>
        <span class="absolute right-0 cursor-pointer">
        <IconDown />
      </span>
      </div>
      <div
          ref="optionsContainer"
          :class="['select-options', showOptions ? 'max-h-64 border-t mt-3' : 'max-h-0 ']"
          class="select-options custom-transition overflow-hidden absolute top-full left-0 border-l border-r border-b border-white h-auto w-full z-10 bg-black"
      >
        <ul class="w-full h-full overflow-y-auto">
          <li
              @click="handleChange(option)"
              v-for="(option, index) in props.options"
              :key="option.name"
              class="option cursor-pointer font-[BebasNeue] text-2xl hover:opacity-60 py-4"
          >
            <div class="w-full h-full flex items-center justify-center px-5">
              <div class="text-white font-medium tracking-wide w-full border-b border-white ">{{ option.label }}</div>
            </div>
          </li>
        </ul>
      </div>
    </div>
  </div>
</template>

<style scoped lang="scss">
.custom-transition {
  transition: all 0.3s cubic-bezier(0.25, 1, 0.5, 1);
}
</style>