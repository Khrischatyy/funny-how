<script setup lang="ts">
import {Popup} from "~/src/shared/ui/components";
import {ref} from "vue";
import { FInputClassic} from "~/src/shared/ui/common";
import {HoursChoose} from "~/src/widgets/HoursChoose";
import {PriceChoose} from "~/src/widgets/PriceChoose";
import {BadgesChoose} from "~/src/widgets/BadgesChoose";
import {EquipmentChoose} from "~/src/widgets/EquipmentChoose";
import {AddStudioButton} from "~/src/features/addStudio";

const props = withDefaults(defineProps<{
  showPopup: boolean
}>(), {
  showPopup: false
});

const emit = defineEmits(['togglePopup', 'closePopup']);

const studioForm = ref({
  name: '',
  address: '',
  description: '',
  hours: '',
  price: 0,
  logo: '',
  badges: [],
  equipment: []
});
const togglePopup = () => {
  emit('togglePopup');
}
const closePopup = () => {
  emit('closePopup');
}
</script>

<template>
  <Popup :title="'Add Studio'" :open="showPopup" @close="closePopup">
    <template #header>
      <div class="input-container flex gap-2">
        <div class="logo">
          <FInputClassic placeholder="Logo" type="file" v-model="studioForm.logo" />
        </div>
        <div class="name">
          <FInputClassic placeholder="Name" v-model="studioForm.name" />
        </div>
      </div>
    </template>
    <template #body>
      <div class="photos mb-5">
        <div class="grid-cols-1 grid-rows-3 sm:grid-cols-[1fr_1fr_250px] sm:grid-rows-1 grid gap-5">
          <div class="cover-photo max-h-60">
            <img src="https://via.placeholder.com/800x600" alt="cover photo" class="w-full h-full object-cover rounded-[10px]"/>
          </div>
          <div class="grid grid-cols-3 grid-rows-2 gap-5 max-h-60">
            <img v-for="photo in 6" src="https://via.placeholder.com/300" alt="cover photo" class="w-full h-full object-cover rounded-[10px]"/>
          </div>
          <div class="add-photo">
            <AddStudioButton title="Add Photo" @click="togglePopup" />
          </div>
        </div>
      </div>
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <div class="w-full flex flex-col gap-5">
          <div class="name w-full flex-col flex gap-1.5">
            <div class="text-white text-sm font-normal tracking-wide opacity-20">Address</div>
            <FInputClassic placeholder="Address" v-model="studioForm.address"/>
          </div>
          <div class="description w-full flex-col flex gap-1.5">
            <div class="text-white text-sm font-normal tracking-wide opacity-20">Description</div>
            <FInputClassic placeholder="Description" v-model="studioForm.description"/>
          </div>
          <div class="price w-full flex-col flex gap-1.5">
            <PriceChoose v-model="studioForm.price"/>
          </div>
        </div>
        <div class="w-full flex-col flex gap-1.5">
          <div class="equipment w-full flex-col flex gap-1.5">
            <EquipmentChoose v-model="studioForm.equipment"/>
          </div>
          <div class="badgees w-full flex-col flex gap-1.5">
            <BadgesChoose v-model="studioForm.badges"/>
          </div>
        </div>
        <div class="w-full">
          <div class="hours w-full flex-col flex gap-1.5">
            <HoursChoose v-model="studioForm.hours"/>
          </div>
        </div>
      </div>
    </template>
  </Popup>
</template>

<style scoped lang="scss">

</style>