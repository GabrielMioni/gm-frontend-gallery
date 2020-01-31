<template>
    <div class="gm-frontend-gallery__detail">
        <v-card
                class="gm-frontend-gallery__detail__main"
        >
            <div class="gm-frontend-gallery__detail__col-1">
                <v-card class="gm-frontend-gallery__detail__col-1__selected-image">
                    <v-img
                            :src="selectedImage"
                            contain
                            height="100%"
                            width="100%"
                            class="grey darken-4"
                    />
                </v-card>
                <div
                        v-if="galleryPost.images.length > 1"
                        class="gm-frontend-gallery__detail__col-1__attached-images"
                >
                    <div
                            class="gm-frontend-gallery__detail__col-1__attached-images__image"
                            v-for="(image, index) in galleryPost.images"
                            :key="index"
                            @click="chooseAttachedImage(index)"
                            v-bind:class="{'gm-frontend-gallery__detail__col-1__attached-images__image--active': selectedImageIndex === index}"
                    >
                        <img :src="image['sized_images'].medium">
                    </div>
                </div>
            </div>
            <div class="gm-frontend-gallery__detail__col-2">
                <h3>{{galleryPost.post_title}}</h3>
                <div class="gm-frontend-gallery__detail__col-2__content">
                    <transition name="fade" mode="out-in">
                        <p :key="selectedImageIndex">
                            {{ galleryPost.images[selectedImageIndex].content }}
                        </p>
                    </transition>
                </div>
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
      },
      chooseAttachedImage(index) {
        this.selectedImageIndex = index;
      }
    },
    computed: {
      selectedImage() {
        return this.galleryPost.images[this.selectedImageIndex]['sized_images'].full;
      }
    }
  }
</script>