<script setup lang="ts">
import {useHead} from "@unhead/vue";
import {definePageMeta, useRuntimeConfig} from '#imports'
import {useSessionStore} from "~/src/entities/Session";
import {computed, onMounted, onUnmounted, type Ref, ref} from "vue";
import {useRoute} from "nuxt/app";
import {type StudioFormValues, useCreateStudioFormStore} from "~/src/entities/RegistrationForms";
import {IconDown, IconMic, IconNav, IconPricetag} from "~/src/shared/ui/common";
import GoogleMap from "~/src/widgets/GoogleMap.vue";
import { SelectPicker } from "~/src/features/DatePicker";
import { TimeSelect } from "~/src/widgets";
import {type ResponseBrand, useAddress} from "~/src/entities/Studio/api";
import BadgesList from "~/src/widgets/BadgesChoose/ui/BadgesList.vue";
import {ScrollContainer} from "~/src/shared/ui/common/ScrollContainer";
import {usePhotoSwipe} from "~/src/shared/ui/components/PhotoSwipe";
import type {SlideData} from "photoswipe";

const route = useRoute();
const addressId = ref(route.params.address_id);

const { address, pending, error } = useAddress(addressId.value);

const pageTitle: Ref<string> = computed(() => {
  return address.value ? `Studio | ${address.value.company.name}` : 'Loading...';
});

const isLoading = ref(false)

const session = ref()


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



useHead({
  title: pageTitle,
  meta: [
    { name: 'description', content: 'Dashboard for ' + pageTitle }
  ]
});

const photoContainer = ref<HTMLElement | null>(null);

const handleScroll = () => {
  if (!photoContainer.value) return;
  const maxHeight = 250; // Max height of the photo container
  const minHeight = 200; // Min height of the photo container
  const scrollThreshold = 0; // Scroll position at which the resizing effect should start

  const scrollPosition = window.scrollY;
  if (scrollPosition < scrollThreshold) {
    photoContainer.value.style.height = `${maxHeight}px`;
  } else {
    const newHeight = Math.max(minHeight, maxHeight - (scrollPosition - scrollThreshold));
    photoContainer.value.style.height = `${newHeight}px`;
  }
};

onMounted(async () => {
  session.value = useSessionStore()
  rentingForm.value.date = rentingList[0].date
  window.addEventListener('scroll', handleScroll);
})

onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll);
});



async function getStartSlots() {
  const config = useRuntimeConfig()
  const start_slots = await $axios.get(`${config.public.apiBaseClient}/address/reservation/start-time?address_id=1&date=${rentingForm.value.date}`);
  if (start_slots.data.success) {
    hoursAvailableStart.value = start_slots.data.data;
  }
}

async function getEndSlots(start_time: string) {
  const config = useRuntimeConfig()
  const end_slots = await $axios.get(`${config.public.apiBaseClient}/address/reservation/end-time?address_id=1&date=${rentingForm.value.date}&start_time=${start_time}`);
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
    url: `${config.public.apiBaseClient}/address/reservation`,
    headers: {
      'Accept': 'application/json',
      'Content-Type': 'application/json',
      'Authorization': 'Bearer ' + useSessionStore().accessToken
    },
    data: {
      address_id: address.value?.id,
      date: rentingForm.value.date,
      start_time: rentingForm.value.start_time,
      end_time: rentingForm.value.end_time,
    }
  };
  $axios.defaults.headers.common['X-Api-Client'] = `web`
  $axios.request(requestConfig)
      .then((response) => {
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

const getRatingColor = (rating: number) => {
  if (rating >= 4) {
    return 'text-green';
  } else if (rating >= 3) {
    return 'text-yellow';
  } else {
    return 'text-red';
  }
}

const { pswpElement, openGallery } = usePhotoSwipe();
const displayedPhotos: SlideData[] = computed(() => address?.value.photos.map(photo => ({
  src: photo.url,
  w: photo.file?.width || 1200, // Default width if not specified
  h: photo.file?.height || 900  // Default height if not specified
})));

</script>

<template>
  <div class="grid min-h-[100vh] h-full bg-black animate__animated animate__fadeInRight">
    <div v-if="!address" class="spinner-container">
      <div class="spinner"></div> <!-- Replace with a proper loading indicator -->
    </div>
    <div v-if="address && address.photos.length > 0" ref="photoContainer" class="photo-container w-full max-h-[250px] max-w-full backdrop-blur p-0 md:p-10">
      <div ref="pswpElement" class="pswp" tabindex="-1" role="dialog" aria-hidden="true">
      </div>
      <ScrollContainer v-if="address?.photos.length > 0" class=" justify-start-important rounded-[10px] h-full" theme="default" main-color="#171717">
        <div v-for="(photo, index) in address?.photos" class="max-h-30 max-w-[250px] bg-white shadow rounded-[10px] scrollElement">
          <img :src="photo.url" @click.stop="() => openGallery(displayedPhotos, index)" alt="cover photo" class="w-full h-full object-cover rounded-[10px]"/>
        </div>
      </ScrollContainer>
    </div>
    <div class="info-container w-full h-full flex-col justify-between items-start gap-7 inline-flex">
      <div v-if="address" class="relative w-full flex-col justify-start items-center gap-2.5 flex ">


        <div class="p-5 md:p-0">
            <div class="max-w-96 w-full flex items-center justify-center gap-2 mt-5 mb-5">
              <div class="text-white w-full flex flex-col justify-center items-center text-5xl font-bold">
                <div class="text-white w-full opacity-20 mb-3 text-lg font-['Montserrat'] font-normal tracking-wide">
                  Studio name
                </div>
                <div class="flex gap-5 w-full">
                  <div>
                    <img :src="address?.company?.logo_url" class="h-10 w-10 object-contain" />
                  </div>
                  <div class="font-[BebasNeue] w-full text-left">
                    {{address?.company.name}}
                  </div>
                </div>
              </div>
            </div>
            <div  class="max-w-96 w-full justify-between gap-1.5 items-center flex-col mb-10 text-center">
              <div class="text-white mb-10 text-5xl font-light text-left tracking-wide">
                <div class="text-white opacity-20 mb-3 text-lg font-['Montserrat'] font-normal tracking-wide">
                  Address
                </div>
                <div class="font-[BebasNeue]">
                  Street: {{address?.street}}<br/>
                </div>
              </div>
              <div class="max-w-96 scale-[1.3] w-full justify-start gap-2.5 items-center inline-flex mb-10 text-center">
                <BadgesList class="justify-center-important" theme="default" :badges="address?.badges" />
              </div>
              <div class="max-w-96 w-full justify-center gap-3.5 items-center flex mb-10 text-center">
                <div v-for="price in address.prices" class="price-tag flex flex-col gap-1 text-white justify-center items-center">
                  <div class="mb-2">
                  <IconPricetag/>
                  </div>
                  <div class="font-[BebasNeue] text-3xl flex justify-center items-center">
                    {{price.hours}} HOUR{{price.hours > 1 ? 'S' : ''}}
                  </div>
                  <div class="font-['Montserrat']">
                    ${{price.total_price}}
                  </div>
                </div>
              </div>
              <div  class="max-w-96 w-full justify-center gap-3.5 items-center flex mb-10 text-center">
                <div class="price-tag flex gap-2 font-[BebasNeue] text-4xl text-white justify-center items-center">
                  Rating: <span :class="getRatingColor(address?.company.rating)">{{address?.company.rating}}</span>
                </div>
              </div>
            </div>
            <div  class="max-w-[514px] w-full justify-between gap-1.5 items-center flex-col mb-10 text-center">
              <div class="w-full max-w-[514px] h-[313px] relative">
                <a :href="`https://www.google.com/maps?q=${address?.latitude},${address?.longitude}`" target="_blank" class="nav group absolute z-10 w-full h-full group bg-black cursor-pointer bg-opacity-70 hover:bg-opacity-90 transition duration-300 flex justify-center items-center">
                  <div class="navigate-button font-[BebasNeue] group-hover:scale-115 transition duration-300 text-2xl text-white flex gap-3 justify-center items-center">
                    <IconNav class="w-[20px] h-[20px]"/> Direction
                  </div>
                </a>
                <GoogleMap class="" :logo="address?.company.logo_url" :lat="address?.latitude" :lng="address?.longitude"/>
              </div>
            </div>
            <div  class="max-w-96 w-full justify-between gap-1.5 items-center flex-col mb-10 text-center">
              <div class="relative w-full flex items-center mt-10">
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

              <div class="max-w-96 w-full justify-between gap-1.5 items-center inline-flex mt-10 mb-10 text-center">
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
                <button @click="book()" class="max-w-96 w-full h-11 p-3.5 hover:opacity-90 bg-white rounded-[10px] text-neutral-900 text-sm font-medium tracking-wide">Book Time</button>
              </div>
            </div>

            <div v-if="session?.reservations" class="justify-start gap-1.5 items-start border-neutral-700 border p-3 rounded-[10px] inline-flex mb-10 text-center">
              <IconMic class="text-white h-6 w-6"/>
              <div class="text-white text-sm text-left font-bold tracking-wide">
                <!--             {{session.reservations}}: { "address_id": 2, "start_time": "2024-05-20T18:58:00.000000Z", "end_time": "2024-05-20T18:59:00.000000Z", "user_id": 1, "total_cost": 30, "date": "2024-05-20", "updated_at": "2024-05-20T08:58:30.000000Z", "created_at": "2024-05-20T08:58:30.000000Z", "id": 5 }-->
                You have a reservation at <br>
                <a :href="`/@${address?.company.slug}`"> {{address?.company.name}}</a> on {{address.street}} <br>
                <br/>
                {{formatDate(session?.reservations?.date)}} from <br>
                {{formatTime(session?.reservations?.start_time)}} to {{formatTime(session?.reservations?.end_time)}} <br>

                {{session?.reservations?.start_time}} - {{session?.reservations?.end_time}} <br>
              </div>
            </div>

            <div v-if="session?.payment_session?.status" class="flex-col mb-14 justify-center items-center gap-1.5 flex">

              <div class="justify-center items-center gap-2.5 inline-flex">
                <div class="max-w-96 w-full gap-1.5 h-11 hover:opacity-90 bg-transparent rounded-[10px] text-neutral-900 text-sm font-medium tracking-wide">
                  <div class="mb-2 text-white">Status Of Payment: <span :class="session?.payment_session?.status == 'open' ? 'text-red' : 'text-white'">{{session?.payment_session?.status == 'open' ? 'Not Paid' : 'Success'}}</span></div>
                  <div class="text-white">Total: ${{session?.payment_session?.amount_total / 100}}</div>
                </div>
              </div>
              <div class="justify-center items-center gap-2.5 inline-flex">
                <button @click="pay(session?.payment_session?.url)" class="max-w-96 w-full h-11 p-3.5 hover:opacity-90 bg-white rounded-[10px] text-neutral-900 text-sm font-medium tracking-wide">Pay Now</button>
              </div>
            </div>
        </div>

      </div>
    </div>
  </div>
</template>

<style scoped lang="scss">
//a {
//  //make cool text decoration and maybe not white but some other color
//  text-decoration: underline;
//  color: var(--color-gold);
//  text-shadow: 2px 2px 4px var(--color-dark-orange); /* Shadow effect */
//}
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
.photo-container {
  position: sticky;
  top: 0; // Adjust this value based on your header or desired offset
  transition: height 0.1s ease-in-out; // Smooth transition for height change
  z-index: 1000; // Ensure the photo container is above other content
}
</style>