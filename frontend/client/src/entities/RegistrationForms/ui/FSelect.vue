<script setup lang="ts">
import { IconDown } from "~/src/shared/ui/common";
import { ref } from "vue";

const props = defineProps<{
  label?: string
  options: {
    id: number,
    name: string
  }[]
}>()

const value = ref<string | null>(null)

const emit = defineEmits({
  change: (value: string) => {
    return true
  }
})

const handleChange = (event: Event) => {
  const target = event.target as HTMLSelectElement
  emit('change', target.value)
}

</script>

<template>
  <div class="w-96 max-w-96 relative">
    <div class="flex items-center">
      {{options}}
      <select @change="handleChange" v-model="value" class="opacity-0 absolute top-0 cursor-pointer h-full w-full outline-none focus:border-white border border-white border-opacity-20 bg-transparent text-white text-sm font-medium tracking-wide" name="workday">
        <option v-for="option in props.options" :key="option.id" :value="option.name">{{ option.name }}</option>
      </select>
    </div>
    <div class="relative flex items-center pointer-events-none input border-double">
      <div class="w-full px-3 h-11 flex justify-start items-center outline-none focus:border-white border border-white border-opacity-100 bg-transparent text-white text-sm font-medium tracking-wide">
        {{ value || props.label }}
      </div>
      <span class="absolute right-0 cursor-pointer">
        <IconDown/>
      </span>
    </div>
  </div>
</template>

<style scoped lang="scss">

</style>
