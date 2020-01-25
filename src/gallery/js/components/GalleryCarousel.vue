<template>
    <v-carousel
            height="100%"
            width="100%"
            hide-delimiter-background
            show-arrows-on-hover
            prev-icon="chevron_left"
            next-icon="chevron_right"
            :dark="$vuetify.theme.dark"
            :light="!$vuetify.theme.dark"
    >
        <v-carousel-item
                height="100%"
                width="100%"
                v-for="(post, i) in galleryPosts"
                :key="i">
            <gallery-detail
                    :gallery-post="post">
            </gallery-detail>
        </v-carousel-item>
    </v-carousel>
</template>

<script>
  import { mapGetters, mapActions } from 'vuex';
  import GalleryDetail from "@/gallery/js/components/GalleryDetail";
  export default {
    name: "GalleryCarousel",
    components: {GalleryDetail},
    data() {
      return {
        bodyElm: null,
        noScrollClass: 'gm-frontend-gallery-body--no-scroll'
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
        SET_OPENED_POST_INDEX: 'galleryData/SET_OPENED_POST_INDEX'
      }),
      checkBodyClass() {
        return this.bodyElm.classList.contains(this.noScrollClass);
      }
    },
    computed: {
      galleryPosts() {
        return this.getGalleryPosts();
      }
    },
    mounted() {
      const bodyCollection = document.getElementsByTagName('body');
      if (bodyCollection.length > 0) {
        this.bodyElm = bodyCollection[0];
      }
      if (!this.checkBodyClass()) {
        this.bodyElm.classList.add(this.noScrollClass);
      }
    },
    beforeDestroy() {
      if (this.checkBodyClass()) {
        this.bodyElm.classList.remove(this.noScrollClass);
      }
    }
  }
</script>

<style scoped>

</style>