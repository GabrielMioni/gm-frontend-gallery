<template>
    <v-card
            class="gm-frontend-gallery__detail__image-area__attached-images__image"
            @click="updateSelectedImageIndex"
            @keypress.enter="updateSelectedImageIndex"
    >
        <v-img
                cover
                height="100%"
                width="100%"
                :src="image['sized_images'].medium"
        />
    </v-card>
</template>

<script>
  export default {
    name: "GalleryPostDetailAttachedImage",
    props: {
      image: {
        type: Object,
        required: true,
      },
      index: {
        type: Number,
        required: true,
      }
    },
    methods: {
      updateSelectedImageIndex() {
        this.$emit('updateSelectedImageIndex', this.index);
      }
    }
  }
</script>