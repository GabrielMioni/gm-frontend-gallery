import Vue from 'vue';
import Vuex from 'vuex';
import { galleryDataModule } from "./modules/galleryDataModule";

Vue.use(Vuex);

export const store = new Vuex.Store({
  modules: {
    galleryData: galleryDataModule
  },
});