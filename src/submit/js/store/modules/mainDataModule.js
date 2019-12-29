export const mainDataModule = {
  namespaced: true,
  state: {
    mainTitle: '',
    mainTitleError: '',
    mainNonce: null,
  },
  getters: {
    getMainTitle: state => state.mainTitle,
    getMainNonce: state => state.postNonce,
    getMainTitleError: state => state.mainTitleError,
  },
  mutations: {
    setMainTitle(state, newTitle) {
      state.mainTitle = newTitle;
    },
    setMainNonce(state, nonce) {
      state.postNonce = nonce;
    },
    setMainTitleError(state, error) {
      state.mainTitleError = error;
    },
  },
  actions: {
    SET_MAIN_TITLE(context, newTitle) {
      context.commit('setMainTitle', newTitle);
    },
    SET_MAIN_NONCE(context, postNonce) {
      context.commit('setMainNonce', postNonce);
    },
    SET_MAIN_TITLE_ERROR(context, error) {
      context.commit('setMainTitleError', error);
    },
  },
};