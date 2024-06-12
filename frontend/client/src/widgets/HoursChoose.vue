<script setup lang="ts">
import { ref, watch } from "vue";
import {FInputClassic, FSelectClassic, IconTrash} from "~/src/shared/ui/common";


interface Price {
  id?: number;
  total_price: string;
  hours: number;
  is_enabled: boolean;
}

const props = defineProps<{
  prices: Price[];
  pricesList: { hours: number }[];
  onAddPrice: () => void;
  onDeletePrice: (price: Price) => void;
  onSendPrice: (price: Price) => void;
  onAddSamplePrices: () => void;
}>();

function isError(form: string, field: string): boolean {
  let formErrors: Record<string, any> = {};
  // Replace with actual error fetching logic
  return formErrors.hasOwnProperty(field) ? formErrors[field][0] : false;
}
</script>

<template>
  <div>
    <div class="flex-col justify-start items-start gap-1.5 flex">
      <div class="w-96 justify-between items-start inline-flex">
        <div class="text-neutral-700 text-sm font-normal tracking-wide">Price</div>
        <div :class="isError('setup', 'studio_name') ? '' : 'hidden'" class=" text-right text-red-500 text-sm font-normal tracking-wide">{{
            isError('setup', 'studio_name')
          }}</div>
      </div>
      <div class="flex-col mb-1 justify-center items-center gap-1.5 flex">
        <div class="justify-center items-center gap-2.5 inline-flex">
          <button @click="props.onAddPrice()" class="w-96 h-11 p-3.5 hover:opacity-90 bg-transparent rounded-[10px] text-white border-white border text-sm font-medium tracking-wide">Add price</button>
        </div>
      </div>
      <div class="flex-col mb-1 justify-center items-center gap-1.5 flex">
        <div class="justify-center items-center gap-2.5 inline-flex">
          <button @click="props.onAddSamplePrices()" class="w-96 h-11 p-3.5 hover:opacity-90 bg-transparent rounded-[10px] text-white border-white border text-sm font-medium tracking-wide">Replace With Sample Data</button>
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
      <div v-for="price in props.prices" :key="price.hours" class="animate__animated animate__fadeInRight relative w-full max-w-96 flex items-center gap-1.5 justify-between">
        <label class="checkbox-wrapper flex">
          <div class="w-5 h-5 justify-center items-center flex">
            <input @change="props.onSendPrice(price)" v-model="price.is_enabled" type="checkbox" class="hidden" />
            <div class="w-5 h-5 rounded-[5px] border border-white custom-checkbox"></div>
          </div>
        </label>
        <div class="relative w-full flex items-center">
          <div class="flex items-center">
            <FSelectClassic
                :options="props.pricesList.map(pr => ({ id: pr.hours, name: `${pr.hours} hours` }))"
                v-model="price.hours"
                @change="props.onSendPrice(price)"
                label="hours"
            />
          </div>
        </div>
        <div class="relative w-full flex items-center">
          <FInputClassic
              v-model="price.total_price"
              @blur="props.onSendPrice(price)"
              type="number"
              label="Price"
          />
          <span class="absolute right-2 text-neutral-700 cursor-pointer">$</span>
        </div>
        <div @click="props.onDeletePrice(price)" class="relative cursor-pointer flex items-center">
          <IconTrash/>
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
