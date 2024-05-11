<template>
  <div>
    <div class="flex-col justify-start items-start gap-1.5 flex">
      <div class="w-96 justify-between items-start inline-flex">
        <div class="text-white text-sm font-normal tracking-wide">Your role</div>
        <div class="hidden text-right text-red-500 text-sm font-normal tracking-wide">Error message</div>
      </div>
      <div class="justify-start items-center w-full gap-2.5 inline-flex">
        <button @click="updateField('role','user')" :class="createAccountForm.inputValues.role == 'user' ? 'bg-white text-neutral-900 ' : 'text-white'" class="w-full h-11 p-3.5 hover:opacity-90 rounded-[10px] border text-sm font-medium tracking-wide flex items-center justify-center">
          <icon-user :icon-color="createAccountForm.inputValues.role == 'user' ? '#171717' : '#ffffff'" class="w-4 h-4 mr-2"/>
          Artist
        </button>
        <button @click="updateField('role','studio_owner')" :class="createAccountForm.inputValues.role == 'studio_owner' ? 'bg-white text-neutral-900 ' : 'text-white'" class="w-full h-11 p-3.5 hover:opacity-90 rounded-[10px] border text-sm font-medium tracking-wide flex items-center justify-center">
          <icon-mic :icon-color="createAccountForm.inputValues.role == 'studio_owner' ? '#171717' : '#ffffff'" class="w-4 h-4 mr-2"/>
          Studio Owner
        </button>
      </div>
    </div>
    <div class="w-96 h-11 p-3.5 justify-between items-center gap-2.5 inline-flex">

      <button @click="$emit('stepUpdate', 'auth')" class="w-full flex justify-center items-center h-11 p-3.5 hover:opacity-70 rounded-[10px] text-white text-sm font-medium tracking-wide">
        <icon-left/>
        Back to Sign In
      </button>
    </div>
  </div>
</template>
<script setup lang="ts">
import {IconLeft, IconMic, IconRight, IconUser} from "~/src/shared/ui/common";
import {type formValues, useCreateAccountFormStore} from "~/src/entities/RegistrationForms";

const createAccountForm = useCreateAccountFormStore()
const emit = defineEmits(['stepUpdate'])

const updateField = (field: keyof formValues, value: string) => {
  createAccountForm.updateRole(value)
  emit('stepUpdate', 'create_account')
}
</script>

<style lang="scss" scoped>

</style>