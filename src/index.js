/* Babel polyfills */
import "core-js/stable";
import "regenerator-runtime/runtime";

window.Vue = require('vue');

// Turn dev tools on. This will automatically be turned off when 
// compiling in production mode.
Vue.config.devtools = true;

import API from './api';
Object.defineProperty(Vue.prototype, '$api', { value: API });

import Toasted from 'vue-toasted';
Vue.use(Toasted, {
    position      : 'top-center',
    duration      : 4000,
    containerClass: 'alert-list',
    iconPack      : 'fontawesome',    
});

import App  from './components/App.vue';
import i18n from './i18n';

window.onload = function () {
    if ( document.getElementById('app') ) {
        const app = new Vue({
            i18n,
            el: '#app',
            render: h => h(App)
        });
    }
}