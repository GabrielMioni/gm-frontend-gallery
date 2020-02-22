<template>
  <div class="gm-frontend-gallery-post">
    <v-card class="gm-frontend-gallery-post-card">
      <div class="gm-frontend-gallery-post-card-left">
        <gallery-post-image
          :index="index"
          :image-url="postState.imageUrl"
          :image-file="postState.file"
          :image-url-error="postState.errors.imageUrl">
        </gallery-post-image>
      </div>
      <div class="gm-frontend-gallery-post-card-right">
        <gallery-post-content
          :index="index"
          :content="postState.content"
          :content-error="postState.errors.content">
        </gallery-post-content>
      </div>
      <div class="gm-frontend-gallery-post-card__controls">
        <trash-post-button
          :index="index"
          :image-url="postState.imageUrl"
          :content="postState.content">
        </trash-post-button>
        <gallery-post-button-add
          :index="index">
        </gallery-post-button-add>
      </div>
    </v-card>
  </div>

</template>

<script>
  import TrashPostButton from "./TrashPostButton";
  import GalleryPostImage from "./GalleryPostImage";
  import GalleryPostContent from "./GalleryPostContent";
  import GalleryPostButtonAdd from "./GalleryPostButtonAdd";

  export default {
    name: "GalleryPost",
    components: {GalleryPostContent, GalleryPostImage, TrashPostButton, GalleryPostButtonAdd},
    props: {
      index: {
        type: Number,
        required: true
      },
      postState: {
        type: Object,
        required: true
      }
    }
  }
</script>