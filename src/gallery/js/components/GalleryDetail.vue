<template>
    <div class="gm-frontend-gallery__detail">
        <v-card
                :dark="false"
                class="gm-frontend-gallery__detail__main"
        >
            <v-btn
                    class="gm-frontend-gallery__detail__close-button"
                    icon
                    @click="closeCarousel"
            >
                <v-icon>close</v-icon>
            </v-btn>
            <div class="gm-frontend-gallery__detail__col-1">
                <v-img
                        :src="selectedImage"
                        contain
                        height="90%"
                        width="90%"
                        class="grey darken-4"
                ></v-img>
            </div>
            <div class="gm-frontend-gallery__detail__col-2">

            </div>
        </v-card>
    </div>
</template>

<script>
  import { mapActions } from 'vuex';
  export default {
    name: "GalleryDetail",
    data() {
      return {
        selectedImageIndex: 0
      }
    },
    props: {
      galleryPost: {
        type: Object,
        required: true
      }
    },
    methods: {
      ...mapActions({
        SET_OPENED_POST_INDEX: 'galleryData/SET_OPENED_POST_INDEX'
      }),
      closeCarousel() {
        this.SET_OPENED_POST_INDEX(null);
      }
    },
    computed: {
      selectedImage() {
        return this.galleryPost.images[0]['sized_images'].full;
      }
    }
  }
</script>