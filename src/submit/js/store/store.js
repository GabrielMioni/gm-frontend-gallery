import Vue from 'vue';
import Vuex from 'vuex';

Vue.use(Vuex);

const defaultGalleryPostObject = () => {
  return {
    content: '',
    imageUrl: null,
    file: null,
  }
};

export const store = new Vuex.Store({
  state: {
    mainTitle: '',
    galleryPosts: [defaultGalleryPostObject()],
    postNonce: null,
  },
  getters: {
    getMainTitle: state => state.mainTitle,
    getGalleryPosts: state => state.galleryPosts,
    postNonce: state => state.postNonce,
  },
  mutations: {
    setMainTitle(state, newTitle) {
      state.mainTitle = newTitle;
    },
    setPostNonce(state, nonce) {
      state.postNonce = nonce;
    },
    updateGalleryPostContent(state, payload) {
      state.galleryPosts[payload.index].content = payload.data;
    },
    updateImageUpload(state, payload) {
      state.galleryPosts[payload.index].imageUrl = payload.imageUrl;
      state.galleryPosts[payload.index].file = payload.file;
    },
    addGalleryPost(state) {
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
    REMOVE_POST(context, index) {
      context.commit('removePost', index);
    },
  },
});