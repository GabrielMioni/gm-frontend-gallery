<template>
    <div id="gm-frontend-gallery">
        <template v-for="(galleryPost, index) in galleryPosts">
            <GalleryPost
                :post="galleryPost"
                :index="index"
                @open-post="openPostHandler">
            </GalleryPost>
        </template>
        <transition name="fade">
            <GalleryLightBox
                @close-post="closePostHandler"
                @galleryNavigate="galleryNavigateHandler"
                v-if="openedPostIndex !== null" :post="galleryPosts[openedPostIndex]"></GalleryLightBox>
        </transition>
    </div>
</template>

<script>
  import GalleryPost from "./components/GalleryPost";
  import GalleryLightBox from "./components/GalleryLightBox";
  export default {
    name: 'gmGallery',
    components: {GalleryLightBox, GalleryPost},
    data() {
      return {
        galleryPosts: '',
        openedPostIndex: null,
      }
    },
    methods: {
      setGalleryItems() {
        const self = this;
        let xhr = new XMLHttpRequest();
        xhr.open('GET', '/wp-json/gm-frontend-gallery/v1/get/1/10');
        xhr.onload = () => {
          self.galleryPosts = JSON.parse(xhr.responseText);
        };
        xhr.send();
      },
      openPostHandler(postIndex) {
        this.openedPostIndex = postIndex;
      },
      closePostHandler() {
        this.openedPostIndex = null;
      },
      galleryNavigateHandler(data) {
        console.log('galleryNavigateHandler', data);
      }
    },
    mounted() {
      console.log('mounted');
      this.setGalleryItems();
    }
  }
</script>