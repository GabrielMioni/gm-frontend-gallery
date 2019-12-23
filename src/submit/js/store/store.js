import Vue from 'vue';
import Vuex from 'vuex';

Vue.use(Vuex);

export const store = new Vuex.Store({
  state: {
    mainTitle: '',
    galleryPosts: [{
      content: '',
      imageUrl: null,
      file: null,
    }],
    postNonce: null,
  },
  mutations: {
    updateTitle(state, newTitle) {
      state.mainTitle = newTitle;
    },
    updatePostNonce(state, nonce) {
      state.postNonce = nonce;
    },
    addGalleryPost(state) {
      state.galleryPosts.push({
        content: 'Tee hee',
        imageUrl: null,
        file: null,
      });
    },
    removeGalleryPost(state, index) {
      state.galleryPosts.splice(index, 1);
    }
  },
  getters: {
    mainTitle: state => state.mainTitle,
    galleryPosts: state => state.galleryPosts,
  }
});