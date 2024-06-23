<script setup lang="ts">
import {ChooseRole} from "~/src/features/Register/createAccount"
import {useHead} from "@unhead/vue";
import {definePageMeta} from '#imports'
import {inject, onMounted, reactive, ref} from "vue";
import {useApi} from "~/src/lib/api";
import {useSessionStore} from "~/src/entities/Session";

useHead({
  title: 'Funny How – Book a Session Time',
  meta: [
    { name: 'Funny How', content: 'Book A Studio Time' }
  ],
})

definePageMeta({
  middleware: 'auth',
})

const roleValue = ref('');
const { post } = useApi({
  url: 'set-role',
  auth: true,
});
const session = useSessionStore();
const isLoading = ref(true);

onMounted(() => {
  roleValue.value = session.userRole as string;
  isLoading.value = false;
});
const handleBackNavigation = () => {
  window.history.back();
};
const handleRoleUpdate = async (role: string) => {
  roleValue.value = role;

  await post({role: role}).then((response) => {
    session.setUserRole(response?.data);
  }).catch((error) => {
    console.log('error', error);
  });
};

</script>

<template>
  <NuxtLayout name="dashboard" title="Choose Role" :isChildLoading="isLoading">

        <ChooseRole
            :selected-role="roleValue"
            :show-title="false"
            @updateRole="handleRoleUpdate"
            @navigateBack="handleBackNavigation"
            ref="create"
            class="translate-x-[0px] duration-[700ms] relative w-full flex-col justify-start items-center gap-2.5 flex max-w-96"
        />
        <div class="text-white opacity-20">
          You have to choose role before continue to use the app.<br/>
          Based on the information above you will get access to the specific features.
        </div>
  </NuxtLayout>
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