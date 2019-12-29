export const postDataModule = {
  namespaced: true,
  getters: {
    getGalleryPostData (state, getters, rootState) {
      return (payload) => {
        if (typeof payload.deepKey !== 'undefined') {
          return rootState.galleryPosts[payload.index][payload.type][payload.deepKey];
        }
        return rootState.galleryPosts[payload.index][payload.type]
      }
    }
  }
};