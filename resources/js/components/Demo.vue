<script setup>
import {computed, onMounted, ref} from 'vue';
import { ExclamationCircleIcon, XCircleIcon } from '@heroicons/vue/20/solid';
import { route } from 'ziggy-js';
import * as _ from 'lodash-es';

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
    const formIsSubmitting = ref(false)
    const hasResults = ref(false)
    const upsMatched = ref(false)
    const fedexMatched = ref(false)
    const upsType = ref('')
    const fedexType = ref('')
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
    function resetResults() {
        hasResults.value = false;
        upsType.value = null;
        upsMatched.value = false;
        fedexType.value = null;
        fedexMatched.value = false;
    }
    function submitForm() {
        resetFieldErrors();
        resetResults()
        generalErrors.value = '';
        formIsSubmitting.value = true;
        if (useUps.value === false && useFedex.value === false) {
            generalErrors.value = 'Please select at least one carrier.';
            formIsSubmitting.value = false;
            return;
        }
        axios.post(route('validateAddress'), {
            address: address.value,
            city: city.value,
            state: state.value,
            zip: zip.value,
            country: country.value,
            useUps: useUps.value,
            useFedex: useFedex.value,
        })
            .then(function (response) {
                if (useFedex.value) {
                    fedexMatched.value = _.get(response, 'data.fedex.matched')
                    fedexType.value = _.get(response, 'data.fedex.type')
                    hasResults.value = true
                    const fedexError = _.get(response, 'data.fedex.error')
                    if (!_.isEmpty(fedexError)) {
                        generalErrors.value = `${generalErrors.value} ${fedexError}`
                    }
                }
                if (useUps.value) {
                    upsMatched.value = _.get(response, 'data.ups.matched')
                    upsType.value = _.get(response, 'data.ups.type')
                    hasResults.value = true
                    const upsError = _.get(response, 'data.ups.error')
                    if (!_.isEmpty(upsError)) {
                        generalErrors.value = `${generalErrors.value} ${upsError}`
                    }
                }
                formIsSubmitting.value = false;
            })
            .catch(function (error) {
                if (error.response.status === 422) {
                    const zipErrorMessages = _.get(error, 'response.data.errors.zip', [])
                    if (zipErrorMessages.length) {
                        zipError.value = zipErrorMessages.join('<br>')
                    }
                    const stateErrorMessages = _.get(error, 'response.data.errors.state', [])
                    if (stateErrorMessages.length) {
                        stateError.value = stateErrorMessages.join('<br>')
                    }
                    formIsSubmitting.value = false;
                    return;
                }
                generalErrors.value = _.get(error, 'response.data.message', '')
                if (generalErrors.value === 'Unable to fetch error message.') {
                    generalErrors.value = 'Unable to fetch error message.'
                }
                formIsSubmitting.value = false;
            })
            .finally(function () {
                //
            })
    }
</script>

<template>
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
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
                            <p class="mt-2 text-sm text-red-600" id="state-error" v-if="stateIsInvalid" v-html="stateError"></p>
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
                            <p class="mt-2 text-sm text-red-600" id="zip-error" v-if="zipIsInvalid" v-html="zipError"></p>
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
                    <div role="status" v-if="formIsSubmitting">
                        <svg aria-hidden="true" class="inline w-4 h-4 text-gray-200 animate-spin dark:text-gray-600 fill-blue-600" viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z" fill="currentColor"/>
                            <path d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z" fill="currentFill"/>
                        </svg>
                        <span class="sr-only">Loading...</span>
                    </div>
                    <button type="submit"
                            class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
                            :disabled="formIsSubmitting"
                    >
                        Get Results
                    </button>
                </div>
            </form>

            <p v-if="hasResults && useUps" class="my-12 text-md leading-6 text-gray-800">
                UPS Results:
                    <span v-if="upsMatched">
                        Address matched.
                    </span>
                    <span v-else>
                        Address not matched.
                    </span>
                    Type: {{ upsType }}
            </p>
            <p v-if="hasResults && useFedex" class="my-12 text-md leading-6 text-gray-800">
                Fedex Results:
                    <template v-if="fedexMatched">
                        Address matched.
                    </template>
                    <template v-else>
                        Address not matched.
                    </template>
                    Type: {{ fedexType }}
            </p>
        </div>
    </div>
</template>

<style scoped>

</style>
