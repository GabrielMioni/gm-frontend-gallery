import Vue from 'vue';
import Vuex from 'vuex';
import {defaultGalleryPostObject} from "./helpers";
import {postDataModule} from "./modules/postDataModule";

Vue.use(Vuex);

export const store = new Vuex.Store({
  modules: {
    postData: postDataModule
  },
  state: {
    mainTitle: '',
    mainTitleError: '',
    galleryPosts: [defaultGalleryPostObject()],
    postNonce: null,
  },
  getters: {
    getMainTitle: state => state.mainTitle,
    getPostNonce: state => state.postNonce,
    getGalleryPosts: state => state.galleryPosts,
    getMainTitleError: state => state.mainTitleError,
  },
  mutations: {
    setMainTitle(state, newTitle) {
      state.mainTitle = newTitle;
    },
    setPostNonce(state, nonce) {
      state.postNonce = nonce;
    },
    setPostContent(state, payload) {
      state.galleryPosts[payload.index].content = payload.data;
    },
    setPostImageData(state, payload) {
      state.galleryPosts[payload.index].imageUrl = payload.imageUrl;
      state.galleryPosts[payload.index].file = payload.file;
    },
    setPostError(state, payload) {
      state.galleryPosts[payload.index].errors[payload.type] = payload.error;
    },
    setMainTitleError(state, error) {
      state.mainTitleError = error;
    },
    addPost(state) {
      state.galleryPosts.push(defaultGalleryPostObject());
    },
    removePost(state, index) {
      state.galleryPosts.splice(index, 1);
      if (state.galleryPosts.length <= 0) {
        state.galleryPosts.push(defaultGalleryPostObject());
      }
    }
  },
  actions: {
    SET_MAIN_TITLE(context, newTitle) {
      context.commit('setMainTitle', newTitle);
    },
    SET_POST_NONCE(context, postNonce) {
      context.commit('setPostNonce', postNonce);
    },
    SET_POST_CONTENT(context, payload) {
      context.commit('setPostContent', payload);
    },
    SET_POST_IMAGE_DATA(context, payload) {
      context.commit('setPostImageData', payload)
    },
    SET_POST_ERROR(context, payload) {
      context.commit('setPostError', payload);
    },
    SET_MAIN_TITLE_ERROR(context, error) {
      context.commit('setMainTitleError', error);
    },
    ADD_POST(context) {
      context.commit('addPost');
    },
    REMOVE_POST(context, index) {
      context.commit('removePost', index);
    },
  },
});