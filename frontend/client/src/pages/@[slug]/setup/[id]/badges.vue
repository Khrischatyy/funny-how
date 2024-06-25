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
import {IconElipse, IconLeft, IconLine, IconRight} from "~/src/shared/ui/common";
import {Loader} from "@googlemaps/js-api-loader";
import axios from "axios";
import {isBadgeTaken} from "~/src/shared/utils/checkBadge";
import {useRouter} from "vue-router";
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

const badges = ref([])

const route = useRoute();

const router = useRouter();

function isError(form: string, field: string): boolean {
  let formErrors: Record<string, any> = useCreateStudioFormStore().errors;
  return formErrors.hasOwnProperty(field) ? formErrors[field][0] : false;
}

const session = ref()

onMounted(async () => {
  const config = useRuntimeConfig()
  session.value = useSessionStore()
  getBadges()
  getAddressId()

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

function toogleBadge(badge_id){
  const config = useRuntimeConfig()

  let data = {
    "badge_id": badge_id
  }

  let requestConfig = {
    method: 'post',
    credentials: true,
    url: `${config.public.apiBaseClient}/address/${route.params.id}/badge`,
    data: data,
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
        badges.value.taken_badges = response.data.data;
      })
      .catch((error) => {
        console.log(error);
      });
}
function getBadges(){
  const config = useRuntimeConfig()

  let requestConfig = {
    method: 'get',
    credentials: true,
    url: `${config.public.apiBaseClient}/address/${route.params.id}/badges`,
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
        badges.value = response.data.data;
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
    url: `${config.public.apiBaseClient}/company/${route.params.slug}`,
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
  navigateTo(`/@${route.params.slug}/setup/${route.params.id}/hours`)
}

function routeNext(){
  navigateTo(`/@${route.params.slug}/setup/${route.params.id}/prices`)
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
            <icon-elipse :class="'opacity-20'" class="h-4"/>
            <router-link :class="'opacity-20'" :to="`/@${route.params.slug}/setup/${route.params.id}/hours`">
              Setup Hours
            </router-link>
            <icon-line :class="'opacity-20'" class="h-2 only-desktop"/>
            <icon-elipse :class="'opacity-100'" class="h-4"/>
            <router-link :class="'opacity-100'" :to="`/@${route.params.slug}/setup/${route.params.id}/badges`">
              Setup Badges
            </router-link>
            <icon-line :class="'opacity-100'" class="h-2 only-desktop"/>
            <icon-elipse :class="'opacity-20'" class="h-4"/>
            <router-link :class="'opacity-20'" :to="`/@${route.params.slug}/setup/${route.params.id}/prices`">
              Setup Prices
            </router-link>
          </div>
        </div>

        <div class="w-96 justify-center items-center inline-flex mb-10 text-center">
          <div class="text-white text-xl font-bold text-center tracking-wide">Set Up Badges </div>
        </div>

        <div class="flex-col justify-start items-start gap-1.5 flex">
          <div class="w-96 justify-between items-start inline-flex">
            <div class="text-white text-sm font-normal tracking-wide">Studio Information</div>
            <div :class="isError('setup', 'studio_name') ? '' : 'hidden'" class=" text-right text-red-500 text-sm font-normal tracking-wide">{{
                isError('setup', 'studio_name')
              }}</div>
          </div>


        </div>

        <div class="flex-col justify-center items-center gap-1.5 flex">
          <div class="w-96 justify-center items-center gap-2.5 inline-flex">
            <div class="w-96 max-w-96 flex gap-2.5">
              <div v-for="badge in badges?.all_badges" :class="isBadge(badge.id, badges?.taken_badges) ? 'border-opacity-100' : 'border-opacity-20'" @click="toogleBadge(badge.id)" class="w-full flex gap-2.5 justify-center items-center cursor-pointer h-11 outline-none rounded-[10px] focus:border-white border border-white bg-transparent text-white text-sm font-medium tracking-wide">
                <img :src="badge.image_url" />
                <span>{{badge.name}}</span>
              </div>
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
        <div class="flex-col mb-14 justify-center items-center gap-1.5 flex">
          <div class="justify-center items-center gap-2.5 inline-flex">
            <button @click="skip()" class="w-96 h-11 p-3.5 hover:opacity-90 bg-transparent border border-white text-white rounded-[10px] text-neutral-900 text-sm font-medium tracking-wide">Skip for later</button>
          </div>
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