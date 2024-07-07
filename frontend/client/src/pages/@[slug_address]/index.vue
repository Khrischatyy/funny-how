<script setup lang="ts">
import {useHead} from "@unhead/vue";
import {definePageMeta, useRuntimeConfig} from '#imports'
import {useSessionStore} from "~/src/entities/Session";
import {computed, onMounted, onUnmounted, type Ref, ref, watch, watchEffect} from "vue";
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
import {useApi} from "~/src/lib/api";
import paymentSystems from '~/src/shared/assets/image/payment_systems.png';
import {useSeoMeta} from "unhead";
import {DisplayNumber} from "~/src/shared/ui/components";
import {getRatingColor } from "~/src/shared/utils";
import IconAddress from "~/src/shared/ui/common/Icon/IconAddress.vue";
import {Clipboard} from "~/src/shared/ui/common/Clipboard";

const route = useRoute();
const addressSlug = ref(route.params.slug_address);
const bookingError = ref('');
const { address, pending, error } = useAddress(addressSlug.value);

const addressId = computed(() => address.value?.id);
const pageTitle: Ref<string> = computed(() => {
  return address.value ? `${address.value.company.name} | Recording Studio` : 'Loading...';
});

const pageDescription: Ref<string> = computed(() => {
  return address.value && address.value.prices.length > 0 ? `Book a session at ${address.value.company.name} from $${address.value.prices[0].price_per_hour}/hour. Only at Funny-How.com` : 'Book a session only at Funny-How.com';
});
const studioFirstPhoto: Ref<string> = computed(() => {
  return address.value && address?.value?.photos.length > 0 ? address?.value?.photos[0].url : '/meta/open-graph-image.png';
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
  start_time: '',
  end_time: '',
})
const today = new Date();
const tomorrow = new Date();
tomorrow.setDate(today.getDate() + 1);
const hoursAvailableStart = ref([]);
const hoursAvailableEnd = ref([]);
const rentingList = [{name: 'today', date: ''}, {name: 'tomorrow', date: ''}, {name: 'another-day', date: 'another-day'}];
rentingList[0].date = today.toISOString().split('T')[0];
rentingList[1].date = tomorrow.toISOString().split('T')[0];

const setSeoMeta = () => {
  useSeoMeta({
    title: () => `${address.value?.company.name} - Funny How`,
    description: () => `Book a session at ${address.value.company.name} from $${address.value.prices[0].price_per_hour}/hour. Only at Funny-How.com`,
    ogTitle: `${address.value?.company.name} - Funny How`,
    ogDescription:() => `Book a session at ${address.value.company.name} from $${address.value.prices[0].price_per_hour}/hour. Only at Funny-How.com`,
    ogImage: ()=> `${address?.value?.photos[0].url}`,
    ogUrl: route.fullPath,
    twitterTitle: () => `${address.value?.company.name} - Funny How`,
    twitterDescription:() => `Book a session at ${address.value.company.name} from $${address.value.prices[0].price_per_hour}/hour. Only at Funny-How.com`,
    twitterImage:`${address?.value?.photos[0].url}`,
    twitterCard: 'summary'
  });

  useHead({
    title: pageTitle,
    meta: [
      { name: 'description', content: pageDescription.value }
    ],
    htmlAttrs: {
      lang: 'en'
    },
    link: [
      {
        rel: 'icon',
        type: 'image/png',
        href: '/favicon.png'
      }
    ]
  });
};


useSeoMeta({
  title: () => `${address.value?.company.name} - Funny How`,
  description: () => `Book a session at ${address.value?.company.name} from $${address.value?.prices[0].price_per_hour}/hour. Only at Funny-How.com`,
  ogTitle: `${address.value?.company.name} - Funny How`,
  ogDescription:() => `Book a session at ${address.value?.company.name} from $${address.value?.prices[0].price_per_hour}/hour. Only at Funny-How.com`,
  ogImage: () => {
    const photos = address?.value?.photos;
    return photos && photos.length > 0 ? photos[0].url : '/meta/open-graph-image.png';
  },
  ogUrl: route.fullPath,
  twitterTitle: () => `${address.value?.company.name} - Funny How`,
  twitterDescription:() => `Book a session at ${address.value?.company.name} from $${address.value?.prices[0].price_per_hour}/hour. Only at Funny-How.com`,
  twitterImage:() => {
    const photos = address?.value?.photos;
    return photos && photos.length > 0 ? photos[0].url : '/meta/open-graph-image.png';
  },
  twitterCard: 'summary'
});

useHead({
  title: pageTitle,
  meta: [
    { name: 'description', content: pageDescription }
  ],
  htmlAttrs: {
    lang: 'en'
  },
  link: [
    {
      rel: 'icon',
      type: 'image/png',
      href: '/favicon.png'
    }
  ]
})

const photoContainer = ref<HTMLElement | null>(null);

const handleScroll = () => {
  if (!photoContainer.value) return;
  const maxHeight = 250; // Max height of the photo container
  const minHeight = 150; // Min height of the photo container
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
  session.value = useSessionStore();
  console.log('route.params.slug_address', route.params)
  rentingForm.value.date = rentingList[0].date;
  window.addEventListener('scroll', handleScroll);
  window.addEventListener('message', handlePaymentStatus);
});

onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll);
  window.removeEventListener('message', handlePaymentStatus);
});

function handlePaymentStatus(event: MessageEvent) {
  if (event.origin !== window.location.origin) return; // Ensure the message is from the same origin
  if (event.data && event.data.status === 'closed') {
    console.log('Payment window closed');
    // Handle the status update here
  }
}

async function getStartSlots() {
  const {fetch: getStartSlots} = useApi({
    url: `/address/reservation/start-time?address_id=${addressId.value}&date=${rentingForm.value.date}`,
  });

  getStartSlots().then((response) => {
    hoursAvailableStart.value = response.data;
  });

}

async function getEndSlots(start_time: string) {
  const {fetch: getEndSlots} = useApi({
    url: `/address/reservation/end-time?address_id=${address?.value.id}&date=${rentingForm.value.date}&start_time=${start_time}`,
  });

  getEndSlots().then((response) => {
    hoursAvailableEnd.value = response.data;
  });


}
const calculatedPrice = ref(0)

const calculatePrice = () => {
  //address_id
  //start_time
  //end_time
  const {post: getPrice} = useApi({
    url: `/address/calculate-price`,
    auth: true,
  });

  getPrice({
    address_id: addressId.value,
    start_time: rentingForm.value.start_time,
    end_time: rentingForm.value.end_time,
  }).then((response) => {
    console.log('Price:', response.data);
    calculatedPrice.value = response.data;
  });
}

watch(()=> address.value, async (newVal) => {
  if (newVal) {
    await getStartSlots();
  }
});

watch(() => rentingForm.value.start_time, (newVal) => {
  if(newVal && rentingForm.value.end_time){
    calculatePrice()
  }
})
watch(() => rentingForm.value.end_time, (newVal) => {
  if(newVal){
    calculatePrice()
  }
})
watch(() => rentingForm.value.date, (newVal) => {
  if(newVal){
    getStartSlots()
  }
})
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
watchEffect(() => {
  if(rentingForm.value.start_time || rentingForm.value.end_time || rentingForm.value.date){
    bookingError.value = ''
  }
})
function book() {
  isLoading.value = true;
  const { post: bookTime } = useApi({
    url: `/address/reservation`,
    auth: true,
  });

  bookTime({
    address_id: address.value?.id,
    date: rentingForm.value.date,
    start_time: rentingForm.value.start_time,
    end_time: rentingForm.value.end_time,
  }).then((response) => {
    responseQuote.value = response.data;
    isLoading.value = false;
    if (response.data?.payment_session?.status == 'open') {
      window.location.href = response.data?.payment_url;
    }
  }).catch((error) => {
    bookingError.value = error.message;
    isLoading.value = false;
  });
}
function pay(url: string) {
  const paymentWindow = window.open(url, '_blank', 'width=800,height=600,toolbar=0,location=0,menubar=0');

  const checkWindowClosed = setInterval(() => {
    if (paymentWindow && paymentWindow.closed) {
      clearInterval(checkWindowClosed);
      window.postMessage({ status: 'closed' }, window.location.origin);
    }
  }, 500);
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

const { pswpElement, openGallery } = usePhotoSwipe();
const displayedPhotos: SlideData[] = computed(() => address?.value.photos.map(photo => ({
  src: photo.url,
  w: photo.file?.width || 1200, // Default width if not specified
  h: photo.file?.height || 900  // Default height if not specified
})));

</script>

<template>
  <div class="grid min-h-[100vh] h-full bg-black">
    <div v-if="!address" class="spinner-container">
      <div class="spinner"></div> <!-- Replace with a proper loading indicator -->
    </div>
    <div v-if="address && address.photos.length > 0" ref="photoContainer" class="photo-container animate__animated animate__fadeInRight w-full max-h-[250px] max-w-full backdrop-blur p-0 py-5 md:p-10">
      <div ref="pswpElement" class="pswp" tabindex="-1" role="dialog" aria-hidden="true">
      </div>
      <ScrollContainer v-if="address?.photos.length > 0" justify-content="center" class="rounded-[10px] h-full" theme="default" main-color="#171717">
        <div v-for="(photo, index) in address?.photos.sort((a, b) => a.index - b.index)" class="max-h-30 max-w-[250px] bg-white shadow rounded-[10px] scrollElement">
          <img :src="photo.url" @click.stop="() => openGallery(displayedPhotos, index)" alt="cover photo" class="w-full h-full object-cover rounded-[10px]"/>
        </div>
      </ScrollContainer>
    </div>
    <div class="info-container w-full animate__animated animate__fadeInRight h-full flex-col justify-between items-start gap-7 inline-flex">
      <div v-if="address" class="relative w-full flex-col justify-start items-center gap-2.5 flex ">


        <div class="p-5 md:p-0 max-w-96">
            <div class="max-w-96 w-full flex items-center justify-center gap-2 mt-5 mb-5">
              <div class="text-white w-full flex flex-col justify-center items-center text-5xl font-bold">
                <div class="text-white w-full opacity-20 mb-3 text-lg font-['Montserrat'] font-normal tracking-wide">
                  Studio name
                </div>
                <div class="flex gap-5 w-full">
                  <div v-if="address?.company?.logo_url">
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
                <Clipboard :text-to-copy="address?.street">
                  <div class="flex gap-5 w-full">
                    <div>
                      <IconAddress class="h-10 w-10 object-contain" />
                    </div>
                    <div class="font-[BebasNeue] w-full text-left">
                       {{address?.street}}<br/>
                    </div>
                  </div>
                </Clipboard>


              </div>
              <div class="max-w-full w-full justify-start gap-2.5 items-center inline-flex mb-10 text-center">
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
                  Rating: <span :class="getRatingColor(address?.rating)">{{address?.rating}}</span>
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
            <div v-if="address && hoursAvailableStart" class="max-w-96 w-full justify-between gap-1.5 items-center flex-col mb-10 text-center">
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
              <div v-if="rentingForm.date && hoursAvailableStart.length > 0" class="relative w-full flex items-center">
                <TimeSelect :available-hours="hoursAvailableStart" label="Start From" renting-form="rentingForm" @timeChanged="timeChanged($event, 'start_time')" />
              </div>
              <div v-if="rentingForm['start_time'] && rentingForm.date && hoursAvailableEnd.length > 0" class="relative w-full flex items-center">
                <span class="absolute left-5 top-0 text-neutral-700 cursor-pointer">To</span>
                <TimeSelect :available-hours="hoursAvailableEnd" label="To" renting-form="rentingForm" @timeChanged="timeChanged($event, 'end_time')" />
              </div>
              <div v-if="calculatedPrice" class="relative w-full max-w-48 mx-auto flex justify-between items-center animate__animated animate__fadeInRight">
                <div class="text-white text-4xl font-[BebasNeue]">
                  Price:
                </div>
                <div class="text-white text-4xl relative font-[BebasNeue]">
                  <DisplayNumber :value="calculatedPrice" />
                </div>
              </div>
            </div>
            <div v-if="calculatedPrice" class="flex-col mb-14 relative justify-center items-center gap-1.5 flex animate__animated animate__fadeInRight">
              <div v-if="isLoading" class="spinner-container">
                <div class="spinner"></div> <!-- Replace with a proper loading indicator -->
              </div>
              <div class="justify-center items-center flex mb-10">
                <img :src="paymentSystems" />
              </div>
              <div v-if="bookingError" class="errors mb-5">
                <div class="text-red-500 text-sm">{{bookingError}}</div>
              </div>
              <div class="relative flex items-center cursor-pointer input border-double">
                <button @click="book()" class="w-full px-16 py-3 font-[BebasNeue] min-h-14 flex justify-start items-center outline-none focus:border-white border border-white border-opacity-100 bg-transparent text-white text-4xl font-medium tracking-wide">
                 Rent
                </button>
              </div>
            </div>

<!--            <div v-if="session?.reservations" class="justify-start gap-1.5 items-start border-neutral-700 border p-3 rounded-[10px] inline-flex mb-10 text-center">-->
<!--              <IconMic class="text-white h-6 w-6"/>-->
<!--              <div class="text-white text-sm text-left font-bold tracking-wide">-->
<!--                You have a reservation at <br>-->
<!--                <a :href="`/@${address?.company.slug}`"> {{address?.company.name}}</a> on {{address.street}} <br>-->
<!--                <br/>-->
<!--                {{formatDate(session?.reservations?.date)}} from <br>-->
<!--                {{formatTime(session?.reservations?.start_time)}} to {{formatTime(session?.reservations?.end_time)}} <br>-->

<!--                {{session?.reservations?.start_time}} - {{session?.reservations?.end_time}} <br>-->
<!--              </div>-->
<!--            </div>-->

<!--            <div v-if="session?.payment_session?.status" class="flex-col mb-14 justify-center items-center gap-1.5 flex">-->
<!--              <div class="justify-center items-center gap-2.5 inline-flex">-->
<!--                <div class="max-w-96 w-full gap-1.5 h-11 hover:opacity-90 bg-transparent rounded-[10px] text-neutral-900 text-sm font-medium tracking-wide">-->
<!--                  <div class="mb-2 text-white">Status Of Payment: <span :class="session?.payment_session?.status == 'open' ? 'text-red' : 'text-white'">{{session?.payment_session?.status == 'open' ? 'Not Paid' : 'Success'}}</span></div>-->
<!--                  <div class="text-white">Total: ${{session?.payment_session?.amount_total / 100}}</div>-->
<!--                </div>-->
<!--              </div>-->
<!--              <div class="justify-center items-center gap-2.5 inline-flex">-->
<!--                <button @click="pay(session?.payment_session?.url)" class="max-w-96 w-full h-11 p-3.5 hover:opacity-90 bg-white rounded-[10px] text-neutral-900 text-sm font-medium tracking-wide">Pay Now</button>-->
<!--              </div>-->
<!--            </div>-->
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