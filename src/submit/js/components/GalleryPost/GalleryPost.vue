<template>
    <div class="gm-frontend-gallery-post">
        <div class="gm-frontend-gallery-post-trash gm-frontend-gallery-post-trash--full">
            <trash-post-button
                    :galleryDataAccessor="getGalleryDataByIndex"
                    :galleryDataDelete="removePost">
            </trash-post-button>
        </div>
        <div class="gm-frontend-gallery-post-left">
            <gallery-post-image
                    :index="index"
                    :get-gallery-data-by-index="getGalleryDataByIndex">
            </gallery-post-image>
        </div>
        <div class="gm-frontend-gallery-post-right">
            <form>
                <div class="gm-frontend-submit-form-group">
                    <label :for="setElementId('gm-frontend-submit-content')">
                        Content
                        <div class="gm-frontend-submit-error">
                            <transition name="fade">
                                <div v-if="contentError !== ''">
                                    {{ contentError }}
                                </div>
                            </transition>
                        </div>
                    </label>
                    <textarea v-model="postContent" :id="setElementId('gm-frontend-submit-content')">
                    </textarea>
                </div>
            </form>
        </div>
    </div>
</template>

<script>
  import TrashPostButton from "../TrashPostButton";
  import GalleryPostImage from "./GalleryPostImage";
  import { mapGetters, mapActions } from 'vuex';
  export default {
    name: "GalleryPost",
    components: {GalleryPostImage, TrashPostButton},
    props: {
      index: Number,
    },
    data() {
      return {
        galleryDataAccessor: this.getGalleryPostData(),
      }
    },
    methods: {
      ...mapActions({
        SET_POST_CONTENT: 'postData/SET_POST_CONTENT',
        SET_POST_ERROR: 'postData/SET_POST_ERROR',
        REMOVE_POST: 'postData/REMOVE_POST',
      }),
      ...mapGetters({
        getGalleryPostData: 'postData/getGalleryPostData',
        getMainOptions: 'mainData/getMainOptions'
      }),
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
      },
      setElementId(idName) {
        return `${idName}-${this.index}`;
      },
      removePost() {
        this.REMOVE_POST(this.index);
      },
    },
    computed: {
      postContent: {
        get() {
          return this.getGalleryDataByIndex({
            type: 'content'
          });
        },
        set(value) {
          const galleryPostContentError = this.getGalleryDataByIndex({
            type: 'errors',
            deepKey: 'content',
          });
          if (galleryPostContentError !== '') {
            this.SET_POST_ERROR({
              index: this.index,
              type: 'content',
              error: '',
            });
          }
          return this.SET_POST_CONTENT({
            index: this.index,
            data: value,
          });
        }
      },
      contentError: {
        get() {
          return this.getGalleryDataByIndex({
            type: 'errors',
            deepKey: 'content'
          });
        },
        set(error) {
          return this.SET_POST_ERROR({
            index: this.index,
            type: 'content',
            error: error,
          });
        }
      }
    }
  }
</script>