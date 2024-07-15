<template>
  <div class="container">
    <Spinner :is-loading="isLoading" />
    <div class="flex flex-col gap-10">
      <div v-if="isLoading" class="text-center">
        <h1 class="text-5xl font-bold">Resetting Password...</h1>
      </div>
      <div v-else-if="errorMessage" class="flex flex-col gap-5">
        <div class="text-center">
          <h1 class="text-5xl font-bold">Error</h1>
          <p class="text-xl">{{ errorMessage }}</p>
        </div>
      </div>
      <div v-else class="flex flex-col gap-5">
        <div class="text-center">
          <h1 class="text-5xl font-bold">Reset Password</h1>
        </div>
        <form @submit.prevent="resetPassword">
          <div class="flex flex-col gap-2.5">
            <input
              type="password"
              v-model="password"
              placeholder="New Password"
              required
              class="input-transparent"
            />
            <input
              type="password"
              v-model="password_confirmation"
              placeholder="Confirm New Password"
              required
              class="input-transparent"
            />
            <button type="submit" class="button-transparent">
              Reset Password
            </button>
          </div>
        </form>
        <div class="flex justify-center items-center gap-2.5">
          <button
            @click="navigateTo('/forgot-password')"
            class="w-full h-11 p-3.5 hover:opacity-90 rounded-[10px] text-white text-sm font-medium tracking-wide"
          >
            Forgot password?
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { useHead } from "@unhead/vue"
import { definePageMeta, navigateTo } from "#imports"
import axios from "axios"
import { ref, computed } from "vue"
import { useRoute } from "nuxt/app"
import { Spinner } from "~/src/shared/ui/common"

// Meta tags for the page
useHead({
  title: "Funny How – Reset Password",
  meta: [{ name: "description", content: "Reset Password" }],
})
definePageMeta({
  layout: "error",
})

const route = useRoute()
const isLoading = ref(false)
const errorMessage = ref("")
const password = ref("")
const password_confirmation = ref("")

const token = computed(() => route.query.token)
const email = computed(() => route.query.email)

const resetPassword = async () => {
  isLoading.value = true
  errorMessage.value = ""

  if (!token.value || !email.value) {
    errorMessage.value = "Invalid request parameters."
    isLoading.value = false
    return
  }

  try {
    const response = await axios.post("/api/v1/auth/reset-password", {
      token: token.value,
      email: email.value,
      password: password.value,
      password_confirmation: password_confirmation.value,
    })
    //AUTHORIZE USER!
    if (response.status === 204) {
      navigateTo("/profile")
    } else {
      errorMessage.value = response.data.message || "Password reset failed."
    }
  } catch (error) {
    if (
      error.response &&
      error.response.data &&
      error.response.data.errors &&
      error.response.data.errors.password
    ) {
      errorMessage.value = error.response.data.errors.password[0]
    } else {
      errorMessage.value =
        error.response?.data?.message || "Password reset failed."
    }
  } finally {
    isLoading.value = false
  }
}
</script>

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

.input-transparent {
  background: transparent;
  border: 1px solid #fff;
  color: #fff;
  padding: 10px;
  margin: 5px 0;
}

.button-transparent {
  background: transparent;
  border: 1px solid #fff;
  color: #fff;
  padding: 10px;
  margin: 5px 0;
}
</style>
