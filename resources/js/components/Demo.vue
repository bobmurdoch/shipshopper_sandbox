<script setup>
import {computed, onMounted, ref} from "vue";
import { ExclamationCircleIcon, XCircleIcon } from '@heroicons/vue/20/solid';

    const props = defineProps({
        states: Object,
        address: String,
        city: String,
        state: String,
        zip: String,
        country: String,
    })
    const states = ref({})
    const address = ref('')
    const city = ref('')
    const state = ref('')
    const country = ref('US')
    const zip = ref('')
    const useUps = ref(false)
    const useFedex = ref(false)
    const zipError = ref(null)
    const stateError = ref(null)
    const generalErrors = ref('')
    // for now, we will rely on html validation for most
    // fields and that state will always have an option selected
    // since there is no empty choice. demonstrate some form validation on
    // the zip and state fields only for now.
    const zipIsInvalid = computed(() => {
       return zipError.value !== null
    });
    const stateIsInvalid = computed(() => {
       return stateError.value !== null
    });
    onMounted(() => {
        states.value = props.states
        address.value = props.address
        city.value = props.city
        state.value = props.state
        country.value = props.country
        zip.value = props.zip
    })
    function resetFieldErrors() {
        stateError.value = null;
        zipError.value = null;
    }
    function submitForm() {
        console.log('got here');
        resetFieldErrors();
        generalErrors.value = '';
        if (useUps.value === false && useFedex.value === false) {
            generalErrors.value = 'Please select at least one carrier.';
        }
    }
</script>

<template>
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <!-- We've used 3xl here, but feel free to try other max-widths based on your needs -->
        <div class="mx-auto max-w-3xl my-10">
            <form @submit.prevent="submitForm">
                <div class="space-y-12">
                    <div class="border-b border-gray-900/10 pb-12">
                        <h2 class="text-base font-semibold leading-7 text-gray-900">ShipShopper Demo</h2>
                        <p class="my-4 text-sm leading-6 text-gray-600">Enter an address to get validator results</p>
                        <div class="rounded-md bg-red-50 p-4" v-if="generalErrors !== ''">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <XCircleIcon class="h-5 w-5 text-red-400" aria-hidden="true" />
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-red-800">There was an error with your submission</h3>
                                    <div class="mt-2 text-sm text-red-700">
                                        <ul role="list" class="list-disc space-y-1 pl-5">
                                            <li>{{ generalErrors }}</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 col-span-full">
                            <label for="street-address" class="block text-sm font-medium leading-6 text-gray-900">Street address</label>
                            <div class="mt-2">
                                <input type="text" name="street-address" id="street-address" autocomplete="street-address"
                                   class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                                   v-model="address"
                                   required
                                />
                            </div>
                        </div>

                        <div class="mt-4 sm:col-span-2 sm:col-start-1">
                            <label for="city" class="block text-sm font-medium leading-6 text-gray-900">City</label>
                            <div class="mt-2">
                                <input type="text" name="city" id="city" autocomplete="address-level2"
                                   class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                                   v-model="city"
                                   required
                                />
                            </div>
                        </div>

                        <div class="mt-4 sm:col-span-2">
                            <label for="region" class="block text-sm font-medium leading-6 text-gray-900">State /
                                Province</label>
                            <div class="mt-2">
                                <!-- normally would make required below, but will leave off so we can demonstrate form validation on this field as required in laravel rules -->
                                <select id="region" name="region" autocomplete="address-level1"
                                    :class="{
                                        'block w-full rounded-md border-0 py-1.5 shadow-sm ring-1 ring-inset focus:ring-2 focus:ring-inset sm:max-w-xs sm:text-sm sm:leading-6': true,
                                        'text-gray-900 ring-gray-300 focus:ring-indigo-600': stateIsInvalid === false,
                                        'text-red-900 ring-red-300 focus:ring-red-600': stateIsInvalid === true,
                                    }"
                                    v-model="state"
                                    :aria-invalid="stateIsInvalid ? true : null" :aria-describedby="stateIsInvalid ? 'state-error' : null"
                                >
                                    <option value="">Select a state</option>
                                    <option v-for="(stateName, stateValue) in states" :value="stateValue">{{ stateName }}</option>
                                </select>
                            </div>
                            <p class="mt-2 text-sm text-red-600" id="state-error" v-if="stateIsInvalid">{{ stateError }}</p>
                        </div>

                        <div class="mt-4 sm:col-span-2">
                            <label for="postal-code" class="block text-sm font-medium leading-6 text-gray-900">ZIP / Postal code</label>
                            <div class="relative mt-2">
                                <input type="text" name="postal-code" id="postal-code" autocomplete="postal-code"
                                   :class="{
                                        'block w-full rounded-md border-0 py-1.5 shadow-sm ring-1 ring-inset focus:ring-2 focus:ring-inset sm:text-sm sm:leading-6' : true,
                                        'text-red-900 ring-red-300 placeholder:text-red-300 focus:ring-red-500': zipIsInvalid === true,
                                        'text-gray-900 ring-gray-300 placeholder:text-gray-400 focus:ring-indigo-600': zipIsInvalid === false,
                                   }"
                                   v-model="zip"
                                   :aria-invalid="zipIsInvalid ? true : null" :aria-describedby="zipIsInvalid ? 'zip-error' : null"
                                   required
                                />
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3" v-if="zipError !== null">
                                    <ExclamationCircleIcon class="h-5 w-5 text-red-500" aria-hidden="true" />
                                </div>
                            </div>
                            <p class="mt-2 text-sm text-red-600" id="zip-error" v-if="zipIsInvalid">{{ zipError }}</p>
                        </div>

                        <div class="mt-4 sm:col-span-3">
                            <label for="country" class="block text-sm font-medium leading-6 text-gray-900">Country</label>
                            <div class="mt-2">
                                <select id="country" name="country" autocomplete="country-name"
                                    class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:max-w-xs sm:text-sm sm:leading-6"
                                    v-model="country"
                                    required
                                >
                                    <option value="US">United States</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <fieldset>
                    <legend  class="my-4 text-sm leading-6 text-gray-600">
                        Select Carriers
                    </legend>
                    <div class="space-y-5">
                        <div class="relative flex items-start">
                            <div class="flex h-6 items-center">
                                <input id="ups" name="carriers" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600" v-model="useUps" />
                            </div>
                            <div class="ml-3 text-sm leading-6">
                                <label for="ups" class="font-medium text-gray-900">UPS</label>
                            </div>
                        </div>
                        <div class="relative flex items-start">
                            <div class="flex h-6 items-center">
                                <input id="fedex" name="carriers" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600" v-model="useFedex" />
                            </div>
                            <div class="ml-3 text-sm leading-6">
                                <label for="fedex" class="font-medium text-gray-900">Fedex</label>
                            </div>
                        </div>
                    </div>
                </fieldset>

                <div class="mt-6 flex items-center justify-end gap-x-6">
                    <button type="submit"
                            class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                        Get Results
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<style scoped>

</style>
