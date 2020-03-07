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
    updateGalleryCount (state, count) {
      state.galleryCount = count
    },
    updateGalleryPosts (state, posts) {
      state.galleryPosts = state.galleryPosts.concat(posts)
    },
    updateGalleryLoading (state, value) {
      state.galleryLoading = value
    },
    updatePageLoaded (state, pageLoaded) {
      state.pageLoaded = pageLoaded
    },
    setOpenedPostIndex (state, index) {
      state.openedPostIndex = index
    }
  },
  actions: {
    SET_GALLERY_POSTS ({ commit, getters }, payload) {
      const namespace = getters.getRouteNameSpace
      const pageLoaded = getters.getPageLoaded
      const postsPerPage = getters.getPostsPerPage

      let time
      let xhrEndPoint

      const galleryPostDataType = typeof payload === 'object' ? 'single' : 'multiple'

      if (galleryPostDataType === 'multiple') {
        time = payload
        xhrEndPoint = `${namespace}/get/${pageLoaded}/${postsPerPage}`
      } else {
        time = payload.time
        xhrEndPoint = `${namespace}/${payload.postId}`
      }

      commit('updateGalleryLoading', true)

      const xhr = new XMLHttpRequest()
      xhr.open('GET', xhrEndPoint)
      xhr.onload = () => {
        const responseData = JSON.parse(xhr.responseText)
        setTimeout(() => {
          if (galleryPostDataType === 'multiple') {
            commit('updateGalleryPosts', responseData.posts)
            commit('updateGalleryCount', responseData.gallery_count)
            commit('updatePageLoaded', pageLoaded + 1)
          }
          if (galleryPostDataType === 'single') {
            commit('updateGalleryPosts', [responseData])
            commit('updateGalleryCount', 1)
          }
          commit('updateGalleryLoading', false)
        }, time)
      }
      xhr.send()
    },
    SET_OPENED_POST_INDEX ({ commit }, index) {
      commit('setOpenedPostIndex', index)
    }
  }
}
