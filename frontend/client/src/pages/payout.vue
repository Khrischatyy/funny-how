<template>
  <div>
    <NuxtLayout
      @toggleSideMenu="toggleSideMenu"
      title="Payouts"
      class="text-white flex flex-col min-h-screen"
      name="dashboard"
    >
      <Spinner :is-loading="isLoading" class="mt-4" />
      <div class="container mx-auto p-4">
        <div
          v-if="
            stripeAccountId &&
            stripeAccountData &&
            Object.keys(stripeAccountData).length > 0
          "
          class="flex flex-col justify-start items-start border border-white border-opacity-10 rounded-[10px] p-4"
        >
          <div class="text-xl">
            <p class="text-gray-400 mb-1.5">Balance:</p>
            <p class="text-sm mb-1.5">
              Total balance: ${{ balance.instant_available[0]?.amount / 100 }}
            </p>
            <p v-if="balance?.pending?.length > 0" class="text-sm mb-1.5">
              Future payouts: ${{ balance.pending[0]?.amount / 100 }}
            </p>
            <p v-if="balance?.available?.length > 0" class="text-sm mb-1.5">
              In transit to bank: ${{ balance.available[0]?.amount / 100 }}
            </p>
            <p
              v-if="balance?.refund_and_dispute_prefunding?.length > 0"
              class="text-sm mb-1.5"
            >
              Refund and Dispute Prefunding:
              {{ balance.refund_and_dispute_prefunding[0]?.amount / 100 }}
            </p>
            <a
              v-if="stripeAccountId"
              target="_blank"
              :href="`https://connect.stripe.com/app/express#${stripeAccountId}/overview`"
              class="border-white border mt-5 mb-5 block text-white text-sm py-2 px-4 rounded-[10px] hover:opacity-80"
            >
              Go to Stripe Dashboard
            </a>
          </div>
          <div class="text-xl">
            <p class="text-gray-400 mt-2 mb-2">
              Payouts
              {{ stripeAccountData?.payouts_enabled ? "Enabled" : "Disabled" }}
            </p>
            <div
              v-if="stripeAccountData?.external_accounts?.total_count > 0"
              class="text-white text-sm font-bold mb-2 flex justify-center items-center"
            >
              <IconCredit class="w-6 h-6 mr-2" />
              {{ stripeAccountData?.external_accounts?.data[0]?.bank_name }}
              {{ stripeAccountData?.external_accounts?.data[0]?.last4 }}
            </div>
            <p v-if="stripeAccountData?.payouts_enabled" class="text-sm">
              Payout interval:
              {{ stripeAccountData?.settings?.payouts?.schedule?.interval }}
            </p>
            <button
              v-if="stripeAccountData?.payouts_enabled"
              @click="createAccountLink"
              class="border-white border mt-5 mb-5 text-white text-sm py-2 px-4 rounded-[10px] hover:opacity-80"
            >
              Update Information
            </button>
          </div>
        </div>
        <div
          v-if="
            stripeAccountId &&
            stripeAccountData &&
            Object.keys(stripeAccountData).length > 0 &&
            (!stripeAccountData.details_submitted ||
              !stripeAccountData.payouts_enabled)
          "
          class="flex mt-5 justify-between items-center border border-white border-opacity-10 rounded-[10px] p-4"
        >
          <div class="text-xl">
            <p class="text-red-400 mb-5">Requires attention</p>
            <p class="text-white font-bold">
              Your account is missing some information to make payouts.
            </p>
          </div>
          <button
            @click="createAccountLink"
            class="bg-white text-gray-800 font-bold py-2 px-4 rounded hover:bg-gray-400"
          >
            Update information
          </button>
        </div>
        <div
          v-if="user && !user.stripe_account_id && !user.payment_gateway"
          class="text-center mt-4 text-gray-400"
        >
          <p class="mb-5">
            Please connect your Stripe or Square account to manage payouts.
          </p>
          <button
              @click="createAccount"
              class="bg-[rgb(103,114,229)] text-gray-800 font-bold py-2 px-4 rounded-md hover:bg-gray-400 flex items-center space-x-2"
          >
  <span class="flex items-center space-x-2">
    <span>Create</span>
    <svg viewBox="0 0 60 25" xmlns="http://www.w3.org/2000/svg" width="60" height="25" class="UserLogo variant--">
      <title>Stripe logo</title>
      <path fill="var(--userLogoColor, #0A2540)" d="M59.64 14.28h-8.06c.19 1.93 1.6 2.55 3.2 2.55 1.64 0 2.96-.37 4.05-.95v3.32a8.33 8.33 0 0 1-4.56 1.1c-4.01 0-6.83-2.5-6.83-7.48 0-4.19 2.39-7.52 6.3-7.52 3.92 0 5.96 3.28 5.96 7.5 0 .4-.04 1.26-.06 1.48zm-5.92-5.62c-1.03 0-2.17.73-2.17 2.58h4.25c0-1.85-1.07-2.58-2.08-2.58zM40.95 20.3c-1.44 0-2.32-.6-2.9-1.04l-.02 4.63-4.12.87V5.57h3.76l.08 1.02a4.7 4.7 0 0 1 3.23-1.29c2.9 0 5.62 2.6 5.62 7.4 0 5.23-2.7 7.6-5.65 7.6zM40 8.95c-.95 0-1.54.34-1.97.81l.02 6.12c.4.44.98.78 1.95.78 1.52 0 2.54-1.65 2.54-3.87 0-2.15-1.04-3.84-2.54-3.84zM28.24 5.57h4.13v14.44h-4.13V5.57zm0-4.7L32.37 0v3.36l-4.13.88V.88zm-4.32 9.35v9.79H19.8V5.57h3.7l.12 1.22c1-1.77 3.07-1.41 3.62-1.22v3.79c-.52-.17-2.29-.43-3.32.86zm-8.55 4.72c0 2.43 2.6 1.68 3.12 1.46v3.36c-.55.3-1.54.54-2.89.54a4.15 4.15 0 0 1-4.27-4.24l.01-13.17 4.02-.86v3.54h3.14V9.1h-3.13v5.85zm-4.91.7c0 2.97-2.31 4.66-5.73 4.66a11.2 11.2 0 0 1-4.46-.93v-3.93c1.38.75 3.1 1.31 4.46 1.31.92 0 1.53-.24 1.53-1C6.26 13.77 0 14.51 0 9.95 0 7.04 2.28 5.3 5.62 5.3c1.36 0 2.72.2 4.09.75v3.88a9.23 9.23 0 0 0-4.1-1.06c-.86 0-1.44.25-1.44.9 0 1.85 6.29.97 6.29 5.88z" fill-rule="evenodd"></path>
    </svg>
  </span>
            <span>Account</span>
          </button>
          <div class="text-white font-['BebasNeue'] my-5">Or</div>
          <button
            @click="connectSquareAccount"
            class="bg-white text-gray-800 font-bold py-2 px-4 rounded hover:bg-gray-400"
          >
            Connect Square Account
          </button>
        </div>

        <div v-if="!isLoading && user.payment_gateway" class="mt-4">
          <p class="text-gray-400">Connected account</p>
          <p class="text-white>">{{ user.payment_gateway }}</p>
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
                <td class="py-2 px-4 border-b">
                  {{ new Date(payout.created_at).toLocaleString() }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </NuxtLayout>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, type Ref } from "vue"
import { useFetch, useRuntimeConfig } from "#app"
import { IconCredit, Spinner } from "~/src/shared/ui/common"
import { useApi } from "../lib/api"
import { useSessionStore } from "../entities/Session"
import { storeToRefs } from "pinia"

type StripAccountDataType = {
  id: string
  object: string
  business_profile: {
    annual_revenue: null
    estimated_worker_count: null
    mcc: string
    name: string
    support_address: null
    support_email: null
    support_phone: null
    support_url: null
    url: string
  }
  capabilities: {
    card_payments: string
    transfers: string
  }
  charges_enabled: boolean
  controller: {
    fees: {
      payer: string
    }
    is_controller: boolean
    losses: {
      payments: string
    }
    requirement_collection: string
    stripe_dashboard: {
      type: string
    }
    type: string
  }
  country: string
  created: number
  default_currency: string
  details_submitted: boolean
  email: string
  external_accounts: {
    object: string
    data: [
      {
        id: string
        object: string
        account: string
        account_holder_name: null
        account_holder_type: null
        account_type: null
        available_payout_methods: string[]
        bank_name: string
        country: string
        currency: string
        default_for_currency: boolean
        financial_account: null
        fingerprint: string
        future_requirements: {
          currently_due: string[]
          errors: string[]
          past_due: string[]
          pending_verification: string[]
        }
        last4: string
        metadata: string[]
        requirements: {
          currently_due: string[]
          errors: string[]
          past_due: string[]
          pending_verification: string[]
        }
        routing_number: string
        status: string
      },
    ]
    has_more: boolean
    total_count: number
    url: string
  }
  future_requirements: {
    alternatives: string[]
    current_deadline: null
    currently_due: string[]
    disabled_reason: null
    errors: string[]
    eventually_due: string[]
    past_due: string[]
    pending_verification: string[]
  }
  login_links: {
    object: string
    data: string[]
    has_more: boolean
    total_count: number
    url: string
  }
  metadata: string[]
  payouts_enabled: boolean
}

type SourceTypes = {
  card: number
}

type BalanceDetail = {
  amount: number
  currency: string
  source_types?: SourceTypes
}

type Balance = {
  object: string
  available: BalanceDetail[]
  instant_available: BalanceDetail[]
  livemode: boolean
  pending: BalanceDetail[]
  refund_and_dispute_prefunding: BalanceDetail[]
}

type BalanceResponse = {
  data: Balance
}

const sideMenuRef = ref()
const isLoading = ref(true)
const balance: Ref<Balance> = ref({
  object: "",
  available: [],
  instant_available: [],
  livemode: false,
  pending: [],
  refund_and_dispute_prefunding: [],
} as Balance)
const payouts = ref([])
const stripeAccountId = ref(null)
const stripeAccountData: Ref<StripAccountDataType> = ref(
  {} as StripAccountDataType,
)
const session = useSessionStore()
const { user } = storeToRefs(session)

const fetchBalance = async () => {
  try {
    const { fetch: fetchBalance } = useApi<BalanceResponse>({
      url: "/user/stripe/account/balance",
      auth: true,
    })

    const response = (await fetchBalance()) || ({ data: {} } as BalanceResponse)

    balance.value = response.data
  } catch (error) {
    console.error("Failed to fetch balance:", error)
  }
}

const connectSquareAccount = async () => {
  isLoading.value = true
  try {
    const { fetch: getLink } = useApi({
      url: "/user/payment/square/redirect",
      auth: true,
    })

    await getLink().then((res) => {
      console.log(res)
      if (res?.url) {
        window.location.href = res?.url
      }
    })
  } finally {
    isLoading.value = false
  }

  // const config = useRuntimeConfig()
  // let redirectUriBase = config.public.baseUrlClient
  // // let redirectUriBase = "http://127.0.0.1"
  // window.location.href = `https://connect.squareupsandbox.com/oauth2/authorize?client_id=sandbox-sq0idb-ZZhreupTqmxTkawXNXA1Yw&redirect_uri=${redirectUriBase}%2Fauth%2Fsquare&scope=MERCHANT_PROFILE_READ%20ORDERS_WRITE%20ORDERS_READ%20PAYMENTS_WRITE%20PAYMENTS_WRITE_ADDITIONAL_RECIPIENTS`
}

const createAccount = async () => {
  isLoading.value = true
  try {
    const { post } = useApi({
      url: "/user/stripe/create-account",
      auth: true,
    })

    const response = await post({})

    stripeAccountId.value = response.data.account_id
    isLoading.value = false
    createAccountLink()
  } catch (error) {
    console.error("Failed to create Stripe account:", error)
  }
}

const createAccountLink = async () => {
  try {
    isLoading.value = true
    const { fetch: getLink } = useApi({
      url: "/user/stripe/account/link?account_id=" + stripeAccountId.value,
      auth: true,
    })

    await getLink().then((res) => {
      if (res.data.url) {
        window.location.href = res.data.url
      }
    })
  } catch (error) {
    console.error("Failed to create account link:", error)
    isLoading.value = false
  }
}

const fetchPayouts = async () => {
  // Implement fetchPayouts logic if needed
}

const fetchStripeAccountData = async () => {
  try {
    isLoading.value = true
    const { fetch: getAccountData } = useApi({
      url: "/user/stripe/account/retrieve",
      auth: true,
    })

    getAccountData().then((res) => {
      stripeAccountData.value = res.data
      isLoading.value = false
    })
  } catch (error) {
    console.error("Failed to fetch Stripe account ID:", error)
    isLoading.value = false
  }
}

const toggleSideMenu = () => {
  if (sideMenuRef.value) {
    sideMenuRef.value.toggleMenu()
  }
}

onMounted(async () => {
  if (!user.value) {
    await session.fetchUserInfo()
  }
  isLoading.value = false
  if (user.value && user.value.stripe_account_id) {
    stripeAccountId.value = user.value.stripe_account_id
    await fetchStripeAccountData()
    await fetchBalance()
  }
})
</script>
