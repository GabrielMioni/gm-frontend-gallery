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
    getPostNonce: state => state.postNonce,
    getGalleryPosts: state => state.galleryPosts,
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
    SET_POST_CONTENT(context, payload) {
      context.commit('setPostContent', payload);
    },
    SET_POST_IMAGE_DATA(context, payload) {
      context.commit('setPostImageData', payload)
    }
  },
});