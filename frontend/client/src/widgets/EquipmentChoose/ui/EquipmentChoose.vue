<script setup lang="ts">
import {definePageMeta, useRuntimeConfig} from '#imports'
import { useSessionStore } from "~/src/entities/Session";
import {onMounted, ref, type UnwrapRef} from "vue";
import {navigateTo, useRoute} from "nuxt/app";
import {

  type StudioFormValues,
  useCreateStudioFormStore
} from "~/src/entities/RegistrationForms";
import {
  FInputClassic,
  IconClose,
} from "~/src/shared/ui/common";
import {Loader} from "@googlemaps/js-api-loader";
import axios from "axios";
import {Popup} from "~/src/shared/ui/components";

function isError(form: string, field: string): boolean {
  let formErrors: Record<string, any> = useCreateStudioFormStore().errors;
  return formErrors.hasOwnProperty(field) ? formErrors[field][0] : false;
}

const getEquipmentTypes = async () => {

}

const session = ref()
onMounted(async () => {
  const config = useRuntimeConfig()
  session.value = useSessionStore()

})

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
      <div class="equipment w-full grid grid-cols-2 lg:grid-cols-3 gap-2">
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