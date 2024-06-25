<script setup lang="ts">
import {onMounted, ref, useSlots, watch} from "vue";
import {IconUpload} from "~/src/shared/ui/common";

const props = defineProps<{
  label?: string;
  placeholder?: string;
  modelValue: string | number | null;
  type?: string;
  size?: 'sm' | 'md' | 'lg';
  error?: string | boolean;
}>()
const slots = useSlots();
const value = ref<string | number | null>(props.modelValue);
const file = ref<File | null>(null);
const imageUrl = ref<string | null>(null);

const emit = defineEmits({
  'update:modelValue': (value: string | number | File) => true,
});

const handleInput = (event: Event) => {
  const target = event.target as HTMLInputElement;
  if (props.type === 'file' && target.files) {
    file.value = target.files[0];
    imageUrl.value = URL.createObjectURL(target.files[0]);
    emit('update:modelValue', target.files[0]);
  } else {
    value.value = target.value;
    emit('update:modelValue', target.value);
  }
};
const handlePlaceholderClick = () => {
  const fileInput = document.getElementById('fileInput') as HTMLInputElement;
  fileInput.click();
};

watch(() => props.modelValue, (newValue) => {
  if (props.type !== 'file') {
    value.value = newValue;
  }
});

</script>

<template>
  <div class="w-full flex-col flex">
    <div class="label-action flex justify-between items-center w-full">
      <div v-if="label"
           :class="{ 'opacity-20': props.size === 'sm', 'opacity-100': props.size === 'md' || props.size === 'lg' }"
           class="text-white mb-1.5 text-sm font-normal tracking-wide">{{ label }}</div>
      <div v-if="error" class="text-right mb-1.5 text-red-500 text-sm font-normal tracking-wide">{{ error }}</div>
      <div v-if="slots?.action" class="action mb-1.5">
        <slot name="action" />
      </div>
    </div>
    <div class="relative flex items-center justify-center">
    <div class="absolute left-3">
      <slot name="icon" />
    </div>
    <input
        v-if="props.type !== 'file'"
        v-bind="$attrs"
        @input="handleInput"
        v-model="value"
        :placeholder="props.placeholder"
        :class="{ 'pl-10': slots?.icon, 'border-red': error, 'border-white': !error && !slots?.icon, 'py-3': props.size === 'sm', 'py-5': props.size === 'md', 'py-7': props.size === 'lg'}"
        class="w-full flex justify-start items-center px-3 h-11 outline-none rounded-[10px] focus:border-white border border-opacity-20 focus:border-opacity-100 bg-transparent text-white text-sm font-medium tracking-wide"
        :type="props.type || 'text'"
    />
    <div v-else class="w-11 h-11 flex items-center justify-center border border-white border-opacity-20 rounded-[10px] bg-transparent text-white text-sm font-medium tracking-wide cursor-pointer" @click="handlePlaceholderClick">
      <input
          id="fileInput"
          v-bind="$attrs"
          type="file"
          class="hidden"
          :class="{ 'pl-10': slots?.icon, 'border-red': error, 'border-white': !error && !slots?.icon }"
          @change="handleInput"
      />
      <span v-if="!imageUrl" class="opacity-20">
        <IconUpload/>
      </span>
      <img v-if="imageUrl" :src="imageUrl" alt="Uploaded Image" class="w-full h-full object-cover rounded-[10px]" />
    </div>
  </div>
  </div>
</template>

<style scoped lang="scss">
</style>
