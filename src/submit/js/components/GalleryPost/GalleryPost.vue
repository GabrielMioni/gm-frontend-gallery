<template>
    <div class="gm-frontend-gallery-post-container">
        <v-card :class="'gm-frontend-gallery-post'">
            <div class="gm-frontend-gallery-post-left">
                <gallery-post-image
                        :index="index"
                        :image-url="postState.imageUrl"
                        :image-url-error="postState.errors.imageUrl">
                </gallery-post-image>
            </div>
            <div class="gm-frontend-gallery-post-right">
                <gallery-post-content
                        :index="index"
                        :content="postState.content"
                        :content-error="postState.errors.content">
                </gallery-post-content>
            </div>
        </v-card>
        <trash-post-button
                :index="index"
                :image-url="postState.imageUrl"
                :content="postState.content">
        </trash-post-button>
    </div>

</template>

<script>
  import TrashPostButton from "./TrashPostButton";
  import GalleryPostImage from "./GalleryPostImage";
  import GalleryPostContent from "./GalleryPostContent";
  export default {
    name: "GalleryPost",
    components: {GalleryPostContent, GalleryPostImage, TrashPostButton},
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