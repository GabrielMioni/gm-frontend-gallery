<template>
    <v-hover>
        <template v-slot:default="{ hover }">
            <v-card
                    v-ripple
                    class="gm-frontend-gallery-image"
            >
                <v-img
                        :src="mainImage"
                        contain
                        v-bind:class="{'gm-frontend-gallery-image__main-image--loading': imageLoading}"
                        class="grey darken-4"
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
                        <v-progress-circular indeterminate size="64"></v-progress-circular>
                    </v-overlay>
                </v-fade-transition>
            </v-card>
        </template>
    </v-hover>
</template>

<script>
  export default {
    name: "GalleryPostImage",
    data() {
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
      loadMainImage() {
        const image = new Image();
        image.src = this.getSizedImage('medium');
        image.onload = () => {
          setTimeout(()=>{
            this.imageLoading = false;
          }, 500);
        }
      },
      getSizedImage(size) {
        return this.galleryPost.images[0]['sized_images'][size];
      },
    },
    computed: {
      mainImage() {
        if (this.imageLoading) {
          return this.getSizedImage('thumbnail');
        }
        return this.getSizedImage('medium');
      }
    },
    mounted() {
      this.loadMainImage();
    }
  }
</script>