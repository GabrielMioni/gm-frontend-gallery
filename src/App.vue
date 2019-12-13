<template>
    <div>
        <div v-for="galleryItem in galleryItems">
            <img v-for="image in galleryItem.images" :src="image['sized_images'].medium" alt="">
        </div>
    </div>
</template>

<script>
  export default {
    name: 'gmGallery',
    data() {
      return {
        galleryItems: '',
      }
    },
    methods: {
      setGalleryItems() {
        const self = this;
        let xhr = new XMLHttpRequest();
        xhr.open('GET', '/wp-json/gm-frontend-gallery/v1/get/');
        xhr.onload = () => {
          self.galleryItems = JSON.parse(xhr.responseText);
        };
        xhr.send();
      }
    },
    mounted() {
      console.log('mounted');
      this.setGalleryItems();
    }
  }
</script>