<script setup lang="ts">
import {useHead} from "@unhead/vue";
import {definePageMeta, useRuntimeConfig} from '#imports'
import { useSessionStore } from "~/src/entities/Session";
import {onMounted, ref, type UnwrapRef} from "vue";
import {BrandingLogo, BrandingLogoSample, BrandingLogoSmall} from "~/src/shared/ui/branding";
import {navigateTo, useRoute} from "nuxt/app";
import {
  type formValues,
  type inputValues,
  type StudioFormValues,
  useCreateStudioFormStore
} from "~/src/entities/RegistrationForms";
import {IconDown, IconElipse, IconLeft, IconLine, IconRight, IconTrash} from "~/src/shared/ui/common";
import {Loader} from "@googlemaps/js-api-loader";
import axios from "axios";
import {isBadgeTaken} from "~/src/shared/utils/checkBadge";
import {useRouter} from "vue-router";
import FormData from "form-data";
definePageMeta({
  middleware: ["auth"],
})

useHead({
  title: 'Dashboard | Prices',
  meta: [
    { name: 'Funny How', content: 'Dashboard' }
  ],
})

const isLoading = ref(false)
//delete
const workHours = ref({
  mode_id: 1,
  open_time: '',
  close_time: '',
  open_time_weekend: '',
  close_time_weekend: '',
  address_id: '',
})

//delete
const badges = ref([])

const prices = ref([])

const route = useRoute();

const router = useRouter();

const pricesList = [{hours: 1}, {hours: 4}, {hours: 8}, {hours: 12}, {hours: 24}]

function isError(form: string, field: string): boolean {
  let formErrors: Record<string, any> = useCreateStudioFormStore().errors;
  return formErrors.hasOwnProperty(field) ? formErrors[field][0] : false;
}



function addPrice(){
  // Get all the existing hours from the prices array
  const existingHours = prices.value.map(price => price.hours);

  // Find the first available hours from pricesList that is not in existingHours
  const nextAvailableHours = pricesList.find(pr => !existingHours.includes(pr.hours));

  // If there's an available hours slot, add a new price with that hours value
  if (nextAvailableHours) {
    prices.value.push({
      total_price: '60',
      hours: nextAvailableHours.hours,
      is_enabled: false
    });
  } else {
    console.log("No available hours slot to add a new price.");
  }
}



function addSamplePrices(){
  prices.value.push({
        total_price: '60',
        hours: 1,
        is_enabled: false
      },
      {
        total_price: '240',
        hours: 4,
        is_enabled: false
      },
      {
        total_price: '360',
        hours: 12,
        is_enabled: false
      });
}

const session = ref()
onMounted(async () => {
  const config = useRuntimeConfig()
  session.value = useSessionStore()
  addSamplePrices()
  getPrices()
  getAddressId()
  if(!session.value.isAuthorized){
    navigateTo('/login')
  }

  const loader = new Loader({
    apiKey: config.public.googlePlacesApi,
    version: "weekly",

  });

  console.log('loader', loader);

  const Places = await loader.importLibrary('places')

  // the center, defaultbounds are not necessary but are best practices to limit/focus search results
  const center = { lat: 34.082298, lng: -82.284777 };
  // Create a bounding box with sides ~10km away from the center point
  const defaultBounds = {
    north: center.lat + 0.1,
    south: center.lat - 0.1,
    east: center.lng + 0.1,
    west: center.lng - 0.1,
  };

  //this const will be the first arg for the new instance of the Places API

  const input = document.getElementById("place"); //binds to our input element

  console.log('input', input); //optional logging

  //this object will be our second arg for the new instance of the Places API
  const options = {
    componentRestrictions: { country: ["us", "ca"] },
    fields: ["address_components", "geometry"],
    types: ["address"],
  };

  // per the Google docs create the new instance of the import above. I named it Places.
  const autocomplete = new Places.Autocomplete(input, options);

  console.log('autocomplete', autocomplete); //optional log but will show you the available methods and properties of the new instance of Places.

  //add the place_changed listener to display results when inputs change
  autocomplete.addListener('place_changed', () => {
    const place = autocomplete.getPlace();

    getFormValues().address = input?.value;
    getFormValues().country = place.address_components.find(item => item.types.includes('country'))?.short_name;
    getFormValues().city = place.address_components.find(item => item.types.includes('locality'))?.short_name;
    getFormValues().street = place.address_components.find(item => item.types.includes('route'))?.short_name;
    getFormValues().longitude = place.geometry.viewport?.Gh?.hi;
    getFormValues().latitude = place.geometry.viewport?.Wh?.hi;

    console.log('place', place);
  });


})

function filterUnassigned(obj) {
  return Object.fromEntries(Object.entries(obj).filter(([_, v]) => v !== ''));
}

function sendPrice(price){
  const config = useRuntimeConfig()

  let data = {
    "total_price": price.total_price,
    "hours": price.hours,
    "is_enabled": price.is_enabled
  }

  if(price.id){
    data.address_price_id = price.id
  }

  let requestConfig = {
    method: 'post',
    credentials: true,
    url: `${config.public.apiBase}/v1/address/${route.params.id}/prices`,
    data: data,
    headers: {
      'Accept': 'application/json',
      'Authorization': 'Bearer ' + useSessionStore().accessToken
    }
  };
  axios.defaults.headers.common['X-Api-Client'] = `web`
  axios.request(requestConfig)
      .then((response) => {
        console.log('response', response.data.data)
      })
      .catch((error) => {
        console.log(error);
      });
}

function deletePrice(price){
  const config = useRuntimeConfig()

  let data = new FormData();

  let requestConfig = {
    method: 'delete',
    maxBodyLength: Infinity,
    url: `${config.public.apiBase}/v1/address/prices?address_id=${route.params.id}&address_prices_id=${price.id}`,
    headers: {
      'Accept': 'application/json',
      'Authorization': 'Bearer ' + useSessionStore().accessToken
    },
    data : data
  }

  axios.request(requestConfig)
      .then((response) => {
        console.log('response', response.data.data)
        prices.value = response.data.data;
      })
      .catch((error) => {
        console.log(error);
      });
}
function getPrices(){
  const config = useRuntimeConfig()

  let requestConfig = {
    method: 'get',
    credentials: true,
    url: `${config.public.apiBase}/v1/address/${route.params.id}/prices`,
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
        prices.value = response.data.data;
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
        workHours.value.address_id = response?.data?.data.addresses.find(addr => addr.id == route.params.id)?.id
      })
      .catch((error) => {
        console.log(error);
      });
}
function getFormValues(): StudioFormValues {
  return useCreateStudioFormStore().inputValues;
}

function isBadge(badgeId: number, badges): boolean {
  if(badges.length > 0)
  return isBadgeTaken(badgeId, badges);
}

function routeBack(){
  navigateTo(`/@${route.params.slug}/setup/${route.params.id}/badges`)
}

function routeNext(){
  navigateTo(`/@${route.params.slug}`)
}

function signOut() {
  session.value.logout()
}

</script>

<template>
  <div class="grid min-h-[100vh] h-full animate__animated animate__fadeInRight">
    <div class="w-full mt-20 h-full flex-col justify-between items-start gap-7 inline-flex">
      <div class="relative w-full flex-col justify-start items-center gap-2.5 flex">
        <BrandingLogo class="mb-20"/>
        <div class="animate__animated animate__fadeInRight">
          <div class="breadcrumbs mb-10 text-white text-sm font-normal tracking-wide flex gap-1.5 justify-center items-center">
            <icon-elipse :class="'opacity-100'" class="h-4"/>
            <button :class="'opacity-100'"> Add Studio</button>
            <icon-line :class="'opacity-100'" class="h-2 only-desktop"/>
            <icon-elipse :class="'opacity-20'" class="h-4"/>
            <button :class="'opacity-20'" > Price Plans </button>
          </div>
        </div>

        <div class="w-96 justify-center items-center inline-flex mb-10 text-center">
          <div class="text-white text-xl font-bold text-center tracking-wide">Set Up Prices </div>
        </div>

        <div class="flex-col justify-start items-start gap-1.5 flex">
          <div class="w-96 justify-between items-start inline-flex">
            <div class="text-neutral-700 text-sm font-normal tracking-wide">Price</div>
            <div :class="isError('setup', 'studio_name') ? '' : 'hidden'" class=" text-right text-red-500 text-sm font-normal tracking-wide">{{
                isError('setup', 'studio_name')
              }}</div>
          </div>
          <div class="flex-col mb-1 justify-center items-center gap-1.5 flex">
            <div class="justify-center items-center gap-2.5 inline-flex">
              <button @click="addPrice()" class="w-96 h-11 p-3.5 hover:opacity-90 bg-transparent rounded-[10px] text-white border-white border text-sm font-medium tracking-wide">Add price</button>
            </div>
          </div>
          <div class="flex-col mb-1 justify-center items-center gap-1.5 flex">
            <div class="justify-center items-center gap-2.5 inline-flex">
              <button @click="addSamplePrices()" class="w-96 h-11 p-3.5 hover:opacity-90 bg-transparent rounded-[10px] text-white border-white border text-sm font-medium tracking-wide">Replace With Sample Data</button>
            </div>
          </div>
        </div>

        <div class="flex-col justify-center items-center gap-1.5 flex">
          <div class="w-96 justify-between items-start inline-flex">
            <div class="text-neutral-700 text-sm font-normal tracking-wide">You can edit pricing anytime</div>
            <div :class="isError('setup', 'studio_name') ? '' : 'hidden'" class=" text-right text-red-500 text-sm font-normal tracking-wide">{{
                isError('setup', 'studio_name')
              }}</div>
          </div>
          <div v-for="price in prices" :key="price.hours" class="animate__animated animate__fadeInRight relative w-full max-w-96 flex items-center gap-1.5 justify-between">
            <label class="checkbox-wrapper flex">
              <div class="w-5 h-5 justify-center items-center flex">
                <input @change="sendPrice(price)" v-model="price.is_enabled" type="checkbox" class="hidden" />
                <div class="w-5 h-5 rounded-[5px] border border-white custom-checkbox"></div>
              </div>
            </label>
            <div class="relative w-full flex items-center">
              <div class="flex items-center">
                <select v-model="price.hours" @change="sendPrice(price)" class="w-full opacity-0 absolute top-0 px-3 h-11 outline-none rounded-[10px] focus:border-white border border-white border-opacity-20 bg-transparent text-white text-sm font-medium tracking-wide" name="workday">
                  <option v-for="pr in pricesList" :value="pr.hours" :disabled="prices.some(p => p.hours === pr.hours)">
                    {{ pr.hours }}
                  </option>
                </select>
              </div>
              <div class="relative flex items-center pointer-events-none">
                <input disabled :value="price.hours" placeholder="Hours" class="w-full px-3 h-11 outline-none rounded-[10px] focus:border-white border border-neutral-700 border-opacity-100 bg-transparent text-white text-sm font-medium tracking-wide" name="workday"/>
                <span class="absolute right-5 text-neutral-700 cursor-pointer">hours</span>
                <span class="absolute right-0 cursor-pointer">
            <IconDown/>
          </span>
              </div>
            </div>
            <div class="relative w-full flex items-center">
              <input @blur="sendPrice(price)" v-model="price.total_price" type="number" placeholder="0" class="w-full px-3 h-11 outline-none rounded-[10px] focus:border-white border border-neutral-700 border-opacity-100 bg-transparent text-white text-sm font-medium tracking-wide" name="price"/>
              <span class="absolute right-2 text-neutral-700 cursor-pointer">$</span>
            </div>
            <div @click="deletePrice(price)" class="relative cursor-pointer flex items-center">
              <IconTrash/>
            </div>
          </div>

        </div>

        <div class="w-96 h-11 p-3.5 mb-5 mt-5 justify-center items-center gap-2.5 inline-flex">
          <button @click="routeBack()" class="w-full flex justify-start items-center gap-2 h-11 hover:opacity-70 rounded-[10px] text-white text-sm font-medium tracking-wide">
            <IconLeft/>
            <span class="font-light">Back</span>
          </button>
          <button @click="routeNext()" class="w-full flex justify-end items-center gap-2 h-11 hover:opacity-70 rounded-[10px] text-white text-sm font-medium tracking-wide">
            <span class="font-light">Next</span>
            <IconRight/>
          </button>
        </div>


      </div>
    </div>
  </div>
</template>

<style scoped lang="scss">
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
input[type=number]::-webkit-outer-spin-button,
input[type=number]::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}

/* For Firefox */
input[type=number] {
  -moz-appearance: textfield;
}

</style>