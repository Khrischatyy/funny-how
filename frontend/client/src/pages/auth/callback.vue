<script setup lang="ts">
import {useHead} from "@unhead/vue";
import {Particles} from "~/src/shared/ui/components";
import {useRouter} from "vue-router";
import {useApi} from "~/src/lib/api";
import {useSessionStore} from "~/src/entities/Session";


useHead({
  title: 'Funny How – Book a Session Time',
  meta: [
    { name: 'Funny How', content: 'Book A Studio Time | Auth' }
  ],
});

const router = useRouter();
const session = useSessionStore();

const authorizeUser = async (token: string) => {
  session.setIsLoading(true);

  try {
    // Validate the token or fetch the user data with the token
    const { fetch } = useApi({ url: '/me', auth: false });
    const data = await fetch({ headers: { Authorization: `Bearer ${token}` } });
console.log('data', data);
    if (data) {
      let role = data?.data?.role;
      let has_company = data?.data?.has_company;
      session.setAccessToken(token);
      session.setAuthorized(true);
      session.setUserRole(data?.data?.role);

      if (role === 'studio_owner' && has_company) {
        await router.push('/create');
      } else {
        await router.push('/');
      }
    }
  } catch (error) {
    console.error('Authorization error:', error);
    await router.push('/login');
  } finally {
    session.setIsLoading(false);
  }
};

// Extract token from URL query
const route = useRouter().currentRoute.value;
const token = route.query.token;

if (token) {
  authorizeUser(token as string);
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