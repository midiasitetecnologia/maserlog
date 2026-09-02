/*=========================================================================================
  File Name: main.js
  Description: main vue(js) file
  ----------------------------------------------------------------------------------------
  Item Name: Vuesax Admin - VueJS Dashboard Admin Template
  Author: Pixinvent
  Author URL: hhttp://www.themeforest.net/user/pixinvent
==========================================================================================*/


import Vue from 'vue'
import App from './App.vue'

// Vuesax Component Framework
import Vuesax from 'vuesax'

Vue.use(Vuesax)

// axios
import axios from "./axios.js"
Vue.prototype.$http = axios

// API Calls
import "./http/requests"

// Theme Configurations
import '../themeConfig.js'

// ACL
import acl from './acl/acl'

// Globally Registered Components
import './globalComponents.js'

// Vue Router
import router from './router'

// Vuex Store
import store from './store/store'

import Vue2Filters from 'vue2-filters' 
Vue.use(Vue2Filters)

// Vuesax Admin Filters
import './filters/filters'

const moment = require('moment')
require('moment/locale/pt')

Vue.use(require('vue-moment'), {
    moment
})

// Clipboard
import VueClipboard from 'vue-clipboard2'
Vue.use(VueClipboard);

// Tour
import VueTour from 'vue-tour'
Vue.use(VueTour)
require('vue-tour/dist/vue-tour.css')

import i18n from './i18n/i18n'

import VeeValidate from 'vee-validate';
import validationMessages from 'vee-validate/dist/locale/pt_BR';
Vue.use(VeeValidate, {
   i18nRootKey: 'validations', // customize the root path for validation messages.
   i18n,
   dictionary: {
    pt_BR: validationMessages
   }
});

// Google Maps
import * as VueGoogleMaps from 'vue2-google-maps'
Vue.use(VueGoogleMaps, {
    load: {
        //Esta chave já esta correta na conta da "app.masertransportes"
        key: process.env.MIX_GOOGLE_MAPS_KEY,
        libraries: 'places', // This is required if you use the Auto complete plug-in
    },
})

// Vuejs - Vue wrapper for hammerjs
import { VueHammer } from 'vue2-hammer'
Vue.use(VueHammer)

// Vuejs = Mask input for Vue.js
import VueTheMask from 'vue-the-mask'
Vue.use(VueTheMask)

import money from 'v-money'
Vue.use(money)

// PrismJS
import 'prismjs'
import 'prismjs/themes/prism-tomorrow.css'

// Feather font icon
require('@assets/css/iconfont.css')

// RGSoft css
require('@assets/css/rgsoft.css')

Vue.config.productionTip = false

new Vue({
    router,
    store,
    i18n,
    acl,
    render: h => h(App)
}).$mount('#app')
