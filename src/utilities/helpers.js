export const defaultGalleryPostObject = () => {
  return {
    content: '',
    imageUrl: null,
    file: null,
    errors: {
      content: '',
      imageUrl: '',
      file: '',
    }
  }
};

export const imageUrlValidator = prop => typeof prop === 'string' || prop === null;

export const getOptionsType = (getMainOptions, type) => {
  if (typeof getMainOptions === 'function') {
    const options = getMainOptions();
    return options[type];
  }
  return getMainOptions[type];
};