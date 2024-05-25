import { defineStore } from 'pinia';
import {useRuntimeConfig} from "#imports";
import axios from "axios";
import {useSessionStore} from "~/src/entities/Session";
import { useRouter } from 'vue-router'
import {navigateTo} from "nuxt/app";
const router = useRouter()

type inputField = {
    name: string;
    title: string;
    type: string;
}

export type StudioFormValues = {
    dayoffs: dayOff[];
}

type formValues = {
    inputValues: StudioFormValues
    errors: string[];
    inputFields: inputField[];
    hoursMods: string[];
}

type dayOff = {
    day: string;
    start: string;
    end: string;
}



export const useCreateStudioFormStore = defineStore({
    id: 'create-studio-hours-store',
    state: (): formValues => ({
            inputValues: {
                dayoffs: [{
                    day: '',
                    start: '',
                    end: ''
                }],
            },
            errors: [],
            hoursMods: [],
            inputFields: [
                {name: 'name', title: 'Name', type: 'text'},
                {name: 'email', title: 'Email', type: 'email'},
                {name: 'password', title: 'Password', type: 'password'},
                {name: 'password_confirmation', title: 'Confirm Password', type: 'password'}]
    }),
    actions: {
        submit(){
            const config = useRuntimeConfig()

            const formData = new FormData();
            formData.append('logo', this.inputValues.logo as File);
            formData.append('company', this.inputValues.company);
            formData.append('address', this.inputValues.address);
            formData.append('country', this.inputValues.country);
            formData.append('city', this.inputValues.city);
            formData.append('street', this.inputValues.street);
            formData.append('about', this.inputValues.about);
            formData.append('longitude', this.inputValues.longitude);
            formData.append('latitude', this.inputValues.latitude);

            let requestConfig = {
                method: 'post',
                credentials: true,
                url: `${config.public.apiBase}/v1/brand`,
                data: formData,
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'multipart/form-data',
                    'Authorization': 'Bearer ' + useSessionStore().accessToken
                }
            };
            axios.defaults.headers.common['X-Api-Client'] = `web`
            axios.request(requestConfig)
                .then((response) => {
                    //function that redirect to route /@[slug]

                    navigateTo('/@' + response.data?.data?.slug)
                    console.log('response', response)
                })
                .catch((error) => {
                    console.log(error);
                });
        }
    }
})