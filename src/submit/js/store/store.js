import Vue from 'vue';
import Vuex from 'vuex';
import { postDataModule } from "./modules/postDataModule";

Vue.use(Vuex);

export const store = new Vuex.Store({
  modules: {
    postData: postDataModule
  },
  state: {
    mainTitle: '',
    mainTitleError: '',
    postNonce: null,
  },
  getters: {
    getMainTitle: state => state.mainTitle,
    getPostNonce: state => state.postNonce,
    getMainTitleError: state => state.mainTitleError,
  },
  mutations: {
    setMainTitle(state, newTitle) {
      state.mainTitle = newTitle;
    },
    setPostNonce(state, nonce) {
      state.postNonce = nonce;
    },
    setMainTitleError(state, error) {
      state.mainTitleError = error;
    },
  },
  actions: {
    SET_MAIN_TITLE(context, newTitle) {
      context.commit('setMainTitle', newTitle);
    },
    SET_POST_NONCE(context, postNonce) {
      context.commit('setPostNonce', postNonce);
    },
    SET_MAIN_TITLE_ERROR(context, error) {
      context.commit('setMainTitleError', error);
    },
  },
});