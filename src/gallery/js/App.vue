<template>
    <div id="gm-frontend-main">
        <div class="gm-frontend-gallery">
            <div class="gm-frontend-gallery-posts">
                <template v-for="(galleryPost, index) in galleryPosts">
                    <GalleryPost
                            @open-post="openPostHandler"
                            :post="galleryPost"
                            :index="index">
                    </GalleryPost>
                </template>
            </div>
            <div class="gm-frontend-gallery-loading">
                <div v-if="galleryLoading">Loading!</div>
                <button v-if="!galleryLoading && galleryPosts.length < galleryCount" @click.stop="setGalleryItems">Load More</button>
            </div>
            <transition name="fade">
                <GalleryLightBox v-if="openedPostIndex !== null"
                                 @closePost="closePostHandler"
                                 @galleryNavigate="galleryNavigateHandler"
                                 :post="galleryPosts[openedPostIndex]"
                                 :loading="lightBoxLoading">
                </GalleryLightBox>
            </transition>
        </div>
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
        galleryPosts: [],
        openedPostIndex: null,
        galleryLoading: true,
        lightBoxLoading: false,
        pageLoaded: 1,
        postsPerPage: 10,
        galleryCount: 0,
      }
    },
    methods: {
      setGalleryItems(event) {
        const self = this;
        if (event.type === 'click' && this.galleryLoading === true) {
          return;
        }
        self.galleryLoading = true;
        let xhr = new XMLHttpRequest();
        xhr.open('GET', `/wp-json/gm-frontend-gallery/v1/get/${self.pageLoaded}/${self.postsPerPage}`);
        xhr.onload = () => {
          const responseData = JSON.parse(xhr.responseText);
          const galleryPosts = responseData.posts;
          self.galleryCount = parseInt(responseData['gallery_count']);

          self.preloadImages(galleryPosts, () => {
            setTimeout(() => {
              self.galleryLoading = false;
              self.galleryPosts = self.galleryPosts.concat(galleryPosts);
            }, 1000);

            ++self.pageLoaded;
          });
        };
        xhr.send();
      },
      preloadImages(galleryPosts, callback) {
        let loadedImageCount = 0;

        galleryPosts.forEach((post) => {
          const images = post['images'];

          images.forEach((image) => {
            const medium = image['sized_images'].medium;
            let currentImage = new Image();

            currentImage.src = medium;
            currentImage.onload = () => {
              ++loadedImageCount;

              if (loadedImageCount === galleryPosts.length) {
                callback();
              }
            };
          })
        })
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

          let currentImage = new Image();
          currentImage.src = sizedImages.full;
          currentImage.onload = () => {
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
      this.setGalleryItems(false);
    }
  }
</script>