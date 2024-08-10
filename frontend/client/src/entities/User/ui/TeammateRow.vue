<template>
  <div class="bg-black p-4 rounded-md shadow-lg flex flex-col lg:flex-row gap-10 justify-between">
    <div class="flex w-auto justify-start basis-auto flex-none items-center">
      <div class="flex justify-start  items-center gap-5">
        <div v-if="client?.profile_photo" class="h-[35px] w-[35px]">
          <img :src="client?.profile_photo" alt="Logo" class="h-auto w-full object-cover"/>
        </div>
        <div class="flex flex-col gap-2">
          <h3 class="text-xl font-bold text-white">{{ client?.role }}</h3>
          <Clipboard v-if="client?.username" :text-to-copy="phoneNormalizer(client?.username)">
            <div class="group flex relative gap-2 items-center justify-start">
              <p class="font-['Montserrat']">{{ client?.username }}</p>
            </div>
          </Clipboard>
        </div>
      </div>
    </div>
    <div
        class="flex w-auto flex-col lg:flex-row basis-full gap-8 min-w-[210px] justify-end items-start lg:items-center">
      <Clipboard v-if="client?.phone" :text-to-copy="client?.phone">
        <div class="flex items-center  max-w-[250px]  relative gap-2 ">
          <IconPhone class="opacity-20"/>
          <div class="flex flex-col group-hover:opacity-100">
            <span class="text-white opacity-20">Phone</span>
            <p class="text-white">{{ client?.phone }}</p>
          </div>
        </div>
      </Clipboard>
      <Clipboard v-if="client?.email" :text-to-copy="client?.email">
        <div class="flex items-center relative gap-2  max-w-[250px]  group-hours-block group">
          <IconEmail class="opacity-20"/>
          <div class="flex flex-col group-hover:opacity-100">
            <span class="text-white opacity-20">Email</span>
            <span class="text-white">{{ client?.email }}</span>
          </div>
        </div>
      </Clipboard>
      <Clipboard v-if="client?.address" :text-to-copy="client?.address">
        <div class="flex max-w-[250px] items-center relative gap-2 group-hours-block group">
          <IconAddress class="opacity-20 w-10 h-10"/>
          <div class="flex flex-col group-hover:opacity-100">
            <span class="text-white opacity-20">Where working</span>
            <span class="text-white">{{ client?.address }}</span>
          </div>
        </div>
      </Clipboard>
      <div class="flex items-center gap-2 min-w-[210px]  max-w-[250px]  relative group-price group">
        <IconStat class="opacity-20"/>
        <div class="flex flex-col group-hover:opacity-100">
          <span class="text-white opacity-20">Booking count</span>
          <span class="text-white">{{ client?.booking_count }}</span>
        </div>
      </div>
      <button
          @click="createEngineerPopup"
          class="w-full max-w-[150px] h-11 hover:opacity-90 border border-red-500 rounded-[10px] text-red-500 bg-red-500 bg-opacity-5 text-sm font-medium tracking-wide"
      >
        Dismiss
      </button>
    </div>
    <Tooltip>
      Phone: {{ tooltipData.content }}
    </Tooltip>
  </div>
</template>

<script setup lang="ts">
import {
  IconCalendar,
  IconCheckmark,
  IconClock,
  IconCopy,
  IconEmail, IconNav,
  IconPhone,
  IconStat,
  IconUser
} from "~/src/shared/ui/common";
import IconAddress from "~/src/shared/ui/common/Icon/IconAddress.vue";
import {getStatus, getColor, phoneNormalizer, BookingStatus} from "~/src/shared/utils";
import {inject, ref} from "vue";
import {Tooltip} from "~/src/shared/ui/Tooltip";
import {Clipboard} from "~/src/shared/ui/common/Clipboard";
import {IconStatus} from "~/src/shared/ui/common/Icon/Filter";
import {ManageBookingModal} from "~/src/widgets/Modals";
import AddEngineerModal from "~/src/widgets/Modals/AddEngineerModal.vue";

const {tooltipData, showTooltip, hideTooltip} = inject('tooltipData');

type Client = {
  id: number,
  firstname: string,
  username: string,
  phone: string,
  email: string,
  booking_count: number
}

const copySuccess = ref(false); // State to track copy success
const props = defineProps<{
  client: Client;
}>();

const copyToClipboard = (text: string) => {
  navigator.clipboard.writeText(text).then(() => {
    copySuccess.value = true; // Show notification
    setTimeout(() => copySuccess.value = false, 2000); // Hide after 2 seconds
  }).catch(err => {
    console.error('Failed to copy:', err);
  });
};

</script>

<style scoped>
</style>