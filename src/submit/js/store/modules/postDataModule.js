import { defaultGalleryPostObject, setUniqueIds } from '@/utilities/helpers'

export const postDataModule = {
  namespaced: true,
  state: {
    galleryPosts: [defaultGalleryPostObject('z')],
    uniqueIds: setUniqueIds()
  },
  getters: {
    getGalleryPosts: state => state.galleryPosts,
    getGalleryPostsLength: state => state.galleryPosts.length,
    getGalleryPostDataFunction (state) {
      return (payload) => {
        if (typeof payload.deepKey !== 'undefined') {
          return state.galleryPosts[payload.index][payload.type][payload.deepKey]
        }
        if (typeof payload.type !== 'undefined') {
          return state.galleryPosts[payload.index][payload.type]
        }
        return state.galleryPosts[payload.index]
      }
    }
  },
  mutations: {
    setPostContent (state, payload) {
      state.galleryPosts[payload.index].content = payload.data
    },
    setPostImageData (state, payload) {
      state.galleryPosts[payload.index].imageUrl = payload.imageUrl
      state.galleryPosts[payload.index].file = payload.file
    },
    setPostError (state, payload) {
      state.galleryPosts[payload.index].errors[payload.type] = payload.error
    },
    addPost (state) {
      const current = state.uniqueIds[state.uniqueIds.length - 1]
      state.uniqueIds.pop()
      state.galleryPosts.push(defaultGalleryPostObject(current))
    },
    addPostAtIndex (state, index) {
      const current = state.uniqueIds[state.uniqueIds.length - 1]
      state.uniqueIds.pop()
      state.galleryPosts.splice(index + 1, 0, defaultGalleryPostObject(current))
      if (state.uniqueIds.length <= 0) {
        state.uniqueIds = setUniqueIds()
      }
    },
    removePost (state, index) {
      state.galleryPosts.splice(index, 1)
      if (state.galleryPosts.length <= 0) {
        state.uniqueIds = setUniqueIds()
        state.galleryPosts.push(defaultGalleryPostObject('z'))
      }
    },
    clearPost (state, index) {
      const galleryPost = state.galleryPosts[index]
      galleryPost.content = ''
      galleryPost.imageUrl = null
      galleryPost.file = null
      galleryPost.errors.content = ''
      galleryPost.errors.imageUrl = ''
      galleryPost.errors.file = ''
    },
    resetGalleryPostData (state) {
      state.uniqueIds = setUniqueIds()
      state.galleryPosts = [defaultGalleryPostObject('z')]
    }
  },
  actions: {
    SET_POST_CONTENT (context, payload) {
      context.commit('setPostContent', payload)
    },
    SET_POST_IMAGE_DATA (context, payload) {
      context.commit('setPostImageData', payload)
    },
    SET_POST_ERROR (context, payload) {
      context.commit('setPostError', payload)
    },
    ADD_POST (context) {
      context.commit('addPost')
    },
    ADD_POST_AT_INDEX (context, index) {
      context.commit('addPostAtIndex', index)
    },
    REMOVE_POST (context, index) {
      context.commit('removePost', index)
    },
    CLEAR_POST (context, index) {
      context.commit('clearPost', index)
    },
    RESET_GALLERY_POST_DATA (context, index) {
      context.commit('resetGalleryPostData', index)
    }
  }
}
