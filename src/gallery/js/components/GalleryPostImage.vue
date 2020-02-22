<template>
  <v-hover>
    <template v-slot:default="{ hover }">
      <v-card
        v-ripple
        class="gm-frontend-gallery-image"
        @click.stop="openCarousel(index)"
      >
        <v-img
          :src="mainImage"
          :lazy-src="thumbImage"
          @load="loadingComplete"
          contain
          v-bind:class="{'gm-frontend-gallery-image__main-image--loading': imageLoading}"
          height="100%"
          width="100%"
        >
        </v-img>
        <v-fade-transition>
          <v-overlay
            v-if="hover && !imageLoading"
            absolute
            color="black"
          >
            <v-btn>See more info</v-btn>
          </v-overlay>
          <v-overlay v-if="imageLoading" absolute>
            <v-progress-circular indeterminate color="grey lighten-5"></v-progress-circular>
          </v-overlay>
        </v-fade-transition>
      </v-card>
    </template>
  </v-hover>
</template>

<script>
  import {mapGetters, mapActions} from 'vuex';

  export default {
    name: "GalleryPostImage",
    data () {
      return {
        imageLoading: true
      }
    },
    props: {
      galleryPost: {
        type: Object,
        required: true,
      },
      index: {
        type: Number,
        required: true
      }
    },
    methods: {
      ...mapActions({
        SET_GALLERY_POSTS: 'galleryData/SET_GALLERY_POSTS',
        SET_OPENED_POST_INDEX: 'galleryData/SET_OPENED_POST_INDEX'
      }),
      isGif (image) {
        return image.match(/\.(gif)$/) != null;
      },
      getSizedImage (size) {
        return this.galleryPost.images[0]['sized_images'][size];
      },
      loadingComplete () {
        setTimeout(() => {
          this.imageLoading = false;
        }, 1000);
      },
      openCarousel (index) {
        this.SET_OPENED_POST_INDEX(index);
      }
    },
    computed: {
      thumbImage () {
        return this.getSizedImage('thumbnail');
      },
      mainImage () {
        const fullImage = this.getSizedImage('full');
        if (this.isGif(fullImage)) {
          return fullImage;
        }
        return this.getSizedImage('medium');
      },
    }
  }
</script>