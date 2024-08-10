<template>
  <div>
    <NuxtLayout
      title="Team"
      class="text-white flex flex-col min-h-screen"
      name="dashboard"
    >
      <template #action>
        <button @click="createEngineerPopup" class="subhead__actions outline-none hover:opacity-70 flex justify-center items-center rounded-[10px] border border-dashed w-auto px-10">
          Add Engineer
        </button>
      </template>
      <div class="container mx-auto px-2 md:px-4">
        <div class="grid grid-cols-1 gap-6">
          <TeammateRow
            class="border border-white border-opacity-30"
            v-for="teammate in teammates"
            @on-dismiss-teammate="handleDismissTeammate"
            :key="teammate.id"
            :teammate="teammate"
          />
        </div>
        <AddEngineerModal
          v-if="showPopup"
          :addresses="addressesForPopup"
          @on-create-engineer="handleCreateEngineer"
          :showPopup="showPopup"
          @closePopup="closePopup"/>
      </div>
    </NuxtLayout>
  </div>
</template>

<style scoped>
.spinner {
  border: 4px solid rgba(255, 255, 255, 0.2);
  border-left-color: #ffffff;
  border-radius: 50%;
  width: 40px;
  height: 40px;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}
</style>

<script setup lang="ts">
import { FilterBar } from "~/src/shared/ui/components"
import { computed, onMounted, ref } from "vue"
import { useApi } from "~/src/lib/api"
import ClientRow from "~/src/entities/User/ui/ClientRow.vue"
import { useSessionStore } from "~/src/entities/Session"
import TeammateRow from "~/src/entities/User/ui/TeammateRow.vue";
import {storeToRefs} from "#imports";
import AddEngineerModal from "~/src/widgets/Modals/AddEngineerModal.vue";
import {IconUser} from "~/src/shared/ui/common";
const showPopup = ref(false)

type Teammate = {
  id: number
  role: string
  username: string
  phone: string
  email: string
  booking_count: number
  address: string
  profile_photo: string
}

const teammates = ref<Teammate[]>([])
const addressesForPopup = ref<{ id: number; name: string; label: string; }[]>([]);
const currentPage = ref(1)
const lastPage = ref(1)
const isLoading = ref(false)
const session = useSessionStore()
const {brand} = storeToRefs(session)
const companySlug = brand
onMounted(() => {
  getTeammate()
  getAddresses()
})


const closePopup = () => {
  showPopup.value = false
}
const createEngineerPopup = () => {
  showPopup.value = true
}
const handleCreateEngineer = (teammate: Teammate) => {
  console.log('created engineer', teammate)
  teammates.value.push(teammate)
  showPopup.value = false
}

const handleDismissTeammate = (teammate: Teammate) => {
  console.log('dismissed', teammate)
  teammates.value = teammates.value.filter((t) => t.id !== teammate.id)
}

const getAddresses = () => {
  const {fetch: fetchAddresses} = useApi({
    url: `/addresses`,
  });

  addressesForPopup.value = [
    {
      id: 1,
      name: "123 Main St, New York, NY 10001",
      label: "123 Main St, New York, NY 10001",
    },
    {
      id: 2,
      name: "456 Main St, New York, NY 10001",
      label: "456 Main St, New York, NY 10001",
    },
    {
      id: 3,
      name: "789 Main St, New York, NY 10001",
      label: "789 Main St, New York, NY 10001",
    },
  ];
  // fetchAddresses().then((response) => {
  //   console.log(response);
  // });
};

const getTeammate = async (page = 1) => {
  isLoading.value = true
  const { post: fetchTeammate } = useApi({
    url: `/address/teammates`,
    auth: true,
  })

  teammates.value = [
    {
      id: 1,
      profile_photo: "https://ci3.googleusercontent.com/meips/ADKq_NZn5DMzFQ0KRAiIsaLGFh2pig3G3dmm6D9HucwY4tJjL0bO8mw-_Zg2NgPmw0isoBoVJlc9edBYSA=s0-d-e1-ft#https://funny-how.com/mail/logo.png",
      role: "Engineer",
      address: "123 Main St, New York, NY 10001",
      username: "Tony Soprano",
      phone: "+1234567890",
      email: "",
      booking_count: 5,
    }]
  // fetchClients({
  //
  // })
  //   .then((response) => {
  //     clients.value = response?.data
  //
  //     isLoading.value = false
  //   })
  //   .catch((error) => {
  //     console.error("Error fetching bookings:", error)
  //   })
}
</script>
