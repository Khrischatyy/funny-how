<script setup lang="ts">
import { Popup } from "~/src/shared/ui/components"
import {
  computed,
  inject,
  onMounted,
  onUpdated,
  provide,
  reactive,
  ref,
  watch,
} from "vue"
import { FInputClassic } from "~/src/shared/ui/common"
import { HoursChoose } from "~/src/widgets/HoursChoose"
import { PriceChoose } from "~/src/widgets/PriceChoose"
import { BadgesChoose } from "~/src/widgets/BadgesChoose"
import { EquipmentChoose } from "~/src/widgets/EquipmentChoose"
import { AddStudioButton } from "~/src/features/addStudio"
import { useApi } from "~/src/lib/api"
import { ScrollContainer } from "~/src/shared/ui/common/ScrollContainer"
import { usePhotoSwipe } from "~/src/shared/ui/components/PhotoSwipe"
import type { SlideData } from "photoswipe"

const props = withDefaults(
  defineProps<{
    showPopup: boolean
  }>(),
  {
    showPopup: false,
  },
)

const emit = defineEmits(["togglePopup", "closePopup", "update-studios"])

type Studio = {
  id: number
  name: string
  address: string
  description: string
  hours: string
  price: number
  logo: string
  badges: string[]
  equipment: string[]
}
const studio = inject("studioForPopup")
const isLoading = ref(false)

const studioForm = reactive({
  name: "",
  address: "",
  description: "",
  slug: "",
  hours: "",
  price: 0,
  logo: "",
  badges: [],
  equipment: [],
  photos: [],
})

const isDragOver = ref(false)
const fileInputRef = ref<HTMLInputElement | null>(null)

const onFileChange = async (event: Event) => {
  const files = (event.target as HTMLInputElement).files
  console.log("files", files)
  if (files) {
    await handleFile(files)
  }
}

const openFileDialog = () => {
  fileInputRef.value?.click()
}

const onDragOver = () => {
  isDragOver.value = true
}

const onDragLeave = () => {
  isDragOver.value = false
}

const onDrop = async (event: DragEvent) => {
  isDragOver.value = false
  const files = event.dataTransfer?.files
  if (files && files.length > 0) {
    await handleFile(files)
  }
}

const getImageUrlByFile = (file: File) => {
  console.log(URL.createObjectURL(file))
  return URL.createObjectURL(file)
}
const getImageBase64 = (file: File) => {
  const reader = new FileReader()
  reader.readAsDataURL(file)
  reader.onload = () => {
    console.log(reader.result)
  }
}

const handleFile = async (files: FileList) => {
  isLoading.value = true
  const newPhotos = Array.from(files).map((file, index) => ({
    url: URL.createObjectURL(file),
    index: index,
    file,
  }))

  studioForm.photos = [...studioForm.photos, ...newPhotos]

  const { post: uploadPhoto } = useApi({
    url: `/photos/upload`,
    auth: true,
  })
  const formData = new FormData()
  //TODO: discuss how to send and accept photos, it works but strange
  for (let i = 0; i < files.length; i++) {
    formData.append(`photos[${i}]`, files[i])
  }
  formData.append("address_id", studio.value.id.toString())
  try {
    const response = await uploadPhoto(formData)
    console.log("Upload successful:", response.data)
    studioForm.photos = response.data
    emit("update-studios")
    // Handle response, possibly updating UI to reflect the uploaded state
  } catch (error) {
    console.error("Upload failed:", error)
    // Handle errors, show user feedback
  } finally {
    isLoading.value = false
  }
}

const togglePopup = () => {
  emit("togglePopup")
}

const updatePhotos = () => {
  if (!studio.value) return
  studioForm.photos = studio?.value.photos
    .map((photo) => ({
      url: photo.url,
      id: photo.id,
      index: photo.index,
      file: null,
    }))
    .sort((a, b) => a.index - b.index) // Sort photos by index
}

const resetForm = () => {
  Object.keys(studioForm).forEach((key) => {
    studioForm[key] = ""
  })
  studioForm.photos = []
}
// Update photos when the component is mounted or updated
onMounted(updatePhotos)
onUpdated(updatePhotos)

// Reset form and photos when the popup is closed
watch(
  () => props.showPopup,
  (newVal) => {
    if (!newVal) {
      resetForm()
    } else {
      updatePhotos()
    }
  },
)

watch(
  () => studio.value,
  (newVal) => {
    if (!newVal) return
    studioForm.photos = studio?.value.photos
      .map((photo) => ({
        url: photo.url,
        id: photo.id,
        index: photo.index,
        file: null,
      }))
      .sort((a, b) => a.index - b.index) // Sort photos by index

    studioForm.slug = studio.value.slug
  },
  { immediate: true },
)

const closePopup = () => {
  Object.keys(studioForm).forEach((key) => {
    studioForm[key] = ""
  })
  emit("closePopup")
}
const { pswpElement, openGallery } = usePhotoSwipe()
const displayedPhotos: SlideData[] = computed(() =>
  studioForm.photos.map((photo) => ({
    src: photo.url,
    w: photo.file?.width || 1200, // Default width if not specified
    h: photo.file?.height || 900, // Default height if not specified
  })),
)

const findRealIndexByUrl = (url: string) => {
  return studioForm.photos.findIndex((photo) => photo.url === url)
}

let draggedItemIndex = ref<number | null>(null)

function handleDragStart(event: DragEvent, index: number) {
  draggedItemIndex.value = index
  const dragItem = event.target as HTMLElement
  event.dataTransfer?.setData("text/plain", `${index}`)

  // Create a custom drag image
  const customImage = dragItem.cloneNode(true) as HTMLElement
  customImage.style.position = "absolute"
  customImage.style.top = "-9999px" // Move the image out of the viewport
  customImage.style.width = `${dragItem.offsetWidth}px` // Set width to the same as original
  customImage.style.height = `${dragItem.offsetHeight}px` // Set height to the same as original
  customImage.style.opacity = "0.8" // Adjust opacity for visual feedback
  customImage.classList.add("custom-drag-image")
  document.body.appendChild(customImage)
  event.dataTransfer?.setDragImage(customImage, 0, 0)
}

function handleDragEnter(event: DragEvent, index: number) {
  if (draggedItemIndex.value === null || draggedItemIndex.value === index)
    return
  // Reorder items
  const draggedItem = studioForm.photos.splice(draggedItemIndex.value, 1)[0]
  studioForm.photos.splice(index, 0, draggedItem)
  draggedItemIndex.value = index

  // Update the index property of photos after reordering
  studioForm.photos.forEach((photo, idx) => {
    photo.index = idx
  })
}

function handleDragEnd(event: DragEvent) {
  if (draggedItemIndex.value === null) return

  const photo = studioForm.photos[draggedItemIndex.value]
  const newIndex = draggedItemIndex.value
  console.log("photo", photo, "newIndex", newIndex)
  // Send photo_id and new index to the server
  updatePhotoOrder(photo.id, newIndex)

  draggedItemIndex.value = null
}

const updatedSlug = ref<string>("")
const updateSlug = () => {
  let slug = updatedSlug.value ? updatedSlug.value : studio.value?.slug
  const { put: updateSlug } = useApi({
    url: `/address/${slug}/update-slug`,
    auth: true,
  })

  updateSlug({ new_slug: studioForm.slug })
    .then((response) => {
      console.log("Update successful:", response.data)
      updatedSlug.value = response.data?.slug
      emit("update-studios")
    })
    .catch((error) => {
      console.error("Update failed:", error)
    })
}
const updatePhotoOrder = async (photoId: number, newIndex: number) => {
  const { post: updatePhotoOrder } = useApi({
    url: `/photos/update-index`,
    auth: true,
  })
  try {
    const response = await updatePhotoOrder({
      address_photo_id: photoId,
      index: newIndex,
    })
    emit("update-studios")
    console.log("Update successful:", response.data)
    // Handle response, possibly updating UI to reflect the uploaded state
  } catch (error) {
    console.error("Update failed:", error)
    // Handle errors, show user feedback
  }
}
</script>

<template>
  <Popup :title="'Add Studio'" :open="showPopup" @close="closePopup">
    <template #header>
      <div class="input-container flex gap-2">
        <div v-if="studio.company.logo_url" class="logo">
          <img
            :src="studio.company.logo_url"
            alt="logo"
            class="w-[40px] h-[40px] object-cover rounded-[10px]"
          />
        </div>
        <div class="name">
          <FInputClassic
            :placeholder="studio.company.name"
            :disabled="true"
            v-model="studioForm.name"
          />
        </div>
      </div>
    </template>
    <template #body>
      <div class="photos mb-5">
        <div v-if="isLoading" class="spinner-container">
          <div class="spinner"></div>
          <!-- Replace with a proper loading indicator -->
        </div>
        <div
          ref="pswpElement"
          class="pswp"
          tabindex="-1"
          role="dialog"
          aria-hidden="true"
        ></div>
        <div
          :class="[
            studioForm?.photos.length > 0
              ? 'sm:grid-cols-[1fr_1fr_250px] grid-rows-3'
              : '',
            { 'grid-rows-1': studioForm?.photos.length === 0 },
          ]"
          class="grid-cols-1 sm:grid-rows-1 grid gap-5"
        >
          <div
            v-if="studioForm?.photos.length > 0"
            class="cover-photo max-h-60"
          >
            <img
              :src="studioForm?.photos[0].url"
              draggable="true"
              @dragstart="handleDragStart($event, 0)"
              @dragover.prevent
              @dragenter="handleDragEnter($event, 0)"
              @dragend="handleDragEnd"
              @click.stop="() => openGallery(displayedPhotos, 0)"
              alt="cover photo"
              class="w-full drag-item h-full object-cover rounded-[10px]"
            />
          </div>
          <div
            :class="{
              hidden: studioForm?.photos.length == 0,
              'grid-rows-1':
                studioForm?.photos.slice(3, studioForm?.photos.length).length ==
                0,
            }"
            class="grid grid-cols-1 grid-rows-2 gap-5 max-h-60"
          >
            <div class="mt-5 sm:mt-0 w-auto md:w-[fit-content]">
              <ScrollContainer
                v-if="studioForm?.photos.length > 1"
                justify-content="start"
                class="rounded-[10px] h-full"
                theme="default"
                main-color="#171717"
              >
                <div
                  v-for="(photo, index) in studioForm?.photos.slice(1, 3)"
                  draggable="true"
                  @dragstart="
                    handleDragStart($event, findRealIndexByUrl(photo.url))
                  "
                  @dragover.prevent
                  @dragenter="
                    handleDragEnter($event, findRealIndexByUrl(photo.url))
                  "
                  @dragend="handleDragEnd"
                  class="drag-item max-h-30 w-[250px] bg-white shadow rounded-[10px] scrollElement no-margin"
                >
                  <img
                    :src="photo.url"
                    @click.stop="
                      () =>
                        openGallery(
                          displayedPhotos,
                          findRealIndexByUrl(photo.url),
                        )
                    "
                    alt="cover photo"
                    class="w-full h-full object-cover rounded-[10px]"
                  />
                </div>
              </ScrollContainer>
            </div>
            <div
              :class="{
                'w-[fit-content]':
                  studioForm?.photos.slice(3, studioForm?.photos.length)
                    .length == 1,
                hidden:
                  studioForm?.photos.slice(3, studioForm?.photos.length)
                    .length == 0,
              }"
              class="mt-5 sm:mt-0"
            >
              <ScrollContainer
                v-if="studioForm?.photos.length > 1"
                justify-content="start"
                class="rounded-[10px] h-full"
                theme="default"
                main-color="#171717"
              >
                <div
                  v-for="(photo, index) in studioForm?.photos.slice(
                    3,
                    studioForm?.photos.length,
                  )"
                  draggable="true"
                  @dragstart="
                    handleDragStart($event, findRealIndexByUrl(photo.url))
                  "
                  @dragover.prevent
                  @dragenter="
                    handleDragEnter($event, findRealIndexByUrl(photo.url))
                  "
                  @dragend="handleDragEnd"
                  class="drag-item max-h-30 w-[250px] bg-white shadow rounded-[10px] scrollElement no-margin"
                >
                  <img
                    :src="photo.url"
                    @click.stop="
                      () =>
                        openGallery(
                          displayedPhotos,
                          findRealIndexByUrl(photo.url),
                        )
                    "
                    alt="cover photo"
                    class="w-full h-full object-cover rounded-[10px]"
                  />
                </div>
              </ScrollContainer>
            </div>
          </div>
          <input
            ref="fileInputRef"
            type="file"
            multiple
            accept="image/png, image/jpeg"
            @change="onFileChange"
            style="display: none"
          />
          <div
            @dragover.prevent="onDragOver"
            @dragleave="onDragLeave"
            @drop.prevent="onDrop"
            class="add-photo"
          >
            <AddStudioButton
              :border-opacity="isDragOver ? '100' : '20'"
              title="Add Photo"
              @click="openFileDialog"
            />
          </div>
        </div>
      </div>
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <div class="w-full flex flex-col gap-5">
          <div class="name w-full flex-col flex gap-1.5">
            <div
              class="text-white text-sm font-normal tracking-wide opacity-20"
            >
              Address
            </div>
            <FInputClassic
              :wide="true"
              :disabled="true"
              placeholder="Address"
              v-model="studio.street"
            />
          </div>
          <div class="slug w-full flex-col flex gap-1.5">
            <div
              class="text-white text-sm font-normal tracking-wide opacity-20"
            >
              Short name for url
            </div>
            <FInputClassic
              :wide="true"
              @blur="updateSlug"
              :placeholder="studio.slug"
              v-model="studioForm.slug"
            >
              <template #icon>
                <div
                  class="text-white text-xl font-normal tracking-wide opacity-20"
                >
                  @
                </div>
              </template>
            </FInputClassic>
          </div>
          <div class="price w-full flex-col flex gap-1.5">
            <PriceChoose v-model="studioForm.price" />
          </div>
        </div>
        <div class="w-full flex-col flex gap-1.5">
          <div class="equipment w-full flex-col flex gap-1.5">
            <EquipmentChoose v-model="studioForm.equipment" />
          </div>
          <div class="badgees w-full flex-col flex gap-1.5">
            <BadgesChoose v-model="studioForm.badges" />
          </div>
        </div>
        <div class="w-full">
          <div class="hours w-full flex-col flex gap-1.5">
            <HoursChoose
              @update-studios="emit('update-studios')"
              v-model="studioForm.hours"
            />
          </div>
        </div>
      </div>
    </template>
  </Popup>
</template>

<style scoped lang="scss">
.drag-item {
  cursor: grab;
}

.drag-item:active {
  cursor: grabbing;
  opacity: 0.5;
}

.drag-item:active {
  cursor: grabbing;
}

.custom-drag-image {
  opacity: 0.8;
  border-style: dashed;
  border-width: 2px;
  animation: float 1.5s infinite ease-in-out,
    border-rolling 1.5s infinite linear;
}

@keyframes float {
  0% {
    scale: 1;
  }
  50% {
    scale: 1.1;
  }
  100% {
    scale: 1;
  }
}

@keyframes border-rolling {
  0% {
    border-color: #ccc;
  }
  25% {
    border-color: #f00;
  }
  50% {
    border-color: #0f0;
  }
  75% {
    border-color: #00f;
  }
  100% {
    border-color: #ccc;
  }
}
</style>
