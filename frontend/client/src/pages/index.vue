<script setup lang="ts">

import BrandingLogo from "~/src/shared/ui/branding/BrandingLogo.vue";
import {useHead} from "@unhead/vue";
import Particles from "~/src/shared/ui/components/Particles/Particles.vue";
import { useRuntimeConfig } from '#imports'
import axios from "axios";
import { useSessionStore } from "~/src/entities/Session";
import {onMounted, ref} from "vue";
import {IconUser, IconMic, IconElipse, IconCopyright, IconLine, IconLeft, IconCheck} from "~/src/shared/ui/common/Icon";

useHead({
  title: 'Funny How – Book a Session Time',
  meta: [
    { name: 'Funny How', content: 'Book A Studio Time' }
  ],
})

const isLoading = ref(false)

const step = ref('auth')
const forms = ref([
  {name: 'auth', title: 'Sign In',
    formValuesStorage: {
      email: '',
      password: ''
    },
    errors: [],
  },
  {
    name: 'create_account', title: 'Sign Up',
    formValuesStorage: {
      name: '',
      email: '',
      password: '',
      password_confirmation: '',
      role: ''
    },
    errors: [],
    inputFields: [
      {name: 'name', title: 'Name', type: 'text'},
      {name: 'email', title: 'Email', type: 'email'},
      {name: 'password', title: 'Password', type: 'password'},
      {name: 'password_confirmation', title: 'Confirm Password', type: 'password'}]
  },
  {
    name: 'forgot', title: 'Forgot Password',
    errors: [],
    formValuesStorage: {
      email: ''
    }
  }
])

type Form = {
  name: string,
  title: string
}
function getAuthTitleByName(name: string) {
  return forms.value.find((form: Form) => form.name === name)?.title
}

function chooseRole(role: string) {
  let roleValue = 'create_account';
  forms.value.find((form: Form) => form.name === 'create_account').formValuesStorage.role = roleValue
  step.value = roleValue
}

function getFormValues(name: string) {
  return forms.value.find((form: Form) => form.name === name)?.formValuesStorage
}

function getFormErrors(name: string) {
  return forms.value.find((form: Form) => form.name === name)?.errors
}

function isError(form:string , field: string){
  let formErrors = getFormErrors(form);
  console.log('formErrors.hasOwnProperty(field)', formErrors.hasOwnProperty(field))
  return formErrors.hasOwnProperty(field) ? formErrors[field][0] : false;
}

function getFormFields(name: string) {
  return forms.value.find((form: Form) => form.name === name)?.inputFields
}

const steps = ref([
  {name: 'auth', title: 'Sign In', order: 1},
  {name: 'create', title: 'Sign Up', order: 2},
  {name: 'create', title: 'Personal Info', order: 3},
  {name: 'forgot', title: 'Forgot Password', order: 3}
])

const session = ref()

onMounted(() => {
  session.value = useSessionStore()
})

function createForm() {
  step.value = 'create'
  return
}

function createAccount(){
  const config = useRuntimeConfig()
  isLoading.value = true;
  let requestConfig = {
    method: 'post',
    credentials: true,
    url: `${config.public.apiBase}/v1/auth/register`,
    data: getFormValues('create_account'),
    headers: {
      'Accept': 'application/json'
    }
  };
  axios.defaults.headers.common['X-Api-Client'] = `web`
  axios.request(requestConfig)
      .then((response) => {
        session.value.setAccessToken(response.data.token)
        session.value.setAuthorized(true)
        isLoading.value = false;
        step.value = 'account'
      })
      .catch((error) => {
        console.log(error);
      });
}

function signOut() {
  session.value.logout()
  step.value = 'auth'
}

function authForm() {
  const config = useRuntimeConfig()

  let requestConfig = {
    method: 'post',
    credentials: true,
    url: `${config.public.apiBase}/v1/auth/login`,
    data: getFormValues('auth'),
    headers: {
      'Accept': 'application/json'
    }
  };
  axios.defaults.headers.common['X-Api-Client'] = `web`
  axios.request(requestConfig)
      .then((response) => {

        session.value.setAccessToken(response.data.token)
        session.value.setAuthorized(true)
      })
      .catch((error) => {
        let errors = getFormErrors('auth');
        Object.assign(errors, error.response.data.errors)
      });

}

function verifyUser() {
  const config = useRuntimeConfig()

  let requestConfig = {
    method: 'post',
    credentials: true,
    url: `${config.public.apiBase}/v1/auth/email/verify/2/07573a3c93dad6e7c283ea427d9f6cf94cf22783?expires=1713901523&signature=fc718ddc61b7cebdbe3dd9eeafb546e2505a5725468334a93e9ba8bf6f3bb884`,
    headers: {
      'Accept': 'application/json'
    }
  };
  axios.defaults.headers.common['X-Api-Client'] = `web`
  axios.request(requestConfig)
      .then((response) => {
        console.log(JSON.stringify(response.data));
      })
      .catch((error) => {
        console.log(error);
      });

}

function getForm() {
  $fetch('/api/user/token/', {
    method: 'POST',
    body: {
      "email": "beilec@yandex.ru",
      "password": "12345678"
    },
    header:{

    }
  }).then(data => {
    let token = data.token ?? '';
    if(token){

    }
  })
}
</script>

<template>
  <div :class="step == 'auth' && !session?.isAuthorized ? 'lg:grid-cols-[486px_1fr] duration-700' : 'lg:grid-cols-[0px_1fr] duration-[700ms]'" class="ease-in-out grid min-h-[100vh] h-full">

    <div :class="step == 'auth' && !session?.isAuthorized  ? 'w-full translate-x-[0px] duration-[700ms]' : 'translate-x-[-486px] duration-300'" class="z-10 ease-in-out bg-black hidden lg:block text-white px-[50px] py-[60px] min-h-[100vh]">
      <div class="w-full mt-10 h-full flex-col justify-between items-start gap-7 inline-flex">
        <div class="description">
          <BrandingLogo/>
          <div class="description mt-10 font-bold text-xl leading-8">
            Empowering Artists Everywhere —<br/>Book, Record, and Create with Ease
          </div>
          <div class="points mt-10">
            <div class="w-full flex-col justify-start items-start gap-7 inline-flex">
              <div class="justify-start items-start gap-5 inline-flex">
                <div class="w-8 h-8 relative">
                  <div class="left-0 top-0 absolute"><IconCheck/></div>
                </div>
                <div class="flex-col justify-start items-start gap-2.5 inline-flex">
                  <div class="text-white text-base font-bold tracking-wide">Seamless Scheduling</div>
                  <div class="w-80 text-white text-sm font-normal tracking-wide">Our intuitive calendar interface makes it easy to see studio availability and schedule sessions without back-and-forth communication.</div>
                </div>
              </div>
              <div class="justify-start items-start gap-5 inline-flex">
                <div class="w-8 h-8 relative">
                  <div class="left-0 top-0 absolute"><IconCheck/></div>
                </div>
                <div class="flex-col justify-start items-start gap-2.5 inline-flex">
                  <div class="text-white text-base font-bold tracking-wide">Integrated Payment System</div>
                  <div class="w-80 text-white text-sm font-normal tracking-wide">Secure and straightforward in-app payments to reserve your studio time without hassle.</div>
                </div>
              </div>
              <div class="justify-start items-start gap-5 inline-flex">
                <div class="w-8 h-8 relative">
                  <div class="left-0 top-0 absolute"><IconCheck/></div>
                </div>
                <div class="flex-col justify-start items-start gap-2.5 inline-flex">
                  <div class="text-white text-base font-bold tracking-wide">Collaborative Planning Tools</div>
                  <div class="w-80 text-white text-sm font-normal tracking-wide">Coordinate with producers, engineers, and artists within the app to ensure everyone is prepared for the session.</div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="footer mt-10 mb-10 ">
          <div class="w-full flex-col justify-between items-start gap-7 mb-5 inline-flex">
            <div class="w-full justify-between items-start gap-7 inline-flex">
              <div class="text-white text-base font-normal tracking-wide">Contact us</div>
              <div class="text-white text-base font-normal tracking-wide">Terms of Service</div>
              <div class="text-white text-base font-normal tracking-wide">About us</div>
            </div>
          </div>
          <div class="w-full h-5 justify-start items-center gap-1.5 inline-flex">
            <div class="text-white text-base font-semibold tracking-wide">2024</div>
            <div class="w-5 h-5 relative"><IconCopyright/></div>
            <div class="text-white text-base font-semibold tracking-wide">Funny-how LLC</div>
          </div>
        </div>
      </div>
    </div>

    <div class="auth-panel bg-[#000000] relative overflow-hidden">
      <div class="w-full h-full grid items-center justify-center mb-10">
        <div class="relative bottom-[10%] flex-col justify-start items-center gap-7 inline-flex">
          <BrandingLogo class="lg:hidden mb-10"/>
          <div :class="step == 'auth' ? 'translate-x-[0px] duration-[700ms] opacity-0' : '-translate-x-96/2 duration-700'" class="breadcrumbs text-white text-sm font-normal tracking-wide flex gap-1.5 justify-center items-center">
            <icon-elipse :class="step == 'create' ? 'opacity-100' : 'opacity-20'" class="h-4"/>
            <button :class="step == 'create' ? 'opacity-100' : 'opacity-20'">Your Role</button>
            <icon-line :class="step == 'create' ? 'opacity-100' : 'opacity-20'" class="h-2 only-desktop"/>
            <icon-elipse :class="step == 'create_account' ? 'opacity-100' : 'opacity-20'" class="h-4"/>
            <button :class="step == 'create_account' ? 'opacity-100' : 'opacity-20'"> Personal Info </button>
            <icon-line :class="step == 'create_account' ? 'opacity-100' : 'opacity-20'" class="h-2 only-desktop"/>
            <icon-elipse :class="step == 'price_plans' ? 'opacity-100' : 'opacity-20'" class="h-4"/>
            <button :class="step == 'price_plans' ? 'opacity-100' : 'opacity-20'"> Price Plans</button>
            <icon-line :class="step == 'price_plans' ? 'opacity-100' : 'opacity-20'" class="h-2 only-desktop"/>
            <icon-elipse :class="step == 'add_studio' ? 'opacity-100' : 'opacity-20'" class="h-4"/>
            <button :class="step == 'add_studio' ? 'opacity-100' : 'opacity-20'" > Add Studio </button>
          </div>
          <div v-if="!session?.isAuthorized" class="relative text-white text-3xl font-bold tracking-wider">{{getAuthTitleByName(step)}}</div>
          <div class="relative h-full min-h-[500px] forms flex justify-start items-center">

            <div v-if="step == 'auth' && !session?.isAuthorized" ref="auth" :class="step == 'auth' ? 'translate-x-[0px] duration-[700ms]' : 'opacity-0 -translate-x-96 duration-700'" class="relative w-full flex-col justify-start items-center gap-2.5 flex">
              <div class="flex-col justify-start items-start gap-1.5 flex">
                <div class="w-96 justify-between items-start inline-flex">
                  <div class="text-white text-sm font-normal tracking-wide">E-mail</div>
                  <div :class="isError('auth', 'email') ? '' : 'hidden'" class="text-right text-red-500 text-sm font-normal tracking-wide">
                    {{ isError('auth', 'email') }}
                  </div>
                </div>
                <div class="justify-start items-center gap-2.5 inline-flex">
                  <input name="email" type="email" v-model="getFormValues('auth')['email']" :class="{'border-red': isError('auth', 'email')}" class="w-96 h-11 px-3.5 py-7 outline-none rounded-[10px] focus:border-white border border-white border-opacity-20 bg-transparent text-white text-sm font-medium tracking-wide"/>
                </div>
              </div>
              <div class="flex-col justify-start items-start gap-1.5 flex">
                <div class="w-96 justify-between items-start inline-flex">
                  <div class="text-white text-sm font-normal tracking-wide">Password</div>
                  <div :class="isError('auth', 'email') ? '' : 'hidden'" class=" text-right text-red-500 text-sm font-normal tracking-wide">{{ isError('auth', 'email') }}</div>
                </div>
                <div class="justify-start items-center gap-2.5 inline-flex">
                  <input name="password" type="password" v-model="getFormValues('auth')['password']" :class="{'border-red': isError('auth', 'email') || isError('auth', 'password')}" class="w-96 h-11 px-3.5 py-7 outline-none rounded-[10px] focus:border-white border border-white border-opacity-20 bg-transparent text-white text-sm font-medium tracking-wide"/>
                </div>
              </div>
              <div class="w-96 h-7 rounded-lg justify-start items-center gap-2.5 inline-flex">
                <label class="checkbox-wrapper flex">
                  <div class="w-4 h-4 justify-center items-center flex">
                    <input type="checkbox" class="hidden" />
                    <div class="w-4 h-4 rounded-[3px] border border-white custom-checkbox"></div>
                  </div>
                  <div class="text-white text-sm font-normal tracking-wide">Stay signed in for a month</div>
                </label>
              </div>
              <div class="justify-center items-center gap-2.5 inline-flex">
                <button @click="authForm()" class="w-96 h-11 p-3.5 hover:opacity-90 bg-white rounded-[10px] text-neutral-900 text-sm font-medium tracking-wide">Sign In</button>
              </div>
              <div class="justify-center items-center gap-2.5 inline-flex">
                <button @click="createForm()" class="w-96 h-11 p-3.5 hover:opacity-90 rounded-[10px] text-white border text-sm font-medium tracking-wide">Don’t have an account? Create account</button>
              </div>
              <div class="w-96 h-11 p-3.5 justify-center items-center gap-2.5 inline-flex">
                <button  class="w-96 h-11 p-3.5 hover:opacity-90 rounded-[10px] text-white text-sm font-medium tracking-wide">Forgot password?</button>
              </div>
            </div>

            <div v-if="step == 'create' && !session?.isAuthorized" ref="create" :class="step == 'create' ? 'translate-x-[0px] duration-[700ms]' : 'ml-96 translate-x-96 duration-700 opacity-0'" class="relative w-full flex-col justify-start items-center gap-2.5 flex">
              <div class="flex-col justify-start items-start gap-1.5 flex">
                <div class="w-96 justify-between items-start inline-flex">
                  <div class="text-white text-sm font-normal tracking-wide">Your role</div>
                  <div class="hidden text-right text-red-500 text-sm font-normal tracking-wide">Error message</div>
                </div>
                <div class="justify-start items-center w-full gap-2.5 inline-flex">
                  <button @click="chooseRole('artist')" :class="getFormValues('create')?.role == 'artist' ? 'bg-white text-neutral-900 ' : 'text-white'" class="w-full h-11 p-3.5 hover:opacity-90 rounded-[10px] border text-sm font-medium tracking-wide flex items-center justify-center">
                    <icon-user :icon-color="getFormValues('create')?.role == 'artist' ? '#171717' : '#ffffff'" class="w-4 h-4 mr-2"/>
                    Artist
                  </button>
                  <button @click="chooseRole('owner')" :class="getFormValues('create')?.role == 'owner' ? 'bg-white text-neutral-900 ' : 'text-white'" class="w-full h-11 p-3.5 hover:opacity-90 rounded-[10px] border text-sm font-medium tracking-wide flex items-center justify-center">
                    <icon-mic :icon-color="getFormValues('create')?.role == 'owner' ? '#171717' : '#ffffff'" class="w-4 h-4 mr-2"/>
                    Studio Owner
                  </button>
                </div>
              </div>
              <div class="w-96 h-11 p-3.5 justify-between items-center gap-2.5 inline-flex">
                <button disabled class="w-full flex justify-start h-11 p-3.5 disabled:opacity-20 hover:opacity-70 rounded-[10px] text-white text-sm font-medium tracking-wide">
                  <icon-left class="w-4 h-4 mr-2"/>Back
                </button>
                <button class="w-full flex justify-end h-11 p-3.5 hover:opacity-70 rounded-[10px] text-white text-sm font-medium tracking-wide">
                  Next
                  <icon-left class="w-4 h-4 ml-2 rotate-180"/>
                </button>
              </div>
              <div class="w-96 h-11 p-3.5 justify-between items-center gap-2.5 inline-flex">
                <button @click="step = 'auth'" class="w-full flex justify-center h-11 p-3.5 hover:opacity-70 rounded-[10px] text-white text-sm font-medium tracking-wide">
                  Back to Sign In
                </button>
              </div>
            </div>

            <div v-if="step == 'create_account' && !session?.isAuthorized" ref="create_account" :class="step == 'create_account' ? 'translate-x-[0px] duration-[700ms]' : 'opacity-0 translate-x-96 duration-700'" class="relative w-full flex-col justify-start items-center gap-2.5 flex">
              <div v-for="field in getFormFields('create_account')" class="flex-col justify-start items-start gap-1.5 flex">
                <div class="w-96 justify-between items-start inline-flex">
                  <div class="text-white text-sm font-normal tracking-wide">{{ field.title }}</div>
                  <div class="hidden text-right text-red-500 text-sm font-normal tracking-wide">Error message</div>
                </div>
                <div class="justify-start items-center gap-2.5 inline-flex">
                  <input :name="field.name" v-model="getFormValues('create_account')[field.name]" :type="field.type" class="w-96 h-11 px-3.5 py-7 outline-none rounded-[10px] focus:border-white border border-white border-opacity-20 bg-transparent text-white text-sm font-medium tracking-wide"/>
                </div>
              </div>
              <div class="justify-center items-center gap-2.5 inline-flex">
                <button @click="createAccount()" :disabled="isLoading" class="w-96 h-11 p-3.5 hover:opacity-90 bg-white rounded-[10px] text-neutral-900 text-sm font-medium tracking-wide">Submit {{isLoading ? 'loading...' : ''}}</button>
              </div>
              <div class="w-96 h-11 p-3.5 justify-between items-center gap-2.5 inline-flex">
                <button @click="step = 'auth'" class="w-full flex justify-center h-11 p-3.5 hover:opacity-70 rounded-[10px] text-white text-sm font-medium tracking-wide">
                  Back to Sign In
                </button>
              </div>
            </div>

            <div v-if="session?.isAuthorized" ref="authorized" :class="session?.isAuthorized ? 'translate-x-[0px] duration-[700ms]' : 'opacity-0 -translate-x-96 duration-700'" class="relative w-full flex-col justify-start items-center gap-2.5 flex">
              <BrandingLogo class="mb-10"/>
              <div class="flex-col justify-start items-start gap-1.5 flex">
                <div class="w-96 justify-between items-start inline-flex">
                  <div class="text-white text-sm font-normal tracking-wide">Hey, {{session?.accessToken}}</div>
                </div>
              </div>
              <div class="justify-center items-center gap-2.5 inline-flex">
                <button @click="signOut()" class="w-96 h-11 p-3.5 hover:opacity-90 bg-white rounded-[10px] text-neutral-900 text-sm font-medium tracking-wide">Sign Out</button>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="fixed left-0 bottom-0 w-full pointer-events-none">
        <client-only>
          <Particles/>
        </client-only>
      </div>
    </div>
  </div>
</template>

<style scoped lang="scss">

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

</style>