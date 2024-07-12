<template>
  <div class="relative w-full">
    <FSelect
      placeholder="Select Time"
      :options="structuredHours"
      :value="time"
      @change="timeChanged($event)"
      :scroll="true"
      class="w-full border-double focus:border-white border border-opacity-20 bg-transparent text-white text-sm font-medium tracking-wide"
    ></FSelect>
  </div>
</template>

<script setup lang="ts">
import { defineProps, defineEmits, ref, computed } from "vue"
import FSelect, { type OptionType } from "../shared/ui/common/Input/FSelect.vue"

type AvailbleHourType = {
  date: string
  iso_string: string
  time: string
}

const props = withDefaults(
  defineProps<{
    rentingForm: any
    label: string
    time: string
    availableHours?: AvailbleHourType[]
  }>(),
  {
    label: "Time",
    time: "09:00",
    rentingForm: {},
  },
)

const emit = defineEmits(["timeChanged"])
const structuredHours = computed(() => {
  const hours = props.availableHours?.map((hour, index) => {
    return {
      id: index + 1,
      label: hour.date,
      name: hour.iso_string,
    }
  })
  return hours
})

const timeChanged = (value: OptionType) => {
  // Parse the provided ISO string without converting to local time
  let time = new Date(value.name)
  console.log("value", value)

  // Function to pad single digit numbers with a leading zero
  const padZero = (number: number) => (number < 10 ? "0" : "") + number

  // Extract date and time components from the provided ISO string
  const [datePart, timePart] = value.name.split("T")
  const [year, month, day] = datePart.split("-")
  const [hour, minute] = timePart.split(":")

  // Format date as YYYY-MM-DD
  const formattedDate = `${year}-${padZero(parseInt(month))}-${padZero(
    parseInt(day),
  )}`

  // Format time as H:i
  const formattedTime = `${padZero(parseInt(hour))}:${padZero(
    parseInt(minute),
  )}`

  const formattedResponse = {
    date: formattedDate,
    time: formattedTime,
  }
  console.log("formattedTime", formattedResponse)

  emit("timeChanged", formattedResponse)
}
</script>

<style scoped></style>
