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
  mutations: {
    updateTitle(state, newTitle) {
      state.mainTitle = newTitle;
    },
    updatePostNonce(state, nonce) {
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
    removeGalleryPost(state, index) {
      state.galleryPosts.splice(index, 1);
      if (state.galleryPosts.length <= 0) {
        state.galleryPosts.push(defaultGalleryPostObject());
      }
    }
  },
  getters: {
    mainTitle: state => state.mainTitle,
    galleryPosts: state => state.galleryPosts,
    postNonce: state => state.postNonce,
  }
});