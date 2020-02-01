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
                    <gallery-post-detail-attached-image
                            v-for="(image, index) in galleryPost.images"
                            :image="image"
                            :index="index"
                            :key="index"
                            @updateSelectedImageIndex="chooseAttachedImage(index)"
                            v-bind:class="{'gm-frontend-gallery__detail__image-area__attached-images__image--active': selectedImageIndex === index}"
                    />
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
  import GalleryPostDetailAttachedImage from "@/gallery/js/components/GalleryPostDetailAttachedImage";
  export default {
    name: "GalleryPostDetail",
    components: {GalleryPostDetailAttachedImage},
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