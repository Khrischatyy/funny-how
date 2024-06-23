<template>
  <div>
    <div v-for="field in store.inputFields" class="flex-col justify-start items-start gap-1.5 flex w-full">
      <div class="max-w-96 w-full justify-between items-start inline-flex">
        <div class="text-white text-sm font-normal tracking-wide">{{ field.title }}</div>
        <div class="hidden text-right text-red-500 text-sm font-normal tracking-wide">Error message</div>
      </div>
      <div class="justify-start items-center gap-2.5 inline-flex w-full">
        <input :name="field.name" v-model="store.inputValues[field.name]" :type="field.type" class="max-w-96 w-full h-11 px-3.5 py-7 outline-none rounded-[10px] focus:border-white border border-white border-opacity-20 bg-transparent text-white text-sm font-medium tracking-wide"/>
      </div>
    </div>
    <div class="justify-center items-center gap-2.5 inline-flex w-full">
      <button @click="createAccount()" :disabled="isLoading" class="max-w-96 w-full h-11 p-3.5 hover:opacity-90 bg-white rounded-[10px] text-neutral-900 text-sm font-medium tracking-wide">Submit {{isLoading ? 'loading...' : ''}}</button>
    </div>
    <div class="max-w-96 w-full h-11 p-3.5 justify-between items-center gap-2.5 inline-flex">
      <button @click="$emit('stepUpdate', 'auth')" class="w-full flex justify-center h-11 p-3.5 hover:opacity-70 rounded-[10px] text-white text-sm font-medium tracking-wide">
        Back to Sign In
      </button>
    </div>
  </div>
</template>
<script setup lang="ts">
import { IconLeft, IconMic, IconUser } from "~/src/shared/ui/common";
import {type formValues, useCreateAccountFormStore} from "~/src/entities/RegistrationForms";
import {useRuntimeConfig} from "#imports";
import axios from "axios";
import {ref} from "vue";
import {useSessionStore} from "~/src/entities/Session";
import {navigateTo} from "nuxt/app";

const store = useCreateAccountFormStore();
const isLoading = ref(false)
const emit = defineEmits(['stepUpdate'])
function createAccount(){
  const config = useRuntimeConfig()
  isLoading.value = true;
  let requestConfig = {
    method: 'post',
    credentials: true,
    url: `${config.public.apiBaseClient}/auth/register`,
    data: store.inputValues,
    headers: {
      'Accept': 'application/json'
    }
  };
  axios.defaults.headers.common['X-Api-Client'] = `web`
  axios.request(requestConfig)
      .then((response) => {
        if(response.data.token){
          let session = useSessionStore()
          session.setAccessToken(response.data.token)
          session.setAuthorized(true)
          session.setUserRole(response.data.role)
          if(session.userRole == 'studio_owner'){
            navigateTo('/create')
            session.setIsLoading(false)
          } else {
            window.location.reload();
          }
        }

      })
      .catch((error) => {
        console.log(error);
      });
}
</script>

<style lang="scss" scoped>

</style>