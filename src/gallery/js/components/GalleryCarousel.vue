<template>
    <v-carousel
            v-bind:value="currentIndex"
            class="gm-frontend-gallery__carousel"
            height="100%"
            width="100%"
            hide-delimiter-background
            show-arrows
            prev-icon="chevron_left"
            next-icon="chevron_right"
            :dark="$vuetify.theme.dark"
            :light="!$vuetify.theme.dark"
            :continuous="false"
            :hide-delimiters="true"
    >
        <v-btn
                class="gm-frontend-gallery__carousel__close-button"
                icon
                @click="closeCarousel"
        >
            <v-icon>close</v-icon>
        </v-btn>
        <v-carousel-item
                height="100%"
                width="100%"
                v-for="(post, i) in galleryPosts"
                :key="i"
        >
            <gallery-post-detail
                    :gallery-post="post"
            >
            </gallery-post-detail>
        </v-carousel-item>
    </v-carousel>
</template>

<script>
  import { mapGetters, mapActions } from 'vuex';
  import GalleryPostDetail from "@/gallery/js/components/GalleryPostDetail";
  export default {
    name: "GalleryCarousel",
    components: {GalleryPostDetail},
    methods: {
      ...mapGetters({
        getGalleryCount: 'galleryData/getGalleryCount',
        getGalleryPosts: 'galleryData/getGalleryPosts',
        getOpenedPostIndex: 'galleryData/getOpenedPostIndex',
        getGalleryLoading: 'galleryData/getGalleryLoading'
      }),
      ...mapActions({
        SET_OPENED_POST_INDEX: 'galleryData/SET_OPENED_POST_INDEX'
      }),
      closeCarousel() {
        this.SET_OPENED_POST_INDEX(null);
      },
      navigateGalleryHandler(e) {
        const key = e.key;
        const allowedKeys = ['ArrowLeft', 'ArrowRight', 'Escape'];
        if (allowedKeys.indexOf(key) < 0) {
          return;
        }
        if (key === 'Escape') {
          this.closeCarousel();
          return;
        }
        let newOpenedPostIndex = key === 'ArrowLeft' ? this.currentIndex - 1 : this.currentIndex + 1;
        if (newOpenedPostIndex < 0 ||
            newOpenedPostIndex > this.galleryPosts.length -1 ||
            newOpenedPostIndex === this.currentIndex) {
          return;
        }
        this.SET_OPENED_POST_INDEX(newOpenedPostIndex);
      }
    },
    computed: {
      galleryPosts() {
        return this.getGalleryPosts();
      },
      currentIndex() {
        return this.getOpenedPostIndex();
      }
    },
    mounted() {
      const scrollY = document.getElementsByTagName('html')[0].scrollTop;
      document.body.style.top = `-${scrollY}px`;
      document.body.style.position = 'fixed';
      document.addEventListener('keyup', this.navigateGalleryHandler, true);
    },
    beforeDestroy() {
      const scrollY = document.body.style.top;
      document.body.style.position = '';
      document.body.style.top = '';
      window.scrollTo(0, parseInt(scrollY || '0') * -1);
      document.removeEventListener('keyup', this.navigateGalleryHandler, true);
    }
  }
</script>