export const mainDataModule = {
  namespaced: true,
  state: {
    mainNonce: null,
    mainTitle: '',
    mainTitleError: '',
    mainSubmitting: false,
  },
  getters: {
    getMainNonce: state => state.postNonce,
    getMainTitle: state => state.mainTitle,
    getMainTitleError: state => state.mainTitleError,
    getMainSubmitting: state => state.mainSubmitting,
  },
  mutations: {
    setMainNonce(state, nonce) {
      state.postNonce = nonce;
    },
    setMainTitle(state, newTitle) {
      state.mainTitle = newTitle;
    },
    setMainTitleError(state, error) {
      state.mainTitleError = error;
    },
    setMainSubmitting(state, value) {
      state.mainSubmitting = value;
    }
  },
  actions: {
    SET_MAIN_NONCE(context, postNonce) {
      context.commit('setMainNonce', postNonce);
    },
    SET_MAIN_TITLE(context, newTitle) {
      context.commit('setMainTitle', newTitle);
    },
    SET_MAIN_TITLE_ERROR(context, error) {
      context.commit('setMainTitleError', error);
    },
    SET_MAIN_SUBMITTING(context, value) {
      context.commit('setMainSubmitting', value);
    }
  },
};