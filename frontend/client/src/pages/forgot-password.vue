<template>
  <div class="container">
    <div class="flex flex-col gap-10">
      <div class="text-center">
        <h1 class="text-5xl font-bold">Forgot Password</h1>
        <p class="text-xl">Enter your email to reset your password</p>
      </div>
      <form @submit.prevent="forgotPassword">
        <div class="flex flex-col gap-2.5">
          <input
              type="email"
              v-model="email"
              placeholder="Your email"
              required
              class="input-transparent"
          />
          <button
              type="submit"
              class="button-transparent"
          >
            Send Reset Link
          </button>
        </div>
      </form>
      <div class="flex justify-center items-center gap-2.5">
        <button @click="navigateTo('/login')" class="w-full h-11 p-3.5 hover:opacity-90 rounded-[10px] text-white text-sm font-medium tracking-wide">
          Back to Login
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';

const email = ref('');
const router = useRouter();

const forgotPassword = async () => {
  try {
    const response = await axios.post('/api/v1/auth/forgot-password', { email: email.value });
    alert('Password reset link sent! Check your email.');
    router.push('/login');
  } catch (error) {
    alert('Failed to send reset link. Please try again., скорее всего не тпользователя, look at console, надо обработать как-то клево');
  }
};
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