/* Babel polyfills */
import "core-js/stable";
import "regenerator-runtime/runtime";

window.Vue = require('vue');
// Turn dev tools on. This will automatically be turned off when 
// compiling in production mode.
Vue.config.devtools = true;

// Use the axios library, since it can be used with or without Vue
window.axios = require('axios');

import Toasted from 'vue-toasted';
Vue.use(Toasted, {
    position      : 'top-center',
    duration      : 4000,
    containerClass: 'alert-list',
    iconPack      : 'fontawesome',    
});

import App from './App.vue';
window.onload = function () {
    if ( document.getElementById('app') ) {
        const app = new Vue({
            el: '#app',
            render: h => h(App)
        });
    }
}