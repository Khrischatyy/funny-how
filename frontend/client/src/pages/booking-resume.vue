<script setup lang="ts">
import { useHead } from "@unhead/vue"
import { definePageMeta, useRuntimeConfig } from "#imports"
import { ref, onMounted, computed } from "vue"
import { useApi } from "~/src/lib/api"
import { navigateTo, useRoute } from "nuxt/app"
import { Spinner } from "~/src/shared/ui/common"

// Meta tags for the page
useHead({
  title: "Funny How – Payment Success",
  meta: [{ name: "description", content: "Processing Payment" }],
})
definePageMeta({
  layout: "error",
})

const route = useRoute()
const isLoading = ref(false)
const errorMessage = ref("")
const sessionId = computed(() => route.query.session_id)
const bookingId = computed(() => route.query.booking_id)

const bookingData = ref<any>({})
const bookingError = ref("")
function book(data) {
  isLoading.value = true
  const { post: bookTime } = useApi({
    url: `/address/reservation`,
    auth: true,
  })

  // Remove booking data from localStorage
  localStorage.removeItem("bookingData")

  bookTime(data)
    .then((response) => {
      isLoading.value = false
      if (response.data?.payment_url) {
        window.location.href = response.data?.payment_url
      }
    })
    .catch((error) => {
      console.log("Error:", error)
      bookingError.value = error.errors.error
      isLoading.value = false
    })
}

onMounted(async () => {
  const storedBookingData = localStorage.getItem("bookingData")
  if (!storedBookingData) {
    navigateTo("/studios")
    return
  }
  bookingData.value = JSON.parse(storedBookingData || "{}")
  book(bookingData.value)
})
</script>

<template>
  <div>
    <Spinner :is-loading="isLoading" />
    <div class="error flex flex-col gap-10">
      <div class="text-center">
        <p class="text-xl">Processing booking information...</p>
        <p class="text-sm">Please wait a moment</p>
      </div>
    </div>
  </div>
</template>

<style scoped lang="scss">
.container {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  background-color: #000;
  color: #fff;
  text-align: center;
}
</style>
