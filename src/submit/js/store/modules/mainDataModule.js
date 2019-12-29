export const mainDataModule = {
  namespaced: true,
  state: {
    mainTitle: '',
    mainTitleError: '',
    mainNonce: null,
  },
  getters: {
    getMainTitle: state => state.mainTitle,
    getMainTitleError: state => state.mainTitleError,
    getMainNonce: state => state.postNonce,
  },
  mutations: {
    setMainTitle(state, newTitle) {
      state.mainTitle = newTitle;
    },
    setMainTitleError(state, error) {
      state.mainTitleError = error;
    },
    setMainNonce(state, nonce) {
      state.postNonce = nonce;
    },
  },
  actions: {
    SET_MAIN_TITLE(context, newTitle) {
      context.commit('setMainTitle', newTitle);
    },
    SET_MAIN_TITLE_ERROR(context, error) {
      context.commit('setMainTitleError', error);
    },
    SET_MAIN_NONCE(context, postNonce) {
      context.commit('setMainNonce', postNonce);
    },
  },
};