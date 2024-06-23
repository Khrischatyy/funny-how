<script setup lang="ts">
import {useHead} from "@unhead/vue";
import {Particles} from "~/src/shared/ui/components";
import {useRouter} from "vue-router";
import {useApi} from "~/src/lib/api";
import {useSessionStore} from "~/src/entities/Session";
import {authorizeUser} from "~/src/shared/utils";
import {getMe} from "~/plugins/session";


useHead({
  title: 'Funny How – Book a Session Time',
  meta: [
    { name: 'Funny How', content: 'Book A Studio Time | Auth' }
  ],
});

const router = useRouter();
const session = useSessionStore();
const route = useRouter().currentRoute.value;
const callAuthorizeUser = async (token: string) => {
  session.setIsLoading(true);

  try {
    await getMe().then((response) => {
      authorizeUser(session, response, route, token)
    })
  } catch (error) {
    console.error('Authorization error:', error);
    await router.push('/login');
  } finally {
    session.setIsLoading(false);
  }
};

// Extract token from URL query
const token = route.query.token;

if (token) {
  callAuthorizeUser(token as string);
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