<script setup lang="ts">
import { useHead } from '@unhead/vue';
import { ref } from 'vue';

// Meta tags for the page
useHead({
  title: 'Funny How – Payment Success',
  meta: [
    { name: 'description', content: 'Processing Payment' }
  ],
});


</script>
<template>
  <div class="container">
    все заебись, ты оплатил, ты на странице управления бронированием
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
