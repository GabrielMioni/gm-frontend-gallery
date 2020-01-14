<template>
    <v-app id="gm-frontend-gallery">
        <v-container fluid>
            <div class="gm-frontend-gallery">
                <v-fade-transition
                        class="gm-frontend-gallery__main"
                        group>
                    <gallery-post-image
                            v-for="(galleryPost, index) in galleryPosts"
                            v-if="galleryPost.images.length > 0"
                            :gallery-post="galleryPost"
                            :index="index"
                            :key="index"
                    >
                    </gallery-post-image>
                </v-fade-transition>
            </div>
            <div class="gm-frontend-gallery__footer">
                <v-btn
                        v-if="galleryPosts.length < getGalleryCount()"
                        color="primary"
                        :loading="getGalleryLoading()"
                        @click="SET_GALLERY_POSTS(1000)"
                >
                    Load More
                </v-btn>
            </div>
        </v-container>
    </v-app>
</template>

<script>
  import GalleryPost from "./components/GalleryPost";
  import GalleryPostImage from "./components/galleryPostImage"
  import GalleryLightBox from "./components/GalleryLightBox";
  import LoadingButton from "@/utilities/vue/components/LoadingButton";
  import { mapGetters, mapActions } from 'vuex';
  export default {
    name: 'gmGallery',
    components: {LoadingButton, GalleryLightBox, GalleryPost, GalleryPostImage},
    data() {
      return {
        // galleryPosts: [],
        //openedPostIndex: null,
        //galleryLoading: true,
        lightBoxLoading: false,
        // pageLoaded: 1,
        // postsPerPage: 10,
        // galleryCount: 0,
      }
    },
    methods: {
      ...mapGetters({
        getGalleryCount: 'galleryData/getGalleryCount',
        getGalleryPosts: 'galleryData/getGalleryPosts',
        getOpenedPostIndex: 'galleryData/getOpenedPostIndex',
        getGalleryLoading: 'galleryData/getGalleryLoading'
      }),
      ...mapActions({
        SET_GALLERY_POSTS: 'galleryData/SET_GALLERY_POSTS',
        SET_OPENED_POST_INDEX: 'galleryData/SET_OPENED_POST_INDEX'
      }),
      setGalleryItems() {
        const self = this;
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
    computed: {
      galleryPosts() {
        return this.getGalleryPosts();
      },
      openedPostIndex: {
        get() {
          return this.getOpenedPostIndex();
        },
        set(index) {
          return this.SET_OPENED_POST_INDEX(index)
        }
      }
    },
    mounted() {
      // console.log('mounted');
      this.SET_GALLERY_POSTS(1000);
      // this.setGalleryItems(false);
    }
  }
</script>