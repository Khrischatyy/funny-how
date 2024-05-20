<script setup lang="ts">
import {useHead} from "@unhead/vue";
import {definePageMeta, useRuntimeConfig} from '#imports'
import { useSessionStore } from "~/src/entities/Session";
import {onMounted, type Ref, ref, type UnwrapRef} from "vue";
import {BrandingLogo, BrandingLogoSample, BrandingLogoSmall} from "~/src/shared/ui/branding";
import {navigateTo, useRoute} from "nuxt/app";
import {
  type formValues,
  type inputValues,
  type StudioFormValues,
  useCreateStudioFormStore
} from "~/src/entities/RegistrationForms";
import {IconDown, IconElipse, IconLeft, IconLine, IconMic, IconRight} from "~/src/shared/ui/common";
import {Loader} from "@googlemaps/js-api-loader";
import axios from "axios";
import GoogleMap from "~/src/widgets/GoogleMap.vue";
definePageMeta({
  middleware: ["auth"],
})
const route = useRoute();

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

const rentingForm = ref({
  user_id: '',
  address_id: '',
  date: '',
  anotherDate: '',
  start_time: '',
  end_time: '',
})
const today = new Date();
const tomorrow = new Date();
tomorrow.setDate(today.getDate() + 1);

const rentingList = [{name: 'today', date: ''}, {name: 'tomorrow', date: ''}, {name: 'another-day', date: 'another-day'}];
rentingList[0].date = today.toISOString().split('T')[0];
rentingList[1].date = tomorrow.toISOString().split('T')[0];

onMounted(async () => {
  const config = useRuntimeConfig()
  getAddressId()
  session.value = useSessionStore()
  rentingForm.value.date = rentingList[0].date

})



const responseQuote = ref({})

function book(){
  const config = useRuntimeConfig()
  let requestConfig = {
    method: 'post',
    credentials: true,
    url: `${config.public.apiBase}/v1/address/reservation`,
    headers: {
      'Accept': 'application/json',
      'Content-Type': 'application/json',
      'Authorization': 'Bearer ' + useSessionStore().accessToken
    },
    data: {
      user_id: 1,
      address_id: brand.value.addresses[0].id,
      date: rentingForm.value.date,
      start_time: rentingForm.value.start_time,
      end_time: rentingForm.value.end_time,
    }
  };
  axios.defaults.headers.common['X-Api-Client'] = `web`
  axios.request(requestConfig)
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

        session.value.setReservations(response.data.data)

        console.log('response', response.data.data)
      })
      .catch((error) => {
        console.log(error);
      });

}

function getAddressId(){
  const config = useRuntimeConfig()

  let requestConfig = {
    method: 'get',
    credentials: true,
    url: `${config.public.apiBase}/v1/company/${route.params.slug}`,
    headers: {
      'Accept': 'application/json',
      'Content-Type': 'multipart/form-data',
      'Authorization': 'Bearer ' + useSessionStore().accessToken
    }
  };
  axios.defaults.headers.common['X-Api-Client'] = `web`
  axios.request(requestConfig)
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

function signOut() {
  session.value.logout()
}

</script>

<template>
  <div class="grid min-h-[100vh] h-full animate__animated animate__fadeInRight">
    <div class="w-full mt-20 h-full flex-col justify-between items-start gap-7 inline-flex">
      <div v-if="brand" class="relative w-full flex-col justify-start items-center gap-2.5 flex">
        <BrandingLogo class="mb-20"/>
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
        <div v-if="session?.reservations" class="justify-start gap-1.5 items-start border-neutral-700 border p-3 rounded-[10px] inline-flex mb-10 text-center">
          <IconMic class="text-white h-6 w-6"/>
          <div class="text-white text-sm text-left font-bold tracking-wide">
            <!--             {{session.reservations}}: { "address_id": 2, "start_time": "2024-05-20T18:58:00.000000Z", "end_time": "2024-05-20T18:59:00.000000Z", "user_id": 1, "total_cost": 30, "date": "2024-05-20", "updated_at": "2024-05-20T08:58:30.000000Z", "created_at": "2024-05-20T08:58:30.000000Z", "id": 5 }-->
            You have a reservation at <br>
            <a :href="`/@${brand.name}`"> {{brand.name}}</a> on {{brand.addresses[0].street}} <br>
            <br/>
            {{formatDate(session?.reservations?.date)}} from <br>
            {{formatTime(session?.reservations?.start_time)}} to {{formatTime(session?.reservations?.end_time)}} <br>
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
          <GoogleMap :lat="address.latitude" :lng="address.longitude"/>
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
          <div class="relative w-full flex items-center mb-5">
            <div class="flex items-center">
              <input type="time" v-model="rentingForm.start_time" placeholder="Start Time" ref="start_time" class="w-full opacity-0 absolute top-0 px-3 h-11 outline-none rounded-[10px] focus:border-white border border-white border-opacity-20 bg-transparent text-white text-sm font-medium tracking-wide">
            </div>
            <div @click="openDatePicker('start_time')" class="relative w-full flex items-center ">
              <input :value="rentingForm.start_time" class="pointer-events-none cursor-pointer w-full px-3 h-11 outline-none rounded-[10px] focus:border-white border border-neutral-700 border-opacity-100 bg-transparent text-white text-sm font-medium tracking-wide" name="start_time"/>
              <span class="absolute right-5 text-neutral-700 cursor-pointer">Start From</span>
              <span class="absolute right-0 cursor-pointer">
                <IconDown/>
              </span>
            </div>
          </div>

          <div class="relative w-full flex items-center">
            <div class="flex items-center">
              <input type="time" v-model="rentingForm.end_time" placeholder="Finish Time" ref="end_time" class="w-full opacity-0 absolute top-0 px-3 h-11 outline-none rounded-[10px] focus:border-white border border-white border-opacity-20 bg-transparent text-white text-sm font-medium tracking-wide">
            </div>
            <div @click="openDatePicker('end_time')" class="relative w-full flex items-center ">
              <input :value="rentingForm.end_time" class="w-full pointer-events-none cursor-pointer px-3 h-11 outline-none rounded-[10px] focus:border-white border border-neutral-700 border-opacity-100 bg-transparent text-white text-sm font-medium tracking-wide" name="end_time"/>
              <span class="absolute right-5 text-neutral-700 cursor-pointer"> To</span>
              <span class="absolute right-0 cursor-pointer">
                <IconDown/>
              </span>
            </div>
          </div>

        </div>

        <div class="flex-col mb-14 justify-center items-center gap-1.5 flex">
          <div class="justify-center items-center gap-2.5 inline-flex">
            <button @click="book()" class="w-96 h-11 p-3.5 hover:opacity-90 bg-white rounded-[10px] text-neutral-900 text-sm font-medium tracking-wide">Book Time</button>
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