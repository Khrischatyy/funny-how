<script setup lang="ts">
import {Popup} from "~/src/shared/ui/components";
import {ref} from "vue";
import { FInputClassic} from "~/src/shared/ui/common";

const props = withDefaults(defineProps<{
  showPopup: boolean
}>(), {
  showPopup: false
});

const emit = defineEmits(['togglePopup']);

const studioForm = ref({
  name: '',
  address: '',
  description: '',
  hours: '',
  price: 0,
  logo: ''
});
const togglePopup = () => {
  emit('togglePopup');
}
</script>

<template>
  <Popup :title="'My Popup'" :open="showPopup" @close="togglePopup">
    <template #header>
      <div class="input-container flex gap-2">
        <div class="logo">
          <FInputClassic label="Logo" type="file" v-model="studioForm.logo" />
        </div>
        <div class="name">
          <FInputClassic label="Name" v-model="studioForm.name" />
        </div>
      </div>
    </template>
    <template #body>
      <div class="flex flex-col gap-5">
        <div class="name flex-col flex gap-1.5">
          <div class="text-white text-sm font-normal tracking-wide opacity-20">Address</div>
          <FInputClassic label="Address" v-model="studioForm.address"/>
        </div>
        <div class="description flex-col flex gap-1.5">
          <div class="text-white text-sm font-normal tracking-wide opacity-20">Description</div>
          <FInputClassic label="Description" v-model="studioForm.description"/>
        </div>
        <div class="description flex-col flex gap-1.5">
          <div class="text-white text-sm font-normal tracking-wide opacity-20">Choose Hours</div>

        </div>
      </div>
    </template>
  </Popup>
</template>

<style scoped lang="scss">

</style>