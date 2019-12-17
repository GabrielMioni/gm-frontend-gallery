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
                v-if="openedPostIndex !== null"
                :post="galleryPosts[openedPostIndex]"
                :loading="lightBoxLoading">
            </GalleryLightBox>
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
        lightBoxLoading: false,
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
        this.loadPost(postIndex);
        this.openedPostIndex = postIndex;
      },
      closePostHandler() {
        this.openedPostIndex = null;
      },
      galleryNavigateHandler(data) {
        let newIndex = data === 'left' ? this.openedPostIndex -1 : this.openedPostIndex +1;

        if (newIndex < 0) {
          newIndex = this.galleryPosts.length -1;
        }
        if (newIndex > this.galleryPosts.length -1) {
          newIndex = 0;
        }
        this.loadPost(newIndex);
        this.openedPostIndex = newIndex;
      },
      loadPost(postIndex) {
        const self = this;
        const postImages = self.galleryPosts[postIndex].images;
        let loadedImageCount = 0;

        self.lightBoxLoading = true;

        postImages.forEach((image) => {
          const sizedImages = image['sized_images'];

          let loadingImage = new Image();
          loadingImage.src = sizedImages['full'];
          loadingImage.onload = () => {
            ++loadedImageCount;

            if (loadedImageCount === postImages.length) {
              setTimeout(() => {
                self.lightBoxLoading = false;
              }, 1000);
            }
          };
        });
      }
    },
    mounted() {
      console.log('mounted');
      this.setGalleryItems();
    }
  }
</script>