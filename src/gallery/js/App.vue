<template>
    <v-app
            :dark="false"
            id="gm-frontend-gallery">
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
            <gallery-carousel
                    v-if="showCarousel"
                    v-on:close="close"
            >
            </gallery-carousel>
        </v-container>
    </v-app>
</template>

<script>
  import GalleryPostImage from "./components/galleryPostImage"
  import GalleryLightBox from "./components/GalleryLightBox";
  import GalleryCarousel from "./components/GalleryCarousel";
  import { mapGetters, mapActions } from 'vuex';
  export default {
    name: 'gmGallery',
    components: {GalleryCarousel, GalleryLightBox, GalleryPostImage},
    data() {
      return {
        showCarousel: true,
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
      close() {
        this.showCarousel = false;
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
      this.SET_GALLERY_POSTS(1000);
    }
  }
</script>