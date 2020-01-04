<template>
    <div class="gm-frontend-gallery-post">
        <div class="gm-frontend-gallery-post-trash gm-frontend-gallery-post-trash--full">
            <trash-post-button :post-state="postState"></trash-post-button>
        </div>
        <div class="gm-frontend-gallery-post-left">
            <gallery-post-image
                    :index="index"
                    :getGalleryDataByIndex="getGalleryDataByIndex">
            </gallery-post-image>
        </div>
        <div class="gm-frontend-gallery-post-right">
            <gallery-post-content
                    :index="index"
                    :getGalleryDataByIndex="getGalleryDataByIndex">
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
        // postState: this.getGalleryDataByIndex({index: this.index})
        postState: this.setPostState()
      }
    },
    methods: {
      ...mapGetters({
        getGalleryPostDataFunction: 'postData/getGalleryPostDataFunction'
      }),
      setPostState() {
        const galleryPostDataFunction = this.getGalleryPostDataFunction();

        return galleryPostDataFunction({
          index: this.index
        });
      },
      getGalleryDataByIndex(data) {
        data = data == null ? {} : data;
        let payload = {
          index: this.index,
        };
        if (typeof data.type !== 'undefined') {
          payload.type = data.type;
        }
        if (typeof data.deepKey !== 'undefined') {
          payload.deepKey = data.deepKey;
        }

        return this.galleryDataAccessor(payload);
      }
    }
  }
</script>