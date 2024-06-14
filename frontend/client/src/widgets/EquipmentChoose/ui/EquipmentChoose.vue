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
import {
  FInputClassic,
  IconClose,
  IconDown,
  IconElipse,
  IconLeft,
  IconLine,
  IconRight,
  IconTrash
} from "~/src/shared/ui/common";
import {Loader} from "@googlemaps/js-api-loader";
import axios from "axios";
import {isBadgeTaken} from "~/src/shared/utils/checkBadge";
import {useRouter} from "vue-router";
import FormData from "form-data";
import {Popup} from "~/src/shared/ui/components";

const workHours = ref({
  mode_id: 1,
  open_time: '',
  close_time: '',
  open_time_weekend: '',
  close_time_weekend: '',
  address_id: '',
})


const prices = ref([])

const route = useRoute();

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
    url: `${config.public.apiBaseClient}/v1/address/${route.params.id}/prices`,
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

function getFormValues(): StudioFormValues {
  return useCreateStudioFormStore().inputValues;
}

export type EquipmentType = {
  id: number,
  label: string,
  value: string
  deletable?: boolean
}

const equipment = ref<EquipmentType[]>([
    {
      id: 1,
      label: 'Microphone',
      value: '',
      deletable: false,
    },
    {
      id: 2,
      label: 'Audio interface',
      value: '',
      deletable: false,
    },
    {
      id: 3,
      label: 'Monitors',
      value: '',
      deletable: false,
    }
])
const showPopup = ref(false)
const togglePopup = () => {
  showPopup.value = !showPopup.value
}

const closePopup = () => {
  showPopup.value = false
}

const addEquipmentForm = ref({
  label: '',
  value: ''
});

const deleteEquipment = (id: number) => {
  equipment.value = equipment.value.filter(eq => eq.id !== id)
}

const addEquipment = () => {
  equipment.value.push({
    id: equipment.value.length + 1,
    label: addEquipmentForm.value.label,
    value: addEquipmentForm.value.value,
    deletable: true,
  })
  addEquipmentForm.value.value = ''
  addEquipmentForm.value.label = ''
  togglePopup()
}
</script>

<template>
  <div class="relative w-full flex-col justify-start items-center gap-2.5 flex">
    <Popup :title="'Add equipment'" type="small" :open="showPopup" @close="closePopup">
      <template #header>
        <h1 class="text-white text-[22px]/[26px]">Add equipment</h1>
      </template>
      <template #body>
        <div class="equipment w-full grid grid-cols-2 gap-2">
          <FInputClassic label="Label" placeholder="Label" v-model="addEquipmentForm.label"/>
          <FInputClassic label="Value" placeholder="Value" v-model="addEquipmentForm.value"/>
        </div>
      </template>
      <template #footer>
        <div class="flex justify-between items-center gap-2 w-full">
          <button @click="togglePopup" class="w-full h-11 p-3.5 hover:opacity-90 bg-transparent rounded-[10px] text-white border-white border text-sm font-medium tracking-wide">Cancel</button>
          <button @click="addEquipment" class="w-full h-11 p-3.5 hover:opacity-90 bg-white rounded-[10px] text-neutral-700 border-white border text-sm font-medium tracking-wide">Add</button>
        </div>
      </template>
    </Popup>
    <div class="flex-col w-full justify-start items-start gap-1.5 flex">
      <div class="w-full justify-between items-start inline-flex">
        <div class="text-neutral-700 text-sm font-normal tracking-wide">Equipment</div>
        <div :class="isError('setup', 'studio_name') ? '' : 'hidden'" class=" text-right text-red-500 text-sm font-normal tracking-wide">{{
            isError('setup', 'studio_name')
          }}</div>
      </div>
      <div class="flex-col w-full mb-1 justify-center items-center gap-1.5 flex">
        <div class="justify-center w-full items-center gap-2.5 inline-flex">
          <button @click="togglePopup" class="w-full h-11 p-3.5 hover:opacity-90 bg-white rounded-[10px] text-neutral-700 border-white border text-sm font-medium tracking-wide">Add equipment</button>
        </div>
      </div>
    </div>

    <div class="equipment-inputs flex-col w-full justify-center items-center gap-1.5 flex">
      <div class="equipment w-full grid grid-cols-3 gap-2">
        <div v-for="(eq, index) in equipment" class="flex gap-2">
          <FInputClassic :label="eq.label" :placeholder="`Name ${eq.label}`" v-model="eq.value">
            <template #action>
              <button v-if="eq.deletable" @click="deleteEquipment(eq.id)" class="w-5 h-5 flex items-center justify-center border border-white border-opacity-20 rounded-[10px] bg-transparent text-white text-sm font-medium tracking-wide cursor-pointer">
                <IconClose />
              </button>
            </template>
          </FInputClassic>
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