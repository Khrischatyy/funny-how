<script setup lang="ts">
import {useRuntimeConfig} from '#imports'
import { useSessionStore } from "~/src/entities/Session";
import {inject, onMounted, ref, watch} from "vue";
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
import {useAsyncData} from "#app";
import {getModes} from "~/src/widgets/HoursChoose/api";
import {type ResponseDto, useApi} from "~/src/lib/api";
import type {Company} from "~/src/entities/RegistrationForms/api";

const workHours = ref({
  mode_id: 1,
  open_time: '10:00',
  close_time: '18:00',
  open_time_weekend: '10:00',
  close_time_weekend: '18:00',
  address_id: '',
  eachDay: [
    {day_of_week: 0, open_time: '10:00', close_time: '18:00', is_closed: false},
    {day_of_week: 1, open_time: '10:00', close_time: '18:00', is_closed: false},
    {day_of_week: 2, open_time: '10:00', close_time: '18:00', is_closed: false},
    {day_of_week: 3, open_time: '10:00', close_time: '18:00', is_closed: false},
    {day_of_week: 4, open_time: '10:00', close_time: '18:00', is_closed: false},
    {day_of_week: 5, open_time: '10:00', close_time: '18:00', is_closed: false},
    {day_of_week: 6, open_time: '10:00', close_time: '18:00', is_closed: false}
  ]
});

const modes = ref([])
const studio = inject('studioForPopup');
const route = useRoute();

function isError(form: string, field: string): boolean {
  let formErrors: Record<string, any> = useCreateStudioFormStore().errors;
  return formErrors.hasOwnProperty(field) ? formErrors[field][0] : false;
}

const session = ref()
onMounted(async () => {
  session.value = useSessionStore()
})

function filterUnassigned(obj) {
  return Object.fromEntries(Object.entries(obj).filter(([_, v]) => v !== ''));
}

function setHours(){
  const {post} = useApi<ResponseDto<Company>>({
    url: `/address/operating-hours`,
    auth: true
  });
  if(workHours.value?.mode_id == 3){
    let data = {
      mode_id: workHours.value?.mode_id,
      address_id: studio?.value.id,
      hours: workHours.value?.eachDay
    }
    post(data).then((response) => {
      console.log('response', response.data)
    }).catch(error => {
      console.error('error', error);
    });
  } else {
    post({
      ...filterUnassigned(workHours.value),
      address_id: route.params.id
    }).then((response) => {
      console.log('response', response.data)
    }).catch(error => {
      console.error('error', error);
    });
  }
}

const {data, error} = useAsyncData('operationModes', getModes);

if (data.value) {
  modes.value = data.value.data
}
watch(data, (newData) => {
  if (newData) {
    console.log('Modes Data:', newData.data);
    modes.value = newData.data || [];
  }
}, { immediate: true });

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
const getDayMean = (day: number) => {
  switch (day) {
    case 0:
      return 'Monday';
    case 1:
      return 'Tuesday';
    case 2:
      return 'Wednesday';
    case 3:
      return 'Thursday';
    case 4:
      return 'Friday';
    case 5:
      return 'Saturday';
    case 6:
      return 'Sunday';
    default:
      return '';
  }
}

</script>

<template>
  <div class="relative w-full max-w-96 flex-col justify-start items-center gap-1.5 flex">
    <div class="flex-col w-full justify-start items-start gap-1.5 flex">
      <div class="w-full justify-between items-start inline-flex">
        <div class="text-white opacity-20 text-sm font-normal tracking-wide">Working hours</div>
        <div :class="isError('setup', 'studio_name') ? '' : 'hidden'" class=" text-right text-red-500 text-sm font-normal tracking-wide">{{
            isError('setup', 'studio_name')
          }}</div>
      </div>


    </div>

    <div class="flex-col w-full justify-center items-center gap-1.5 flex">

      <div class="w-full justify-center items-center gap-2.5 inline-flex">
        <div class="w-full max-w-full relative">
          <div class="flex items-center">
            <select :class="workHours.mode_id == 3 ? 'opacity-0 absolute' : ''" v-model="workHours.mode_id" class="w-full top-0 px-3 h-11 outline-none rounded-[10px] focus:border-white border border-white border-opacity-20 bg-transparent text-white text-sm font-medium tracking-wide" name="workday">
              <optgroup v-for="mode in modes" :label="mode.description">
                <option class="text-white" :value="mode.id">{{mode.label}}</option>
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
      </div>
      <div v-for="(hour, index) in 7" v-if="workHours.mode_id == 3"  class="w-96 justify-center items-center gap-2.5 inline-flex">
        <div class="w-96 max-w-96">
          <input disabled :placeholder="getDayMean(index)" class="w-full px-3 h-11 outline-none rounded-[10px] focus:border-white border border-white border-opacity-20 bg-transparent text-white text-sm font-medium tracking-wide" name="workday"/>
        </div>
        <div class="w-48 max-w-48 gap-2.5 inline-flex justify-center items-center">
          <input v-model="workHours.eachDay[index].open_time" class="w-full h-11 px-3 outline-none rounded-[10px] focus:border-white border border-white border-opacity-20 bg-transparent text-white text-sm font-medium tracking-wide" type="time" placeholder="From (Weekend)" />
          <input v-model="workHours.eachDay[index].close_time" class="w-full h-11 px-3 outline-none rounded-[10px] focus:border-white border border-white border-opacity-20 bg-transparent text-white text-sm font-medium tracking-wide" type="time" placeholder="To (Weekend)" />
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