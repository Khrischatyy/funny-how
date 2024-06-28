<script setup lang="ts">
import GoogleMap from "~/src/widgets/GoogleMap.vue";
import {useHead} from "@unhead/vue";
import {useNuxtApp} from "#app";
import {useRoute} from "nuxt/app";
import {onMounted, ref} from "vue";
import {Header, Footer} from "~/src/shared/ui/components";
import {useApi} from "~/src/lib/api";

const route = useRoute();
const { $axios } = useNuxtApp();

useHead({
  title: 'Dashboard | Map',
  meta: [
    { name: 'Funny How', content: 'Dashboard' }
  ],
})

const studios = ref([]);
onMounted(async () => {
  const {fetch:getStudios} = useApi({
    'url': '/map/studios',
  });
  getStudios().then((response) => {
    studios.value = response.data;
    console.log(studios.value.map((studio) => studio.company.name));
  });
});
</script>

<template>
  <div class="grid min-h-[100vh] h-full animate__animated animate__fadeInRight">
    <Header :hide-login-button="true" class="absolute z-50 bottom-0 left-0 md:bottom-[unset] md:left-[unset]" />
    <GoogleMap :lat="'34.0199732'" v-if="studios.length>0" :markers="studios" :lng="'-118.266289'"/>
  </div>
</template>

<style scoped lang="scss">
a {
  //make cool text decoration and maybe not white but some other color
  text-decoration: underline;
  color: var(--color-gold);
  text-shadow: 2px 2px 4px var(--color-dark-orange); /* Shadow effect */
}
.shadow-text{
  text-shadow: 2px 3px 1px rgba(0, 0, 0, 0.8), 12px 14px 1px rgba(0, 0, 0, 0.8);
}
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
select {
  -webkit-appearance: none;
  -moz-appearance: none;
  text-indent: 1px;
  text-overflow: '';
  cursor: pointer;
}

</style>