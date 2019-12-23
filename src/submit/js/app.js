import Vue from 'vue'
import App from './App.vue'
import { store } from './store/store';

new Vue({
  el: '#gm-frontend-submit',
  store,
  render: h => h(App)
});