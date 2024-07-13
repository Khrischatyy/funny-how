<script setup lang="ts">
import { ScrollContainer } from "~/src/shared/ui/common/ScrollContainer"
import { computed, inject } from "vue"
import { defineProps } from "vue"
import { IconBooking, IconMic, IconMonitor } from "~/src/shared/ui/common"
import { Tooltip } from "~/src/shared/ui/Tooltip"
const props = withDefaults(
  defineProps<{
    badges: { id: number; name: string; description: string }[]
    theme?: string
  }>(),
  {
    theme: "default",
  },
)

const { tooltipData, showTooltip, hideTooltip } = inject("tooltipData")
const ICON_MAP = {
  mixing: IconMonitor,
  record: IconMic,
  rent: IconBooking,
}
</script>

<template>
  <div class="mt-4 flex gap-3 w-full justify-center items-center relative mb-5">
    <ScrollContainer v-bind="$attrs" :theme="theme">
      <div
        v-for="(badge, index) in badges"
        :key="badge.id"
        @click.stop="showTooltip($event, badge.description)"
        @mouseenter="showTooltip($event, badge.description)"
        @mouseleave="hideTooltip"
        @touchstart="showTooltip($event, badge.description)"
        @touchend="hideTooltip"
        class="relative w-8 h-full group scrollElement"
      >
        <Component
          :is="ICON_MAP[badge.name as typeof ICON_MAP]"
          class="w-full h-8 object-fit group-hover:opacity-70"
        />
        <!--                  <img :src="badge.image" :alt="badge.name" class="w-full h-full object-contain" />-->
        <div
          class="text-white text-xs text-center mt-1 group-hover:opacity-70 font-[BebasNeue]"
        >
          {{ badge.name }}
        </div>
      </div>
    </ScrollContainer>
    <Tooltip>
      {{ tooltipData.content }}
    </Tooltip>
  </div>
</template>

<style scoped lang="scss"></style>
