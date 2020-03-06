import Vue from 'vue'
import App from './App.vue'
import Vuetify from 'vuetify'
import { store } from '@/gallery/js/store/store'

Vue.use(Vuetify)

const vuetify = new Vuetify()

new Vue({ // eslint-disable-line no-new
  vuetify,
  el: '#gm-frontend-gallery-post-single',
  store,
  render: h => h(App)
})
