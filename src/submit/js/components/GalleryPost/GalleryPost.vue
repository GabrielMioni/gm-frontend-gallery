<template>
    <div class="gm-frontend-gallery-post">
        <div class="gm-frontend-gallery-post-trash gm-frontend-gallery-post-trash--full">
            <trash-post-button :post-state="postState"></trash-post-button>
        </div>
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
    </div>
</template>

<script>
  import TrashPostButton from "./TrashPostButton";
  import GalleryPostImage from "./GalleryPostImage";
  import GalleryPostContent from "./GalleryPostContent";
  import { mapGetters } from 'vuex';
  export default {
    name: "GalleryPost",
    components: {GalleryPostContent, GalleryPostImage, TrashPostButton},
    props: {
      index: Number,
    },
    data() {
      return {
        galleryDataAccessor: this.getGalleryPostDataFunction(),
        postState: this.getPostState()
      }
    },
    methods: {
      ...mapGetters({
        getGalleryPostDataFunction: 'postData/getGalleryPostDataFunction'
      }),
      getPostState() {
        const galleryPostDataFunction = this.getGalleryPostDataFunction();

        return galleryPostDataFunction({
          index: this.index
        });
      }
    }
  }
</script>