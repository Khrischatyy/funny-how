<script setup lang="ts">
import {onMounted, ref, useSlots, watch} from "vue";
import {IconUpload} from "~/src/shared/ui/common";

const props = defineProps<{
  label?: string;
  placeholder?: string;
  modelValue: string | number | null;
  type?: string;
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
  <div class="w-full flex-col flex gap-1.5">
    <div class="label-action flex justify-between items-center w-full">
      <div v-if="label" class="text-white text-sm font-normal tracking-wide opacity-20">{{ label }}</div>
      <div v-if="slots?.action" class="action">
        <slot name="action" />
      </div>
    </div>
    <div class="relative flex items-center justify-center">
    <div class="absolute left-3">
      <slot name="icon" />
    </div>
    <input
        v-if="props.type !== 'file'"
        @input="handleInput"
        v-model="value"
        :placeholder="props.placeholder"
        :class="slots?.icon?'pl-10':''"
        class="w-full flex justify-start items-center px-3 h-11 outline-none rounded-[10px] focus:border-white border border-white border-opacity-20 focus:border-opacity-100 bg-transparent text-white text-sm font-medium tracking-wide"
        :type="props.type || 'text'"
    />
    <div v-else class="w-11 h-11 flex items-center justify-center border border-white border-opacity-20 rounded-[10px] bg-transparent text-white text-sm font-medium tracking-wide cursor-pointer" @click="handlePlaceholderClick">
      <input
          id="fileInput"
          type="file"
          class="hidden"
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
