<template>
  <div>
    <NuxtLayout title="Profile" class="text-white flex flex-col min-h-screen" name="dashboard">
      <div class="container flex flex-col relative gap-10 mx-auto px-2 md:px-4">
        <Spinner v-if="isLoading" class="spinner" />
        <div class="flex gap-2 w-full p-5 max-w-2xl bg-black rounded-[10px] justify-center items-center">
          <div class="w-30 h-30 avatar rounded-full">
            <img class="object-cover rounded-full" src="https://placeholder.com/120x120">
          </div>
          <div class="flex flex-col gap-5 w-full">
            <FInputClassic @blur="saveUser" label="First Name" placeholder="First name" :model-value="userForm.firstname"/>
            <FInputClassic @blur="saveUser" label="Last Name" placeholder="Last name" :model-value="userForm.lastname"/>
          </div>
        </div>
        <div class="flex gap-2 w-full p-5 max-w-2xl bg-black rounded-[10px]">
          <div class="flex flex-col gap-5 w-full">
            <FInputClassic @blur="saveUser" label="Phone" placeholder="000-00-00" :model-value="userForm.phone"/>
            <FInputClassic @blur="saveUser" label="E-mail" placeholder="E-mail address" :model-value="userForm.email"/>
          </div>
        </div>
        <div class="flex gap-2 w-full p-5 max-w-2xl bg-black rounded-[10px]">
          <div class="flex flex-col gap-5 w-full">
            <FInputClassic @blur="saveUser" label="Date Of Birth" placeholder="mm/dd/yy" :model-value="userForm.date_of_birth"/>
            <FInputClassic @blur="saveUser" label="Username" placeholder="Visible to others" :model-value="userForm.username"/>
          </div>
        </div>
      </div>
    </NuxtLayout>
  </div>
</template>

<style scoped>
.spinner {
  border: 4px solid rgba(255, 255, 255, 0.2);
  border-left-color: #ffffff;
  border-radius: 50%;
  width: 40px;
  height: 40px;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}
</style>

<script setup lang="ts">
import {onMounted, reactive, ref} from "vue";
import {FInputClassic, Spinner} from "~/src/shared/ui/common";
import {useSessionStore} from "~/src/entities/Session";
import {useApi} from "~/src/lib/api";


const isLoading = ref(false);

const userForm = reactive({
  firstname: 'John',
  lastname: 'Doe',
  email: '',
  date_of_birth: '',
  phone: '',
  username: '',
  profile_photo: '',
});

onMounted(async () => {
  const {fetch: fetchUser} = useApi({ url: '/user/me', auth: true });
  isLoading.value = true;
  await fetchUser().then((response) => {
    userForm.firstname = response.data.user.firstname;
    userForm.lastname = response.data.user.lastname;
    userForm.profile_photo = response.data.user.profile_photo;
    userForm.email = response.data.user.email;
    userForm.phone = response.data.user.phone;
    userForm.date_of_birth = response.user.data.date_of_birth;
    userForm.username = response.user.data.username;
    isLoading.value = false;
    isLoading.value = false;
  });
});

const saveUser = async () => {
  isLoading.value = true;
  const {post: saveUser} = useApi({ url: '/user/update', auth: true });

  await saveUser({ data: userForm }).then((response) => {
    console.log('response', response.data.user)
    isLoading.value = false;
  });
};

</script>