<script setup lang="ts">
import defaultLogo from "~/src/shared/assets/image/studio.png"
import {Popup} from "~/src/shared/ui/components"
import {getStatus, getColor, getRatingColor} from "~/src/shared/utils"
import {computed, onMounted, onUnmounted, reactive, ref} from "vue"
import {FInput, FInputClassic, FSelect, FSelectClassic, IconLike} from "~/src/shared/ui/common"
import IconStar from "~/src/shared/ui/common/Icon/IconStar.vue"
import IconAddress from "~/src/shared/ui/common/Icon/IconAddress.vue"
import {useApi} from "~/src/lib/api"
import {Spinner} from "~/src/shared/ui/common/Spinner"

const props = withDefaults(
    defineProps<{
      showPopup: boolean
    }>(),
    {
      showPopup: false,
    },
)
const isLoading = ref(false)

const engineer = reactive({
  address: "",
  name: "",
  role: "1",
  email: "",
})

const roles = [
  {
    id: 1,
    name: "Engineer",
    label: "Engineer",
  },
]
const addresses = [{
  id: 1,
  name: "123 Main St, New York, NY 10001",
  label: "123 Main St, New York, NY 10001",
}, {
  id: 2,
  name: "456 Main St, New York, NY 10001",
  label: "456 Main St, New York, NY 10001",
}, {
  id: 3,
  name: "789 Main St, New York, NY 10001",
  label: "789 Main St, New York, NY 10001",
}
]

const emit = defineEmits<{
  (e: "togglePopup"): void
  (e: "closePopup"): void
  (e: "onCreateEngineer"): void
}>()

const closePopup = () => {
  emit("closePopup")
}
const createEngineer = async () => {
  isLoading.value = true
  // const { post: cancelBooking } = useApi({
  //   url: "/room/cancel-booking",
  //   auth: true,
  // })
  //
  // await cancelBooking({
  //   booking_id: props.booking?.id,
  // }).then((response) => {
  //   isLoading.value = false
  //   emit("onCancelBooking", response.data)
  //   closePopup()
  // })
  emit("onCreateEngineer")
  closePopup()
}

const getFirstPhoto = computed(() => {
  if (!props.booking.room.photos || !props.booking.room.photos.length) {
    return ""
  }
  return props.booking.room.photos[0].url
})
</script>

<template>
  <Popup
      :scroll-to-close="true"
      type="small"
      :title="'Manage Booking'"
      :open="showPopup"
      @close="closePopup"
  >
    <template #header>
      <div class="flex justify-start items-center gap-5">
        <div>
          <h3 class="text-xl font-bold text-white">
            Create Engineer
          </h3>
        </div>
      </div>
    </template>
    <template #body>
      <div class="flex flex-col gap-7 justify-between items-center relative">
        <Spinner :is-loading="isLoading"/>
        <div class="w-full relative flex justify-start gap-10 items-center">
          <div class="flex w-full justify-start gap-2">
            <div class="relative flex flex-col w-full gap-0.5">
              <FSelectClassic
                  :wide="true"
                  label="Address"
                  size="lg"
                  v-model="engineer.address"
                  :options="addresses"
                  placeholder="Select address"
                  class="w-full"/>
            </div>
          </div>
        </div>
        <div class="w-full relative flex justify-start gap-10 items-center">
          <div class="flex w-full justify-start gap-2">
            <div class="relative w-full flex flex-col gap-0.5">
              <FInputClassic label="Name" :wide="true" v-model="engineer.name" placeholder="Enter name" class="w-full"/>
            </div>
          </div>
        </div>
        <div class="w-full relative flex justify-start gap-10 items-center">
          <div class="flex w-full justify-start gap-2">
            <div class="relative w-full flex flex-col gap-0.5">
              <FInputClassic label="E-mail" :wide="true" v-model="engineer.email" placeholder="Enter e-mail" class="w-full"/>
            </div>
          </div>
        </div>
        <div class="w-full relative flex justify-start gap-10 items-center">
          <div class="flex w-full justify-start gap-2">
            <div class="relative flex flex-col w-full gap-0.5">
              <FSelectClassic
                  :wide="true"
                  label="Role"
                  size="lg"
                  v-model="engineer.role"
                  :options="roles"
                  placeholder="Select role"
                  class="w-full"/>
            </div>
          </div>
        </div>
      </div>
    </template>
    <template #footer>
      <div class="flex justify-between items-center gap-2 w-full">
        <button
            @click="createEngineer"
            class="w-full h-11 p-3.5 hover:opacity-90 bg-white rounded-[10px] text-black text-sm font-medium tracking-wide"
        >
          Add Engineer
        </button>
      </div>
    </template>
  </Popup>
</template>

<style scoped lang="scss"></style>
