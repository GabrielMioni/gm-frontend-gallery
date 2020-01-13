<template>
    <v-hover>
        <template v-slot:default="{ hover }">
            <v-card class="gm-frontend-gallery-image">
                <v-img
                        :src="mainImage"
                        class="grey darken-4"
                        height="100%"
                        width="100%">
                </v-img>
                <v-fade-transition>
                    <v-overlay
                            v-if="hover"
                            absolute
                            color="black"
                    >
                        <v-btn>See more info</v-btn>
                    </v-overlay>
                </v-fade-transition>
            </v-card>
        </template>
    </v-hover>
</template>

<script>
  export default {
    name: "GalleryPostImage",
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
    computed: {
      mainImage: {
        get() {
          return this.galleryPost.images[0]['sized_images'].full;
        }
      }
    }
  }
</script>