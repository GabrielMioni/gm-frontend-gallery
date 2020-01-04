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