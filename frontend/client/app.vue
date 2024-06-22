<template>
  <div class="overflow-hidden">
    <NuxtLayout>
      <NuxtPage />
    </NuxtLayout>
  </div>
</template>

<script setup>
import {BrandingLogo, BrandingLogoSample, BrandingLogoSmall} from "~/src/shared/ui/branding";
import {useSessionStore} from "~/src/entities/Session";
const store = useSessionStore();
</script>

<style lang="scss">
@import "@/src/shared/assets/style";
@keyframes slideInFromLeft {
  0% {
    opacity: 0;
    transform: translateX(-100%);
  }
  100% {
    opacity: 1;
    transform: translateX(0);
  }
}
.page-enter-active,
.page-leave-active {
  transition: all 0.7s ease-in-out;
}
.page-enter-from{
  transform: translate(100%, 0);
}

.page-leave-to {
  transform: translate(0, 0);
}
</style>
