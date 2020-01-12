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
    getRouteNameSpace: state => state.routeNameSpace,
    getPageLoaded: state => state.pageLoaded,
    getPostsPerPage: state => state.postsPerPage,
  },
  mutations: {
    updateGalleryCount(state, count) {
      state.galleryCount = count;
    },
    updateGalleryPosts(state, posts) {
      state.galleryPosts = posts;
    },
    updatePageLoaded(state, pageLoaded) {
      state.pageLoaded = pageLoaded;
    }
  },
  actions: {
    SET_GALLERY_POSTS({ dispatch, commit, getters }) {
      const namespace = getters.getRouteNameSpace;
      const pageLoaded = getters.getPageLoaded;
      const postsPerPage = getters.getPostsPerPage;

      let xhr = new XMLHttpRequest();
      xhr.open('GET', `${namespace}/get/${pageLoaded}/${postsPerPage}`);
      xhr.onload = () => {
        const responseData = JSON.parse(xhr.responseText);
        commit('updateGalleryPosts', responseData.posts);
        commit('updateGalleryCount', responseData['gallery_count']);
      };
      xhr.send();
    },
  }
};