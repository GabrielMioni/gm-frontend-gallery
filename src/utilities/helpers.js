export const defaultGalleryPostObject = (uniqueId) => {
  return {
    content: '',
    imageUrl: null,
    file: null,
    uniqueId: uniqueId,
    errors: {
      content: '',
      imageUrl: '',
      file: '',
    }
  }
};

export const setUniqueIds = () => {
  return 'abcdefghijklmnopqrstuvwxy'.split('');
};

export const imageUrlValidator = prop => typeof prop === 'string' || prop === null;

export const getOptionsType = (getMainOptions, type) => {
  if (typeof getMainOptions === 'function') {
    const options = getMainOptions();
    return options[type];
  }
  return getMainOptions[type];
};

export const applyShake = (elm, time) => {
  const shakeClass = 'gm-frontend-shake';

  if (elm.classList.contains(shakeClass)) {
    return;
  }

  elm.classList.add(shakeClass);

  setTimeout(()=>{
    elm.classList.remove(shakeClass);
  }, time);
};