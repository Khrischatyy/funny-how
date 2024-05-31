<script setup lang="ts">
import { useHead } from '@unhead/vue';
import { useRuntimeConfig } from '#imports';
import axios from 'axios';
import { ref, onMounted } from 'vue';

// Meta tags for the page
useHead({
  title: 'Funny How – Payment Success',
  meta: [
    { name: 'description', content: 'Processing Payment' }
  ],
});

const isLoading = ref(true);
const errorMessage = ref('');

onMounted(async () => {
  const config = useRuntimeConfig();
  const query = new URLSearchParams(window.location.search);
  const sessionId = query.get('session_id');
  const bookingId = query.get('booking_id');

  if (!sessionId || !bookingId) {
    errorMessage.value = 'Invalid request parameters.';
    isLoading.value = false;
    return;
  }

  try {
    const response = await axios.post(`${config.public.apiBase}/v1/address/payment-success`, {
      session_id: sessionId,
      booking_id: bookingId,
    });

    if (response.data.success) {
      window.location.href = `${config.public.frontendUrl}/booking-management`;
    } else {
      errorMessage.value = response.data.message;
    }
  } catch (error) {
    errorMessage.value = error.response.data.message || 'Payment verification failed.';
  } finally {
    isLoading.value = false;
  }
});
</script>

<template>
  <div class="container">
    <div v-if="isLoading" class="loading">
      <h1>Processing Payment...</h1>
    </div>
    <div v-else class="error" v-if="errorMessage">
      <h1>Error</h1>
      <p>{{ errorMessage }}</p>
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

.loading {
  font-size: 1.5em;
}

.error {
  color: red;
}
</style>
