import Vue from 'vue'
import App from './App.vue'
import { store } from './store/store';
import PortalVue from 'portal-vue'

Vue.use(PortalVue);

new Vue({
  el: '#gm-frontend-submit',
  store,
  render: h => h(App)
});