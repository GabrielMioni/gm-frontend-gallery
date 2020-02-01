<template>
    <v-card class="gm-frontend-gallery__detail">
        <div class="gm-frontend-gallery__detail__col-1">
            <div class="gm-frontend-gallery__detail__image-area">
                <v-card class="gm-frontend-gallery__detail__image-area__selected-image">
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
                        class="gm-frontend-gallery__detail__image-area__attached-images"
                >
                    <v-card
                            class="gm-frontend-gallery__detail__image-area__attached-images__image"
                            v-for="(image, index) in galleryPost.images"
                            :key="index"
                            @click="chooseAttachedImage(index)"
                            v-bind:class="{'gm-frontend-gallery__detail__image-area__attached-images__image--active': selectedImageIndex === index}"
                    >
                        <v-img
                                cover
                                height="100%"
                                width="100%"
                                :src="image['sized_images'].medium"
                        />
                    </v-card>
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
</template>

<script>
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