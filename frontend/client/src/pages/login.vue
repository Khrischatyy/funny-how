<script setup lang="ts">

import { CreateForm } from "~/src/features/Register/create"
import BrandingLogo from "~/src/shared/ui/branding/BrandingLogo.vue";
import {useHead} from "@unhead/vue";
import Particles from "~/src/shared/ui/components/Particles/Particles.vue";
import { useRuntimeConfig } from '#imports'
import axios from "axios";
import { useSessionStore } from "~/src/entities/Session";
import {nextTick, onMounted, reactive, ref, watch, watchEffect} from "vue";
import {IconUser, IconMic, IconElipse, IconCopyright, IconLine, IconLeft, IconCheck} from "~/src/shared/ui/common/Icon";
import { Loader } from '@googlemaps/js-api-loader';
import {BrandingLogoSample, BrandingLogoSmall} from "~/src/shared/ui/branding";
import {navigateTo} from "nuxt/app";
import CreateAccountForm from "~/src/features/Register/createAccount/ui/CreateAccountForm.vue";
import {useApi} from "~/src/lib/api";

useHead({
  title: 'Funny How – Book a Session Time',
  meta: [
    { name: 'Funny How', content: 'Book A Studio Time' }
  ],
})

const isLoading = ref(false)

const step = ref('auth')
const forms = reactive([
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
    name: 'setup', title: 'Set-Up',
    formValuesStorage: {
      address: '',
      about: '',
      gender: '',
      place: {},
      studio_name: '',
    },
    errors: [],
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
  name: string;
  title: string;
  formValuesStorage? : {
    email? : string;
    password? : string;
    name? : string;
    password_confirmation? : string;
    role? : string;
  };
  errors? : any[];
  inputFields? : {name: string; title: string; type: string;}[];
}
function getAuthTitleByName(name: string) {
  return forms.find((form: Form) => form.name === name)?.title
}

function chooseRole(role: string) {
  let roleValue = 'create_account';
  forms.find((form: Form) => form.name === 'create_account').formValuesStorage.role = role
  step.value = roleValue
}

function chooseGender(gender: string) {
  forms.find((form: Form) => form.name === 'setup').formValuesStorage.gender = gender

}

function getFormValues(name: string) {
  return forms.find((form: Form) => form.name === name)?.formValuesStorage
}
function setFormValue(name: string, fieldName: string, value: any) {
  const form = forms.find((form: Form) => form.name === name);
  if (form) {
    form.formValuesStorage[fieldName] = value;
  } else {
    console.error(`Form with name '${name}' not found.`);
  }
}


function getFormErrors(name: string) {
  return forms.find((form: Form) => form.name === name)?.errors
}

function isError(form:string , field: string){
  let formErrors = getFormErrors(form);

  return formErrors.hasOwnProperty(field) ? formErrors[field][0] : false;
}

function getFormFields(name: string) {
  return forms.find((form: Form) => form.name === name)?.inputFields
}

const steps = ref([
  {name: 'auth', title: 'Sign In', order: 1},
  {name: 'create', title: 'Sign Up', order: 2},
  {name: 'create', title: 'Personal Info', order: 3},
  {name: 'forgot', title: 'Forgot Password', order: 3}
])

const session = ref()
const place = ref()
onMounted(async () => {
  const config = useRuntimeConfig()
  session.value = useSessionStore()
  if(session.value.isAuthorized){
    navigateTo('/')
  }
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
    url: `${config.public.apiBaseClient}/auth/register`,
    data: getFormValues('create_account'),
    headers: {
      'Accept': 'application/json'
    }
  };
  axios.defaults.headers.common['X-Api-Client'] = `web`
  axios.request(requestConfig)
      .then((response) => {
        if(response.data.token){
          session.value.setAccessToken(response.data.token)
          session.value.setUserRole(response.data.role)
          session.value.setAuthorized(true)
          isLoading.value = false;
          step.value = 'account'
        }
      })
      .catch((error) => {
        console.log(error);
      });
}


async function authForm() {
  const config = useRuntimeConfig();
  const session = useSessionStore();
  session.setIsLoading(true);

  const { post } = useApi({
    url: `/auth/login`,
    auth: false
  });

  try {
    const data = await post(getFormValues('auth'));

    if (data.token) {
      session.setAccessToken(data.token);
      session.setAuthorized(true);
      session.setUserRole(data.role);

      if (data.role === 'studio_owner' && !data.has_company) {
        navigateTo('/create');
      } else {
        window.location.reload();
      }
    }
  } catch (error) {
    let errors = getFormErrors('auth');
    if (error.response && error.response.data && error.response.data.errors) {
      Object.assign(errors, error.response.data.errors);
    } else {
      console.error('Login error:', error);
    }
  } finally {
    session.setIsLoading(false);
  }
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

            <CreateForm @stepUpdate="step = $event" v-if="step == 'create' && !session?.isAuthorized" ref="create" :class="step == 'create' ? 'translate-x-[0px] duration-[700ms]' : 'ml-96 translate-x-96 duration-700 opacity-0'" class="relative w-full flex-col justify-start items-center gap-2.5 flex"/>

            <CreateAccountForm @stepUpdate="step = $event" v-if="step == 'create_account' && !session?.isAuthorized" ref="create_account" :class="step == 'create_account' ? 'translate-x-[0px] duration-[700ms]' : 'opacity-0 translate-x-96 duration-700'" class="relative w-full flex-col justify-start items-center gap-2.5 flex"/>


          </div>
        </div>
      </div>
      <div v-if="!session?.isAuthorized" class="fixed left-0 bottom-0 w-full pointer-events-none">
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