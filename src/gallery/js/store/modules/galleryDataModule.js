export const galleryDataModule = {
  namespaced: true,
  state: {
    galleryPosts: [],
    openedPostIndex: null,
    galleryLoading: true,
    lightBoxLoading: false,
    pageLoaded: 1,
    postsPerPage: 9,
    galleryCount: 0,
    routeNameSpace: '/wp-json/gm-frontend-gallery/v1'
  },
  getters: {
    getGalleryPosts: state => state.galleryPosts,
    getGalleryCount: state => state.galleryCount,
    getGalleryLoading: state => state.galleryLoading,
    getRouteNameSpace: state => state.routeNameSpace,
    getPageLoaded: state => state.pageLoaded,
    getPostsPerPage: state => state.postsPerPage,
    getOpenedPostIndex: state => state.openedPostIndex
  },
  mutations: {
    updateGalleryCount(state, count) {
      state.galleryCount = count;
    },
    updateGalleryPosts(state, posts) {
      state.galleryPosts = state.galleryPosts.concat(posts);
    },
    updateGalleryLoading(state, value) {
      state.galleryLoading = value;
    },
    updatePageLoaded(state, pageLoaded) {
      state.pageLoaded = pageLoaded;
    },
    setOpenedPostIndex(state, index) {
      state.openedPostIndex = index;
    }
  },
  actions: {
    SET_GALLERY_POSTS({ commit, getters }, time) {
      const namespace = getters.getRouteNameSpace;
      const pageLoaded = getters.getPageLoaded;
      const postsPerPage = getters.getPostsPerPage;

      commit('updateGalleryLoading', true);

      let xhr = new XMLHttpRequest();
      xhr.open('GET', `${namespace}/get/${pageLoaded}/${postsPerPage}`);
      xhr.onload = () => {
        const responseData = JSON.parse(xhr.responseText);
        setTimeout(()=>{
          commit('updateGalleryPosts', responseData.posts);
          commit('updateGalleryCount', responseData['gallery_count']);
          commit('updatePageLoaded', pageLoaded + 1);
          commit('updateGalleryLoading', false);
        }, time)
      };
      xhr.send();
    },
    SET_OPENED_POST_INDEX({commit}, index) {
      commit('setOpenedPostIndex', index);
    }
  }
};