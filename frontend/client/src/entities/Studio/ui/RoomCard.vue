<template>
  <div
    class="bg-black p-4 rounded-md shadow-lg flex flex-col justify-between relative"
  >
    <div class="flex justify-between items-start mb-4">
      <div class="flex justify-start items-center gap-5">
        <div>
          <h3 class="text-2xl font-bold font-[BebasNeue] text-white">
            {{ room.name }}
          </h3>
        </div>
      </div>
      <div
        v-if="isDelete"
        @click.stop="openPopup()"
        class="flex items-center gap-3 cursor-pointer hover:opacity-70"
      >
        <IconTrash />
      </div>
    </div>

    <div class="mt-4 flex gap-3 justify-between items-center relative">
      <PhotoSwipe
        v-if="displayedPhotos"
        :key="photosUpdated"
        :photos="displayedPhotos"
        ref="photoSwipe"
      />
    </div>
    <div class="mt-4 flex gap-3 justify-between items-center">
      <div
        @mouseenter="showTooltip($event, generateTooltipContent('price'))"
        @click.stop="showTooltip($event, generateTooltipContent('price'))"
        @mouseleave="hideTooltip"
        class="flex items-center gap-2 relative group-price group"
      >
        <IconPrice class="opacity-20 group-hover:opacity-100" />
        <div class="flex flex-col group-hover:opacity-100">
          <span
            class="text-white opacity-20 font-['BebasNeue'] group-hover:opacity-100"
            >Price</span
          >
          <span class="text-white font-['BebasNeue']">{{ primaryPrice }}</span>
        </div>
      </div>
    </div>
    <Tooltip>
      <div class="text-left">
        {{ tooltipData.content }}
      </div>
    </Tooltip>
    <Teleport v-if="showPopup" to="body">
      <Popup
        :title="'Delete Studio'"
        type="small"
        :open="showPopup"
        @close="closePopup"
      >
        <template #header>
          <h1 class="text-white text-[22px]/[26px]">
            You sure you want to delete the studio by {{ room.name }}?
          </h1>
        </template>
        <template #body>
          <div class="equipment w-full flex flex-col gap-2">
            <FInputClassic
              label="Type in Delete studio to confirm"
              placeholder="Type in Delete studio"
              v-model="deleteConfirmation"
            />
          </div>
        </template>
        <template #footer>
          <div class="flex justify-between items-center gap-2 w-full">
            <button
              @click="closePopup"
              class="w-full h-11 p-3.5 hover:opacity-90 bg-transparent rounded-[10px] text-white border-white border text-sm font-medium tracking-wide"
            >
              Cancel
            </button>
            <button
              :disabled="deleteConfirmation.toLowerCase() !== 'delete studio'"
              :class="{
                'opacity-80':
                  deleteConfirmation.toLowerCase() !== 'delete studio',
              }"
              @click="deleteStudio()"
              class="w-full h-11 p-3.5 hover:opacity-80 bg-white rounded-[10px] text-neutral-700 border-white border text-sm font-medium tracking-wide"
            >
              Delete
            </button>
          </div>
        </template>
      </Popup>
    </Teleport>
  </div>
</template>

<script setup lang="ts">
import {computed, inject, ref, watch, watchEffect} from "vue"
import IconPrice from "~/src/shared/ui/common/Icon/Filter/IconPrice.vue"
import {
  FInputClassic,
  FSelectClassic,
  IconBooking,
  IconClock,
  IconDollar,
  IconLeft,
  IconLike,
  IconMic,
  IconMonitor,
  IconRight,
  IconTrash,
} from "~/src/shared/ui/common"

// Import the default images
import defaultLogo from "~/src/shared/assets/image/studio.png"

import defaultPhoto3_1 from "~/src/shared/assets/image/skeleton-studio-card/music-studio.png"
import defaultPhoto3_2 from "~/src/shared/assets/image/skeleton-studio-card/studio.png"
import defaultPhoto3_3 from "~/src/shared/assets/image/skeleton-studio-card/studio-microphone.png"
import { ScrollContainer } from "~/src/shared/ui/common/ScrollContainer"
import { Tooltip } from "~/src/shared/ui/Tooltip"
import { PhotoSwipe } from "~/src/shared/ui/components/PhotoSwipe"
import BadgesList from "~/src/widgets/BadgesChoose/ui/BadgesList.vue"
import { Clipboard } from "~/src/shared/ui/common/Clipboard"
import { Popup } from "~/src/shared/ui/components"
import { useApi } from "~/src/lib/api"
import { IconStatus } from "~/src/shared/ui/common/Icon/Filter"

const props = defineProps({
  room: {
    type: Object,
    required: true,
  },
  isDelete: {
    type: Boolean,
    default: false,
  },
})

const photosUpdated = ref(0)

watch(() => props.room.photos, () => {
  photosUpdated.value++
})



const emit = defineEmits(["update-studios"])

const showPopup = ref(false)
const openPopup = () => {
  showPopup.value = true
}
const closePopup = () => {
  showPopup.value = false
}

const deleteConfirmation = ref("")
const ICON_MAP = {
  mixing: IconMonitor,
  record: IconMic,
  rent: IconBooking,
}

const { tooltipData, showTooltip, hideTooltip } = inject("tooltipData")

const currentIndex = ref(0)
const badgeIndex = ref(0)

const displayedPhotos = computed(() => {
  let defaultPhotos = [
    { url: defaultPhoto3_1 },
    { url: defaultPhoto3_2 },
    { url: defaultPhoto3_3 },
  ]

  return props.room.photos
    ? props.room.photos.sort((a, b) => a.index - b.index)
    : defaultPhotos
})

function generateTooltipContent(type) {
  if (type === "price") {
    return props.room.prices
      .map((price) => {
        return `${price.total_price} / ${price.hours} hour`
      })
      .join("\n")
  }
}
const deleteStudio = () => {
  const { post: deleteAddress } = useApi({
    url: `/address/delete-studio`,
    auth: true,
  })

  deleteAddress({ address_id: props.room.id }).then(() => {
    emit("update-studios")
    closePopup()
  })
}

const todayWorkingHours = computed(() => {
  const today = new Date().getDay()
  return "24h"
})

const primaryPrice = computed(() => {
  if (props.room.prices && props.room.prices.length > 0) {
    const price = props.room.prices[0]
    return `$${parseInt(price.total_price)} / ${
      price.hours > 1 ? price.hours + " hours" : "hour"
    }`
  }
  return ""
})
</script>

<style scoped>
.group:hover .group-hover\:opacity-100 {
  opacity: 1 !important;
}

.group-price:hover .group-hover:opacity-100 {
  opacity: 1 !important;
}

.group-hours-block:hover .group-hover:opacity-100 {
  opacity: 1 !important;
}
</style>
