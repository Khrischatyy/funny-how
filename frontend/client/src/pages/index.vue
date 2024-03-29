<script setup lang="ts">

import BrandingLogo from "~/src/shared/ui/branding/BrandingLogo.vue";
import {useHead} from "@unhead/vue";
import IconCopyright from "~/src/shared/ui/common/Icon/IconCopyright.vue";
import Particles from "~/src/shared/ui/components/Particles/Particles.vue";
import IconCheck from "~/src/shared/ui/common/Icon/IconCheck.vue";

useHead({
  title: 'Funny How – Book a Session Time',
  meta: [
    { name: 'Funny How', content: 'Book A Studio Time' }
  ],
})

function createForm() {
  $fetch('/api/user/create/', {
    method: 'POST',
    body: {
      "email": "user@example.com",
      "password": "string",
      "name": "string"
    }
  })
}

function authForm() {
  $fetch('/api/user/token/', {
    method: 'POST',
    body: {
      "email": "beilec@yandex.ru",
      "password": "12345678"
    }
  }).then(data => {
    let token = data.token ?? '';
    if(token){
      console.log('token', token)
    }
  })
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
  <div class="grid lg:grid-cols-[486px_1fr] min-h-[100vh] h-full">
    <div class="bg-black hidden lg:block text-white px-[50px] py-[60px] min-h-[100vh]">
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
    <div class="bg-[#000000] relative overflow-hidden">
      <div class="w-full h-full grid items-center justify-center mb-10">
        <div class="relative bottom-[10%] flex-col justify-start items-center gap-7 inline-flex">
          <BrandingLogo class="lg:hidden mb-10"/>
          <div class="text-white text-3xl font-bold tracking-wider">Sign In</div>
          <div class="flex-col justify-start items-center gap-2.5 flex">
            <div class="flex-col justify-start items-start gap-1.5 flex">
              <div class="w-96 justify-between items-start inline-flex">
                <div class="text-white text-sm font-normal tracking-wide">E-mail</div>
                <div class="hidden text-right text-red-500 text-sm font-normal tracking-wide">Error message</div>
              </div>
              <div class="justify-start items-center gap-2.5 inline-flex">
                <input class="w-96 h-11 px-3.5 py-7 outline-none rounded-[10px] focus:border-white border border-white border-opacity-20 bg-transparent text-white text-sm font-medium tracking-wide"/>
              </div>
            </div>
            <div class="flex-col justify-start items-start gap-1.5 flex">
              <div class="w-96 justify-between items-start inline-flex">
                <div class="text-white text-sm font-normal tracking-wide">Password</div>
                <div class="hidden text-right text-red-500 text-sm font-normal tracking-wide">Error message</div>
              </div>
              <div class="justify-start items-center gap-2.5 inline-flex">
                <input class="w-96 h-11 px-3.5 py-7 outline-none rounded-[10px] focus:border-white border border-white border-opacity-20 bg-transparent text-white text-sm font-medium tracking-wide"/>
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
        </div>
      </div>
      <div class="absolute bottom-0 w-full pointer-events-none">
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