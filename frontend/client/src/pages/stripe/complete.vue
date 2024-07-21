<template>
  <div class="complete-container">
    <h1>Stripe Account Onboarding Complete</h1>
    <p>Your account has been successfully created and verified. You can now start using Stripe services.</p>
    <nuxt-link to="/">Go to Dashboard</nuxt-link>
  </div>
</template>

<script>
export default {
  mounted() {
    // Add any additional logic needed after onboarding completion
  }
}
</script>

<style scoped>
.complete-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 100vh;
  text-align: center;
}

.complete-container h1 {
  margin-bottom: 20px;
}

.complete-container p {
  margin-bottom: 40px;
}

.complete-container a {
  color: #007bff;
  text-decoration: none;
}
</style>