export const mainDataModule = {
  namespaced: true,
  state: {
    mainNonce: null,
    mainTitle: '',
    mainTitleError: '',
  },
  getters: {
    getMainNonce: state => state.postNonce,
    getMainTitle: state => state.mainTitle,
    getMainTitleError: state => state.mainTitleError,
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
  },
};