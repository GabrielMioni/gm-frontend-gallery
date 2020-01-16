<template>
    <div class="gm-frontend-gallery__detail">
        <v-card
                :dark="false"
                class="gm-frontend-gallery__detail__main">
            <div class="gm-frontend-gallery__detail__col-1">
                <v-img
                        :src="selectedImage"
                        contain
                        height="100%"
                        width="100%"
                ></v-img>
            </div>
            <div class="gm-frontend-gallery__detail__col-2">

            </div>
        </v-card>
    </div>
</template>

<script>
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
    computed: {
      selectedImage() {
        console.log(this.galleryPost);
        return this.galleryPost.images[0]['sized_images'].full;
      }
    }
  }
</script>