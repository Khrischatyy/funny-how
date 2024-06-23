<script setup lang="ts">
import {navigateTo} from 'nuxt/app';
import {authorizeUser} from '~/src/shared/utils';
import {type ResponseDto, useApi} from '~/src/lib/api';
import {Particles} from '~/src/shared/ui/components';
import {useSessionStore} from '~/src/entities/Session';
import {useRouter} from "vue-router";
import {useHead} from "@unhead/vue";

// Set the page metadata
useHead({
  title: 'Funny How – Book a Session Time',
  meta: [
    { name: 'description', content: 'Book A Studio Time | Auth' }
  ],
});

const router = useRouter();
const route = router.currentRoute.value;
const sessionStore = useSessionStore(); // Retrieve session store

// Directly handle the token from URL
const token = route.query.token as string | undefined;
if (token) {
  // Setup API with token
  const api = useApi<ResponseDto<{ user: string, role: string }>>({ url: '/me', auth: true, token });
  api.fetch().then(response => {
    // Pass sessionStore to authorizeUser
    authorizeUser(sessionStore, response, route, token);
    navigateTo('/');
  }).catch(error => {
    console.error('Authorization error:', error);
    router.push('/login');
  });
} else {
  router.push('/login');
}
</script>

<template>
  <div>
    <client-only>
      <Particles :position="{x: 0, y: 0, z: 0}"/>
    </client-only>
  </div>
</template>

<style scoped lang="scss">
</style>