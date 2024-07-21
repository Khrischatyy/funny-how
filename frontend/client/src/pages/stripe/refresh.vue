<template>
  <div class="refresh-container">
    <h1>Refresh Stripe Account</h1>
    <p>Please update your information by clicking the button below.</p>
    <button @click="refreshAccountLink">Refresh Account Link</button>
    <p v-if="loading">Creating new account link...</p>
    <p v-if="error">{{ error }}</p>
  </div>
</template>

<script>
export default {
  data() {
    return {
      loading: false,
      error: null,
    };
  },
  methods: {
    async refreshAccountLink() {
      this.loading = true;
      this.error = null;
      try {
        const response = await this.$axios.$get('/api/v1/user/stripe/account/link');
        window.location.href = response.url; // Redirect to the new account link URL
      } catch (e) {
        this.error = e.response ? e.response.data.error : 'An error occurred';
      } finally {
        this.loading = false;
      }
    }
  }
}
</script>

<style scoped>
.refresh-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 100vh;
  text-align: center;
}

.refresh-container h1 {
  margin-bottom: 20px;
}

.refresh-container p {
  margin-bottom: 20px;
}

.refresh-container button {
  padding: 10px 20px;
  font-size: 16px;
  cursor: pointer;
}

.refresh-container button:disabled {
  cursor: not-allowed;
}
</style>