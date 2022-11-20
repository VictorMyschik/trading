/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

window.Vue = require('vue').default;
/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

// const files = require.context('./', true, /\.vue$/i)
// files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default))

import CarouselPlugin from 'bootstrap-vue';
import vmodal from 'vue-js-modal';
import vSelect from "vue-select";
import "vue-select/dist/vue-select.css";

Vue.use(vmodal);

Vue.use(require('bootstrap-vue'));

Vue.component("v-select", vSelect);
Vue.use(CarouselPlugin)

Vue.component('mr-table', require('./components/MrTable.vue').default);
Vue.component('pagination', require('laravel-vue-pagination'));
Vue.component('mr-p', require('./components/MrPopupForm.vue').default);
Vue.component('mr-welcome-page', require('./components/MrWelcomePage').default);
/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

const app = new Vue({
    el: '#app',
});
