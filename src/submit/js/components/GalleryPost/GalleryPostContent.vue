<template>
    <form class="gm-frontend-gallery-post-content">
        <div class="gm-frontend-submit-form-group">
            <label :for="setElementId('gm-frontend-submit-content')">
                Content
                <span class="gm-frontend-submit-error">
                    <transition name="fade">
                        <div v-if="contentError !== ''">
                            {{ contentError }}
                        </div>
                    </transition>
                </span>
            </label>
            <textarea
                    v-model="postContent"
                    :id="setElementId('gm-frontend-submit-content')">
            </textarea>
        </div>
    </form>
</template>

<script>
  import { mapActions } from 'vuex';
  export default {
    name: "GalleryPostContent",
    props: {
      index: {
        type: Number,
        required: true
      },
      getGalleryDataByIndex: {
        type: Function,
        required: true
      }
    },
    methods: {
      ...mapActions({
        SET_POST_CONTENT: 'postData/SET_POST_CONTENT',
        SET_POST_ERROR: 'postData/SET_POST_ERROR'
      }),
      setElementId(idName) {
        return `${idName}-${this.index}`;
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