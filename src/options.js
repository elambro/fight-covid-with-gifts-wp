/* Babel polyfills */
import "core-js/stable";
import "regenerator-runtime/runtime";

window.Vue = require('vue');

// Turn dev tools on. This will automatically be turned off when 
// compiling in production mode.
Vue.config.devtools = true;

import API from './api';
Object.defineProperty(Vue.prototype, '$api', { value: API });

import App  from './components/Options.vue';
import i18n from './i18n';

window.onload = function () {
    if ( document.getElementById('cvdapp') ) {
        const cvdapp = new Vue({
            i18n,
            el: '#cvdapp',
            render: h => h(App)
        });
    }
}