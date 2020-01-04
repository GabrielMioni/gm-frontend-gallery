import {defaultGalleryPostObject} from "../../../../utilities/helpers";

export const postDataModule = {
  namespaced: true,
  state: {
    galleryPosts: [defaultGalleryPostObject()],
  },
  getters: {
    getGalleryPosts: state => state.galleryPosts,
    getGalleryPostDataFunction (state) {
      return (payload) => {
        if (typeof payload.deepKey !== 'undefined') {
          return state.galleryPosts[payload.index][payload.type][payload.deepKey];
        }
        if (typeof payload.type !== 'undefined') {
          return state.galleryPosts[payload.index][payload.type];
        }
        return state.galleryPosts[payload.index];
      }
    }
  },
  mutations: {
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
    addPost(state) {
      state.galleryPosts.push(defaultGalleryPostObject());
    },
    removePost(state, index) {
      state.galleryPosts.splice(index, 1);
      if (state.galleryPosts.length <= 0) {
        state.galleryPosts.push(defaultGalleryPostObject());
      }
    },
    resetGalleryPostData(state) {
      state.galleryPosts = [defaultGalleryPostObject()];
    }
  },
  actions: {
    SET_POST_CONTENT(context, payload) {
      context.commit('setPostContent', payload);
    },
    SET_POST_IMAGE_DATA(context, payload) {
      context.commit('setPostImageData', payload)
    },
    SET_POST_ERROR(context, payload) {
      context.commit('setPostError', payload);
    },
    ADD_POST(context) {
      context.commit('addPost');
    },
    REMOVE_POST(context, index) {
      context.commit('removePost', index);
    },
    RESET_GALLERY_POST_DATA(context) {
      context.commit('resetGalleryPostData');
    }
  }
};