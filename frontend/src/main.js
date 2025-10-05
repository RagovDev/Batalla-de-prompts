import { createApp } from "vue";
import App from "./App.vue";
import router from "./router";
import './style.css' 

import Vue3Toastify from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';

createApp(App).use(router).mount("#app");
