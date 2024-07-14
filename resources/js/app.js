import './bootstrap';

import { createApp } from 'vue'
import Demo from "./components/Demo.vue";

const app = createApp()
app.component('demo', Demo)

app.mount('#app')
