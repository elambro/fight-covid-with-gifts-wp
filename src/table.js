/* Babel polyfills */
import "core-js/stable";
import "regenerator-runtime/runtime";

window.Vue = require('vue');

// Turn dev tools on. This will automatically be turned off when 
// compiling in production mode.
Vue.config.devtools = true;

import API from './api';
Object.defineProperty(Vue.prototype, '$api', { value: API });

import i18n from './i18n';

import AppTable from './components/Admin/Table';
Vue.component('cvdapp-table', AppTable);

import Actions from './components/Admin/Actions';
Vue.component('actions', Actions);

window.onload = function () {
    if ( document.getElementById('cvdapp') ) {
        const cvdapp = new Vue({
            i18n,
            el: '#cvdapp'
        });
    }
}