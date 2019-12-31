export const mainDataModule = {
  namespaced: true,
  state: {
    mainNonce: null,
    mainTitle: '',
    mainTitleError: '',
    mainSubmitting: false,
    mainOptions: {}
  },
  getters: {
    getMainNonce: state => state.mainNonce,
    getMainTitle: state => state.mainTitle,
    getMainTitleError: state => state.mainTitleError,
    getMainSubmitting: state => state.mainSubmitting,
    getMainOptions: state => state.mainOptions
  },
  mutations: {
    setMainNonce(state, nonce) {
      state.mainNonce = nonce;
    },
    setMainTitle(state, newTitle) {
      state.mainTitle = newTitle;
    },
    setMainTitleError(state, error) {
      state.mainTitleError = error;
    },
    setMainSubmitting(state, value) {
      state.mainSubmitting = value;
    },
    resetMainData(state) {
      state.mainTitle = '';
      state.mainTitleError = '';
      state.mainSubmitting = false;
    },
    setMainOptions(state, value) {
      state.mainOptions = JSON.parse(value);
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
    },
    RESET_MAIN_DATA(context) {
      context.commit('resetMainData');
    },
    SET_MAIN_OPTIONS(context, value) {
      context.commit('setMainOptions', value);
    }
  },
};