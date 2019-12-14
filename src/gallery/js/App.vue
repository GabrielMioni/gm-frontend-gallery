<template>
    <div id="gm-frontend-gallery">
        <template v-for="galleryItem in galleryItems">
            <GalleryPost
                :post="galleryItem"
                @open-post="openPostHandler">
            </GalleryPost>
        </template>
    </div>
</template>

<script>
  import GalleryPost from "./components/GalleryPost";
  export default {
    name: 'gmGallery',
    components: {GalleryPost},
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
      },
      openPostHandler(data) {
        console.log(data);
      }
    },
    mounted() {
      console.log('mounted');
      this.setGalleryItems();
    }
  }
</script>