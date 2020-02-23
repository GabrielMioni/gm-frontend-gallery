import Vue from 'vue'
import App from './App.vue'
import Vuetify from 'vuetify'

Vue.use(Vuetify)

const vuetify = new Vuetify()

new Vue({ // eslint-disable-line no-new
  vuetify,
  el: '#gm-frontend-gallery-post-single',
  render: h => h(App)
})
