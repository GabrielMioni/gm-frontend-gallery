import Vue from 'vue'
import Vuex from 'vuex'
import { postDataModule } from './modules/postDataModule'
import { mainDataModule } from './modules/mainDataModule'

Vue.use(Vuex)

export const store = new Vuex.Store({
  modules: {
    postData: postDataModule,
    mainData: mainDataModule
  }
})
