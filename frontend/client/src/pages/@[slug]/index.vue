<script setup lang="ts">
import {useHead} from "@unhead/vue";
import {definePageMeta, useRuntimeConfig} from '#imports'
import {useSessionStore} from "~/src/entities/Session";
import {computed, onMounted, ref} from "vue";
import {BrandingLogoSmall} from "~/src/shared/ui/branding";
import {useRoute} from "nuxt/app";
import {type StudioFormValues, useCreateStudioFormStore} from "~/src/entities/RegistrationForms";
import {IconDown, IconMic} from "~/src/shared/ui/common";
import GoogleMap from "~/src/widgets/GoogleMap.vue";
import { SelectPicker } from "~/src/features/DatePicker";
import { TimeSelect } from "~/src/widgets";
import {useNuxtApp} from "#app";

definePageMeta({
  middleware: ["auth"],
})
const route = useRoute();
const { $axios } = useNuxtApp();

useHead({
  title: 'Dashboard | '+ route.params.slug,
  meta: [
    { name: 'Funny How', content: 'Dashboard' }
  ],
})

const isLoading = ref(false)


const session = ref()
const brand = ref()

const dateInput = ref<HTMLInputElement | null>(null);
const end_time = ref<HTMLInputElement | null>(null);
const start_time = ref<HTMLInputElement | null>(null);

type StudioFormValues = {
  address_id: string,
  date: string,
  start_time: string,
  end_time: string,
}
const rentingForm = ref({
  address_id: '',
  date: '',
  anotherDate: '',
  start_time: '09:00',
  end_time: '19:00',
})
const today = new Date();
const tomorrow = new Date();
tomorrow.setDate(today.getDate() + 1);
const hoursAvailableStart = ref([]);
const hoursAvailableEnd = ref([]);
const rentingList = [{name: 'today', date: ''}, {name: 'tomorrow', date: ''}, {name: 'another-day', date: 'another-day'}];
rentingList[0].date = today.toISOString().split('T')[0];
rentingList[1].date = tomorrow.toISOString().split('T')[0];
onMounted(async () => {
  const config = useRuntimeConfig()
  getAddressId()
  session.value = useSessionStore()
  rentingForm.value.date = rentingList[0].date

  await getStartSlots()
})

async function getStartSlots() {
  const config = useRuntimeConfig()
  const start_slots = await $axios.get(`${config.public.apiBaseClient}/v1/address/reservation/start-time?address_id=1&date=${rentingForm.value.date}`);
  if (start_slots.data.success) {
    hoursAvailableStart.value = start_slots.data.data;
  }
}

async function getEndSlots(start_time: string) {
  const config = useRuntimeConfig()
  const end_slots = await $axios.get(`${config.public.apiBaseClient}/v1/address/reservation/end-time?address_id=1&date=${rentingForm.value.date}&start_time=${start_time}`);
  console.log('end_slots', end_slots.data.data)
  if (end_slots.data.success) {
    hoursAvailableEnd.value = end_slots.data.data;
  }
  console.log('availableHours', hoursAvailableEnd.value);

}

function processAvailableHoursStart() {
  const hoursSet = new Set();
  availableHours.value.forEach(slot => {
    const startHour = parseInt(slot.start_time.split(':')[0]);
    const endHour = parseInt(slot.end_time.split(':')[0]);

    for (let hour = startHour; hour <= endHour; hour++) {
      hoursSet.add(hour % 12 || 12); // Convert to 12-hour format
    }
    for (let hour = endHour; hour <= 12; hour++) {
      hoursSet.add(hour % 12 || 12); // Convert to 12-hour format
    }
  });
  return Array.from(hoursSet).map(hour => hour.toString().padStart(2, '0'));
}

export type reservationResponse = {
  address_id: number,
  start_time: string,
  end_time: string,
  total_cost: number,
  date: string,
  updated_at: string,
  created_at: string,
  id: number
}

const responseQuote = ref({})

function book(){
  const config = useRuntimeConfig()
  let requestConfig = {
    method: 'post',
    credentials: true,
    url: `${config.public.apiBaseClient}/v1/address/reservation`,
    headers: {
      'Accept': 'application/json',
      'Content-Type': 'application/json',
      'Authorization': 'Bearer ' + useSessionStore().accessToken
    },
    data: {
      address_id: brand.value.addresses[0].id,
      date: rentingForm.value.date,
      start_time: rentingForm.value.start_time,
      end_time: rentingForm.value.end_time,
    }
  };
  $axios.defaults.headers.common['X-Api-Client'] = `web`
  $axios.request(requestConfig)
      .then((response) => {
        //responseQuote
// "address_id": 2,
//     "start_time": "2024-05-20T08:18:00.000000Z",
//     "end_time": "2024-05-20T17:18:00.000000Z",
//     "user_id": 1,
//     "total_cost": 30,
//     "date": "2024-05-20",
//     "updated_at": "2024-05-20T08:18:49.000000Z",
//     "created_at": "2024-05-20T08:18:49.000000Z",
//     "id": 2
//
        responseQuote.value = response.data.data;

        session.value.setReservations(response.data.data?.booking)
        session.value.setPaymentSession(response.data.data?.payment_session)

        console.log('response', response.data.data)
      })
      .catch((error) => {
        console.log(error);
      });

}
function pay(url: string){
  window.open(url, '_blank', 'width=800,height=600,toolbar=0,location=0,menubar=0');
}

function getAddressId(){
  const config = useRuntimeConfig()

  let requestConfig = {
    method: 'get',
    credentials: true,
    url: `${config.public.apiBaseClient}/v1/company/${route.params.slug}`,
    headers: {
      'Accept': 'application/json',
      'Content-Type': 'multipart/form-data',
      'Authorization': 'Bearer ' + useSessionStore().accessToken
    }
  };
  $axios.defaults.headers.common['X-Api-Client'] = `web`
  $axios.request(requestConfig)
      .then((response) => {
        console.log('response', response.data.data)
        brand.value = response.data.data;
      })
      .catch((error) => {
        console.log(error);
      });
}
function getFormValues(): StudioFormValues {
  return useCreateStudioFormStore().inputValues;
}

function openDatePicker(input) {
  if(input == 'date')
    dateInput.value[0].showPicker();
  else if(input == 'start_time'){
    start_time.value[0].showPicker();
  }
  else if(input == 'end_time'){
    end_time.value[0].showPicker();
  }
}

function formatTime(time: string) {
  const date = new Date(time);
  return date.toLocaleTimeString('en-US', {hour: 'numeric', minute: 'numeric', hour12: true});
}

function formatDate(date: string) {
  const dateObj = new Date(date);
  return dateObj.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
}
type DateReponse = {
  date: string
}
function dateChanged(newDate: DateReponse, input: keyof StudioFormValues) {
  rentingForm.value[input] = newDate?.date;

}

function timeChanged(newDate: string, input: keyof StudioFormValues) {
  console.log('Time changed:', newDate, input);
  if(input == 'start_time'){
    rentingForm.value[input] = newDate;
    getEndSlots(newDate)
    return
  }

  rentingForm.value[input] = newDate;

}

function signOut() {
  session.value.logout()
}

</script>

<template>
  <div class="grid min-h-[100vh] h-full animate__animated animate__fadeInRight">
    <div class="w-full h-full flex-col justify-between items-start gap-7 inline-flex">
      <div class="absolute top-0 left-0 w-full p-5">
        <BrandingLogoSmall class="opacity-50" />
      </div>
      <div v-if="brand" class="relative w-full flex-col justify-start items-center gap-2.5 flex">

        <div class="w-96 justify-start gap-1.5 items-center inline-flex mb-10 text-center">

          <div class="text-white text-xl font-bold text-center tracking-wide">
            <img class="w-full w-100" :src="brand.logo_url" alt="">
          </div>
          <div class="text-white text-xxl font-bold text-center tracking-wide">
            {{brand.description}}
          </div>
          <div class="text-white text-xxl font-bold text-center tracking-wide">
            {{brand.name}}
          </div>
        </div>

        <div class="w-96 justify-between gap-1.5 items-center inline-flex mb-10 text-center">
          <h2 class="text-white text-xxl font-bold text-center tracking-wide">
           Addresses
          </h2>

        </div>
        <div v-for="address in brand.addresses" class="w-96 justify-between gap-1.5 items-center flex-col mb-10 text-center">
          <div class="text-white mb-10 text-xl font-light text-left tracking-wide">


            Street: {{address.street}}<br/>
          </div>
          <GoogleMap class="h-[300px]" :logo="brand.logo_url" :lat="address.latitude" :lng="address.longitude"/>
          <div class="w-96 justify-between gap-1.5 mt-10 items-center inline-flex mb-10 text-center">
            <h2 class="text-white text-xxl font-bold text-center tracking-wide">
              Badges
            </h2>

          </div>
          <div  class="w-96 justify-start gap-2.5 items-center inline-flex mb-10 text-center">
            <div v-for="badge in address.badges" class="text-white text-xl font-light text-left tracking-wide">
              <div  :class="'border-opacity-100'" class="w-full flex px-3 gap-2.5 justify-center items-center cursor-pointer h-11 outline-none rounded-[10px] focus:border-white border border-white bg-transparent text-white text-sm font-medium tracking-wide">
                <img :src="badge.image_url" />
                <span>{{badge.name}}</span>
              </div>
            </div>
          </div>
          <div class="w-96 justify-between gap-1.5 items-center inline-flex mb-10 text-center">
            <h2 class="text-white text-xxl font-bold text-center tracking-wide">
              Booking
            </h2>

          </div>
          <div class="relative w-full flex items-center">
            <div class="flex items-center w-full">
              <SelectPicker class="w-full" @dateSelected="dateChanged($event, 'date')" />
            </div>
          </div>

          <div class="relative hidden w-full flex items-center">
            <div class="flex items-center">
              <select v-model="rentingForm.date" class="w-full opacity-0 absolute top-0 px-3 h-11 outline-none rounded-[10px] focus:border-white border border-white border-opacity-20 bg-transparent text-white text-sm font-medium tracking-wide" name="workday">
                <option v-for="day in rentingList" :value="day.date">
                  {{day.name}}
                </option>
              </select>
            </div>
            <div class="relative w-full flex items-center pointer-events-none">
              <input disabled :value="rentingForm.date" placeholder="Day" class="w-full px-3 h-11 outline-none rounded-[10px] focus:border-white border border-neutral-700 border-opacity-100 bg-transparent text-white text-sm font-medium tracking-wide" name="workday"/>
              <span class="absolute right-5 text-neutral-700 cursor-pointer">Day</span>
              <span class="absolute right-0 cursor-pointer">
            <IconDown/>
          </span>
            </div>
          </div>

          <div v-if="rentingForm.date == 'another-day'" class="relative w-full flex items-center mt-3">
            <div class="flex items-center">
              <input v-model="rentingForm.anotherDate" name="date" type="date" ref="dateInput" class="w-full px-3 h-11 outline-none rounded-[10px] opacity-0 absolute focus:border-white border border-neutral-700 border-opacity-100 bg-transparent text-white text-sm font-medium tracking-wide"  />
            </div>
            <div @click="openDatePicker('date')" class="relative w-full flex items-center">
              <input :value="rentingForm.anotherDate" placeholder="Choose Another Day" class="pointer-events-none w-full px-3 h-11 outline-none rounded-[10px] focus:border-white border border-neutral-700 border-opacity-100 bg-transparent text-white text-sm font-medium tracking-wide" name="workday"/>
              <span class="absolute right-5 text-neutral-700 cursor-pointer">Day</span>
              <span class="absolute right-0 cursor-pointer">
                <IconDown/>
              </span>
            </div>
          </div>

          <div class="w-96 justify-between gap-1.5 items-center inline-flex mt-10 mb-10 text-center">
            <h2 class="text-white text-xxl font-bold text-center tracking-wide">
              Choose Hours
            </h2>

          </div>
          <div class="relative w-full flex items-center">
            <TimeSelect :available-hours="hoursAvailableStart" label="Start From" renting-form="rentingForm" @timeChanged="timeChanged($event, 'start_time')" />
          </div>

          <div class="relative w-full flex items-center">
            <span class="absolute left-5 top-0 text-neutral-700 cursor-pointer">To</span>
            <TimeSelect :available-hours="hoursAvailableEnd" label="To" renting-form="rentingForm" @timeChanged="timeChanged($event, 'end_time')" />
          </div>
        </div>

        <div class="flex-col mb-14 justify-center items-center gap-1.5 flex">
          <div class="justify-center items-center gap-2.5 inline-flex">
            <button @click="book()" class="w-96 h-11 p-3.5 hover:opacity-90 bg-white rounded-[10px] text-neutral-900 text-sm font-medium tracking-wide">Book Time</button>
          </div>
        </div>

        <div v-if="session?.reservations" class="justify-start gap-1.5 items-start border-neutral-700 border p-3 rounded-[10px] inline-flex mb-10 text-center">
          <IconMic class="text-white h-6 w-6"/>
          <div class="text-white text-sm text-left font-bold tracking-wide">
            <!--             {{session.reservations}}: { "address_id": 2, "start_time": "2024-05-20T18:58:00.000000Z", "end_time": "2024-05-20T18:59:00.000000Z", "user_id": 1, "total_cost": 30, "date": "2024-05-20", "updated_at": "2024-05-20T08:58:30.000000Z", "created_at": "2024-05-20T08:58:30.000000Z", "id": 5 }-->
            You have a reservation at <br>
            <a :href="`/@${brand.name}`"> {{brand.name}}</a> on {{brand.addresses[0].street}} <br>
            <br/>
            {{formatDate(session?.reservations?.date)}} from <br>
            {{formatTime(session?.reservations?.start_time)}} to {{formatTime(session?.reservations?.end_time)}} <br>

            {{session?.reservations?.start_time}} - {{session?.reservations?.end_time}} <br>
          </div>
        </div>

        <div v-if="session?.payment_session?.status" class="flex-col mb-14 justify-center items-center gap-1.5 flex">

          <div class="justify-center items-center gap-2.5 inline-flex">
            <div class="w-96 gap-1.5 h-11 hover:opacity-90 bg-transparent rounded-[10px] text-neutral-900 text-sm font-medium tracking-wide">
              <div class="mb-2 text-white">Status Of Payment: <span :class="session?.payment_session?.status == 'open' ? 'text-red' : 'text-white'">{{session?.payment_session?.status == 'open' ? 'Not Paid' : 'Success'}}</span></div>
              <div class="text-white">Total: ${{session?.payment_session?.amount_total / 100}}</div>
            </div>
          </div>
          <div class="justify-center items-center gap-2.5 inline-flex">
            <button @click="pay(session?.payment_session?.url)" class="w-96 h-11 p-3.5 hover:opacity-90 bg-white rounded-[10px] text-neutral-900 text-sm font-medium tracking-wide">Pay Now</button>
          </div>
        </div>

      </div>
    </div>
  </div>
</template>

<style scoped lang="scss">
a {
  //make cool text decoration and maybe not white but some other color
  text-decoration: underline;
  color: var(--color-gold);
  text-shadow: 2px 2px 4px var(--color-dark-orange); /* Shadow effect */
}
.shadow-text{
  text-shadow: 2px 3px 1px rgba(0, 0, 0, 0.8), 12px 14px 1px rgba(0, 0, 0, 0.8);
}
.checkbox-wrapper {
  display: flex;
  gap: 5px;
  cursor: pointer;

  .custom-checkbox {
    display: inline-block;
    vertical-align: middle;
    position: relative;

    &:after {
      content: '';
      position: absolute;
      display: none;
    }
  }

  input[type="checkbox"] {
    &:checked ~ .custom-checkbox {
      padding: 3px;
      position: relative;
      display: flex;
      justify-content: center;
      align-items: center;

      &:after {
        position: relative;
        top: 0;
        left: 0;
        display: block;
        width: 100%;
        height: 100%;
        border: solid white;
        background: #F3F5FD;
        border-radius: 2px;
      }
    }
  }
}
select {
  -webkit-appearance: none;
  -moz-appearance: none;
  text-indent: 1px;
  text-overflow: '';
  cursor: pointer;
}

</style>