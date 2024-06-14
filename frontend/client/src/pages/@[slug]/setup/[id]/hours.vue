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
import {IconDown, IconElipse, IconLeft, IconLine, IconRight} from "~/src/shared/ui/common";
import {Loader} from "@googlemaps/js-api-loader";
import axios from "axios";
definePageMeta({
  middleware: ["auth"],
})

useHead({
  title: 'Dashboard | Setup',
  meta: [
    { name: 'Funny How', content: 'Dashboard' }
  ],
})

const isLoading = ref(false)
const workHours = ref({
  mode_id: 1,
  open_time: '',
  close_time: '',
  open_time_weekend: '',
  close_time_weekend: '',
  address_id: '',
})

const modes = ref([])

const route = useRoute();

function isError(form: string, field: string): boolean {
  let formErrors: Record<string, any> = useCreateStudioFormStore().errors;
  return formErrors.hasOwnProperty(field) ? formErrors[field][0] : false;
}

const session = ref()
onMounted(async () => {
  const config = useRuntimeConfig()
  session.value = useSessionStore()
  getModes()
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

function setHours(){
  const config = useRuntimeConfig()

  let requestConfig = {
    method: 'post',
    credentials: true,
    url: `${config.public.apiBaseClient}/v1/address/operating-hours`,
    data: filterUnassigned(workHours.value),
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
        navigateTo(`/@${route.params.slug}/setup/${route.params.id}/badges`)
      })
      .catch((error) => {
        console.log(error);
      });
}
function getModes(){
  const config = useRuntimeConfig()

  let requestConfig = {
    method: 'get',
    credentials: true,
    url: `${config.public.apiBaseClient}/v1/operation-modes`,
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
        modes.value = response.data.data;
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
    url: `${config.public.apiBaseClient}/v1/company/${route.params.slug}`,
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

function routeBack(){
  navigateTo(`/create`)
}

function routeNext(){
  navigateTo(`/@${route.params.slug}/setup/${route.params.id}/badges`)
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
            <button :class="'opacity-100'"> Setup Hours</button>
            <icon-line :class="'opacity-100'" class="h-2 only-desktop"/>
            <icon-elipse :class="'opacity-20'" class="h-4"/>
            <router-link :class="'opacity-20'" :to="`/@${route.params.slug}/setup/${route.params.id}/badges`">
              Setup Badges
            </router-link>
            <icon-line :class="'opacity-20'" class="h-2 only-desktop"/>
            <icon-elipse :class="'opacity-20'" class="h-4"/>
            <router-link :class="'opacity-20'" :to="`/@${route.params.slug}/setup/${route.params.id}/prices`">
              Setup Prices
            </router-link>
          </div>
        </div>

        <div class="w-96 justify-center items-center inline-flex mb-10 text-center">
          <div class="text-white text-xl font-bold text-center tracking-wide">Set Up Hours </div>
        </div>

        <div class="flex-col justify-start items-start gap-1.5 flex">
          <div class="w-96 justify-between items-start inline-flex">
            <div class="text-white text-sm font-normal tracking-wide">Working hours</div>
            <div :class="isError('setup', 'studio_name') ? '' : 'hidden'" class=" text-right text-red-500 text-sm font-normal tracking-wide">{{
                isError('setup', 'studio_name')
              }}</div>
          </div>


        </div>

        <div class="flex-col justify-center items-center gap-1.5 flex">

          <div class="w-96 justify-center items-center gap-2.5 inline-flex">
            <div class="w-96 max-w-96 relative">
              <div class="flex items-center">
                <select :class="workHours.mode_id == 3 ? 'opacity-0 absolute' : ''" v-model="workHours.mode_id" class="w-full top-0 px-3 h-11 outline-none rounded-[10px] focus:border-white border border-white border-opacity-20 bg-transparent text-white text-sm font-medium tracking-wide" name="workday">
                  <optgroup v-for="mode in modes" :label="mode.description">
                  <option class="text-white" :value="mode.id">{{mode.mode}}</option>
                  </optgroup>
                </select>
                <span v-if="workHours.mode_id != 3" class="absolute right-0 cursor-pointer">
                  <IconDown/>
                </span>
              </div>
              <div v-if="workHours.mode_id == 3" class="relative flex items-center pointer-events-none">
              <input disabled placeholder="Weekday Hours" class="w-full px-3 h-11 outline-none rounded-[10px] focus:border-white border border-white border-opacity-100 bg-transparent text-white text-sm font-medium tracking-wide" name="workday"/>
                <span class="absolute right-0 cursor-pointer">
                  <IconDown/>
                </span>
              </div>
            </div>

            <div v-if="workHours.mode_id == 2" class="w-48 max-w-48 gap-2.5 inline-flex justify-center items-center">
              <input v-model="workHours.open_time" class="w-full h-11 px-3 outline-none rounded-[10px] focus:border-white border border-white border-opacity-20 bg-transparent text-white text-sm font-medium tracking-wide" type="time" placeholder="From" />
              <input v-model="workHours.close_time" class="w-full h-11 px-3 outline-none rounded-[10px] focus:border-white border border-white border-opacity-20 bg-transparent text-white text-sm font-medium tracking-wide" type="time" placeholder="To" />
            </div>

            <div v-if="workHours.mode_id == 3" class="w-48 max-w-48 gap-2.5 inline-flex justify-center items-center">
              <input v-model="workHours.open_time" class="w-full h-11 px-3 outline-none rounded-[10px] focus:border-white border border-white border-opacity-20 bg-transparent text-white text-sm font-medium tracking-wide" type="time" placeholder="From (Weekday)" />
              <input v-model="workHours.close_time" class="w-full h-11 px-3 outline-none rounded-[10px] focus:border-white border border-white border-opacity-20 bg-transparent text-white text-sm font-medium tracking-wide" type="time" placeholder="To (Weekday)" />
            </div>

          </div>
          <div v-if="workHours.mode_id == 3"  class="w-96 justify-center items-center gap-2.5 inline-flex">
            <div class="w-96 max-w-96">
              <input disabled placeholder="Weekend Hours" class="w-full px-3 h-11 outline-none rounded-[10px] focus:border-white border border-white border-opacity-20 bg-transparent text-white text-sm font-medium tracking-wide" name="workday"/>
            </div>
            <div class="w-48 max-w-48 gap-2.5 inline-flex justify-center items-center">
              <input v-model="workHours.open_time_weekend" class="w-full h-11 px-3 outline-none rounded-[10px] focus:border-white border border-white border-opacity-20 bg-transparent text-white text-sm font-medium tracking-wide" type="time" placeholder="From (Weekend)" />
              <input v-model="workHours.close_time_weekend" class="w-full h-11 px-3 outline-none rounded-[10px] focus:border-white border border-white border-opacity-20 bg-transparent text-white text-sm font-medium tracking-wide" type="time" placeholder="To (Weekend)" />
            </div>
          </div>

        </div>

        <div class="w-96 h-11 p-3.5 mb-5 mt-5 justify-center items-center gap-2.5 inline-flex">
          <button disabled class="disabled:opacity-20 flex justify-start items-center gap-2 h-11 hover:opacity-70 rounded-[10px] text-white text-sm font-medium tracking-wide">
            <IconLeft/>
            <span class="font-light">Back</span>
          </button>
          <button @click="setHours()" class="w-full flex justify-end items-center gap-2 h-11 hover:opacity-70 rounded-[10px] text-white text-sm font-medium tracking-wide">
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

</style>