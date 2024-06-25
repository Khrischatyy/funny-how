import { ref } from 'vue';
import { Loader } from '@googlemaps/js-api-loader';
import { useRuntimeConfig } from '#imports';

export function useGoogleMaps() {
    const config = useRuntimeConfig();
    const autocomplete = ref(null);
    const geocoder = ref(null);

    async function initGoogleMaps(inputElement: HTMLInputElement) {
        const loader = new Loader({
            apiKey: config.public.googleMapKey,
            version: "weekly",
        });

        const Places = await loader.importLibrary('places');
        const Geocoding = await loader.importLibrary('geocoding');

        const options = {
            componentRestrictions: { country: ["us", "ca"] },
            fields: ["address_components", "geometry"],
            types: ["address"],
        };

        autocomplete.value = new Places.Autocomplete(inputElement, options);

        geocoder.value = new Geocoding.Geocoder();
        autocomplete.value.addListener('place_changed', () => {
            const place = autocomplete.value.getPlace();
            console.log('place', place.id)
            console.log(place.geometry.location.lat(), place.geometry.location.lng());
            if (!place.geometry) {
                return;
            }
            geocoder.value.geocode({ location: place.geometry.location }, (results, status) => {
                if (status === "OK") {
                    console.log(results);
                } else {
                    console.error("Geocoder failed due to: " + status);
                }
            });

        })

        //
    }

    return {
        autocomplete,
        geocoder,
        initGoogleMaps,
    };
}