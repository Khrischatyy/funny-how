<template>
  <div>
    <NuxtLayout
        @toggleSideMenu="toggleSideMenu"
        title="Payouts"
        class="text-white flex flex-col min-h-screen"
        name="dashboard"
    >
      <div class="container mx-auto p-4">
        <h2 class="text-2xl mb-4">Manage Payouts</h2>
        <div v-if="stripeAccountId" class="flex justify-between items-center bg-gray-800 p-4 rounded-lg shadow-lg">
          <div class="text-xl">
            <p class="text-gray-400">Available Balance:</p>
            <p class="text-white font-bold">${{ (balance / 100).toFixed(2) }}</p>
          </div>
          <button @click="createPayout" class="bg-white text-gray-800 font-bold py-2 px-4 rounded hover:bg-gray-400">
            Create Payout
          </button>
        </div>
        <div v-else class="text-center mt-4 text-gray-400">
          <p>Please connect your Stripe account to manage payouts.</p>
          <button @click="createAccount" class="bg-white text-gray-800 font-bold py-2 px-4 rounded hover:bg-gray-400">
            Connect Stripe Account
          </button>
        </div>

        <div v-if="isLoading" class="flex justify-center items-center mt-4">
          <div class="spinner"></div>
        </div>

        <div v-if="!isLoading && payouts.length === 0" class="text-center mt-4 text-gray-400">
          No payouts available.
        </div>

        <div v-if="!isLoading && payouts.length > 0" class="mt-4">
          <table class="min-w-full bg-white">
            <thead>
            <tr>
              <th class="py-2 px-4 border-b">Payout ID</th>
              <th class="py-2 px-4 border-b">Amount</th>
              <th class="py-2 px-4 border-b">Status</th>
              <th class="py-2 px-4 border-b">Created At</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="payout in payouts" :key="payout.id">
              <td class="py-2 px-4 border-b">{{ payout.id }}</td>
              <td class="py-2 px-4 border-b">{{ payout.amount / 100 }}</td>
              <td class="py-2 px-4 border-b">{{ payout.status }}</td>
              <td class="py-2 px-4 border-b">{{ new Date(payout.created_at).toLocaleString() }}</td>
            </tr>
            </tbody>
          </table>
        </div>
      </div>
    </NuxtLayout>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useFetch } from '#app';

const sideMenuRef = ref();
const isLoading = ref(true);
const balance = ref(null);
const payouts = ref([]);
const stripeAccountId = ref(null);

const fetchBalance = async () => {
  try {
    const { data } = await useFetch('/api/v1/user/available-balance');
    balance.value = data.balance;
  } catch (error) {
    console.error('Failed to fetch balance:', error);
  }
};

const createPayout = async () => {
  try {
    isLoading.value = true;
    const { data } = await useFetch('/api/v1/user/create-payout', { method: 'POST' });
    fetchBalance();
    fetchPayouts();
  } catch (error) {
    console.error('Failed to create payout:', error);
  } finally {
    isLoading.value = false;
  }
};

const createAccount = async () => {
  try {
    const { data } = await useFetch('/api/v1/stripe/create-account', { method: 'POST' });
    stripeAccountId.value = data.account_id;
    createAccountLink();
  } catch (error) {
    console.error('Failed to create Stripe account:', error);
  }
};

const createAccountLink = async () => {
  try {
    const { data } = await useFetch('/api/v1/stripe/account/link', { method: 'GET' });
    if (data.error && data.error.includes('https://dashboard.stripe.com/connect/accounts/overview')) {
      window.location.href = data.error; // Redirect user to Stripe Dashboard
    } else {
      window.location.href = data.url; // Redirect user to Stripe Account Onboarding
    }
  } catch (error) {
    console.error('Failed to create account link:', error);
  }
};

const fetchPayouts = async () => {
  // Implement fetchPayouts logic if needed
};

const fetchStripeAccountId = async () => {
  try {
    const { data } = await useFetch('/api/v1/user/me');
    stripeAccountId.value = data.user.stripe_account_id;
  } catch (error) {
    console.error('Failed to fetch Stripe account ID:', error);
  }
};

const toggleSideMenu = () => {
  if (sideMenuRef.value) {
    sideMenuRef.value.toggleMenu();
  }
};

onMounted(() => {
  fetchBalance();
  fetchStripeAccountId();
  // fetchPayouts(); Uncomment if fetchPayouts logic is implemented
});
</script>