<template>
  <div
    ref="pswpElement"
    class="pswp"
    tabindex="-1"
    role="dialog"
    aria-hidden="true"
  ></div>
  <ScrollContainer justify-content="start">
    <div
      v-for="(photo, index) in displayedPhotos"
      :key="index"
      class="w-24 h-20 relative scrollElement cursor-pointer hover:scale-105 transition-scale duration-500"
    >
      <img
        :src="photo.src"
        @click.stop="() => openGallery(displayedPhotos, index)"
        alt="Photo"
        class="w-full h-full object-cover rounded-[10px]"
      />
    </div>
  </ScrollContainer>
</template>

<script lang="ts" setup>
import type { SlideData } from "photoswipe"
import { usePhotoSwipe } from "~/src/shared/ui/components/PhotoSwipe"
import { ScrollContainer } from "~/src/shared/ui/common/ScrollContainer"

const props = defineProps<{
  photos: { path: string; w?: number; h?: number }[]
}>()

const { pswpElement, openGallery } = usePhotoSwipe()

const displayedPhotos: SlideData[] = props.photos.map((photo) => ({
  src: photo.path,
  w: photo.w || 1200, // Default width if not specified
  h: photo.h || 900, // Default height if not specified
}))

defineExpose({ openGallery })
</script>
<style lang="scss">
@import "photoswipe/style.css";

.pswp__img {
  object-fit: cover;
}
</style>
