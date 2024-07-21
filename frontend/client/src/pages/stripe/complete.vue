<template>
  <div class="w-full px-5">
    <Spinner :is-loading="isLoading" />
    <div class="flex flex-col gap-10 justify-center items-center">
      <div class="text-center flex flex-col gap-5">
        <h1 class="text-4xl font-bold">Payment updated</h1>
        <p class="text-xl">Redirecting to payout page</p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { onMounted, ref } from "vue"
import { Spinner } from "~/src/shared/ui/common"
import { definePageMeta } from "#imports"
const isLoading = ref(true)
// Meta tags for the page
definePageMeta({
  layout: "error",
})

onMounted(() => {
  setTimeout(() => {
    window.location.href = "/payout"
  }, 3000)
})
</script>

<style scoped lang="scss"></style>
