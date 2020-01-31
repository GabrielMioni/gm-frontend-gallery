<template>
    <div class="gm-frontend-gallery__detail">
        <v-card
                class="gm-frontend-gallery__detail__main"
        >
            <div class="gm-frontend-gallery__detail__col-1">
                <div class="gm-frontend-gallery__detail__col-1__selected-image">
                    <v-img
                            :src="selectedImage"
                            contain
                            height="90%"
                            width="90%"
                            class="gm-frontend-gallery__detail__col-1__selected-image__main grey darken-4"
                    ></v-img>
                </div>
                <div
                        v-if="galleryPost.images.length > 1"
                        class="gm-frontend-gallery__detail__col-1__attached-images"
                >
                    <!--<v-img
                            v-for="(image, index) in galleryPost.images"
                            :src="image['sized_images'].medium"
                            :key="index"
                            height="100%"
                            contain
                    ></v-img>-->
                    <div
                            class="gm-frontend-gallery__detail__col-1__attached-images__container"
                            v-for="(image, index) in galleryPost.images"
                            :key="index"
                    >
                        <img
                                class="gm-frontend-gallery__detail__col-1__attached-images__image"
                                :src="image['sized_images'].medium"
                        >
                    </div>
                </div>
            </div>
            <div class="gm-frontend-gallery__detail__col-2">

            </div>
        </v-card>
    </div>
</template>

<script>
  import { mapActions } from 'vuex';
  export default {
    name: "GalleryPostDetail",
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
        return this.galleryPost.images[this.selectedImageIndex]['sized_images'].full;
      }
    }
  }
</script>