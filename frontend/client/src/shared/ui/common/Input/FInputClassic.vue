<!-- Input.vue -->
<script setup lang="ts">
import { ref, watch } from "vue";

const props = defineProps<{
  label?: string;
  modelValue: string | number | null;
}>()

const value = ref<string | number | null>(props.modelValue);

const emit = defineEmits({
  'update:modelValue': (value: string | number) => true,
});

const handleInput = (event: Event) => {
  const target = event.target as HTMLInputElement;
  value.value = target.value;
  emit('update:modelValue', target.value);
};

watch(() => props.modelValue, (newValue) => {
  value.value = newValue;
});
</script>

<template>
  <div class="relative flex items-center justify-center">
    <div class="absolute left-3">
      <slot name="icon" />
    </div>
      <input
          @input="handleInput"
          v-model="value"
          :placeholder="props.label"
          class="w-full flex justify-start items-center px-3 pl-10 h-11 outline-none rounded-[10px] focus:border-white border border-white border-opacity-20 focus:border-opacity-100 bg-transparent text-white text-sm font-medium tracking-wide"
          type="text"
      />
  </div>
</template>

<style scoped lang="scss">
</style>