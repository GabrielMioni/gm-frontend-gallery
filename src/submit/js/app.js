import Vue from 'vue'
import App from './App.vue'
import Vuetify from 'vuetify'
import { store } from './store/store'
import PortalVue from 'portal-vue'

Vue.use(PortalVue)
Vue.use(Vuetify)

const vuetify = new Vuetify()

new Vue({
  vuetify,
  el: '#gm-frontend-submit',
  store,
  render: h => h(App)
})
