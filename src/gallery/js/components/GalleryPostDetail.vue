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
                    />
                </div>
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
                    <v-fade-transition
                            group
                            origin="0 0"
                    >
                        <div
                                v-for="(image, index) in galleryPost.images"
                                v-show="selectedImageIndex === index"
                                :key="index"
                        >
                            {{image.content}}
                        </div>
                    </v-fade-transition>
                </div>
                <!--<v-fade-transition>
                    <div>
                        {{galleryPost.images[selectedImageIndex].content}}
                    </div>
                </v-fade-transition>-->
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