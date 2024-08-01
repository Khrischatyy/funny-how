<script setup lang="ts">
import { useSessionStore } from "~/src/entities/Session"
import { useRouter } from "vue-router"
import { useHead } from "@unhead/vue"
import { useApi } from "~/src/lib/api"
import type { ResponseDto } from "~/src/lib/api/types"
import { Particles } from "~/src/shared/ui/components"
import { navigateTo } from "nuxt/app"
import { storeToRefs } from "pinia"
import { ref } from "vue"
// Set the page metadata
useHead({
  title: "Funny How – Book a Session Time",
  meta: [{ name: "description", content: "Book A Studio Time | Auth" }],
})
const isLoading = ref(true)
const router = useRouter()
const route = router.currentRoute.value
const sessionStore = useSessionStore() // Retrieve session store
const { user } = storeToRefs(sessionStore)
const obtainToken = async (code: string) => {
  const setTokenApi = useApi({ url: "/payment/square/token", auth: true })
  console.log("code", code)

  try {
    const response = await setTokenApi.post({ code })
    if (response?.data) {
      console.log("response", response)
    }
  } catch (error) {
    console.error("Error fetching user info:", error)
  }
}
// Getting the code from url
const code = route.query.code as string | undefined
if (code) {
  obtainToken(code)
} else {
  router.push("/login")
}
</script>

<template>
  <div>
    <client-only>
      <Spinner :is-loading="isLoading" />
      <Particles :position="{ x: 0, y: 0, z: 0 }" />
    </client-only>
  </div>
</template>

<style scoped lang="scss"></style>
