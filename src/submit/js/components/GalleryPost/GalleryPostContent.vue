<template>
    <v-card class="gm-frontend-gallery-post-content">
        <v-textarea
                v-model="postContent"
                solo
                flat
                :counter="maxContentLength"
                :name="setElementId('gm-frontend-submit-content')"
                :error-messages="contentError"
                label="Content"
                hint="Say something about the image"
        ></v-textarea>
    </v-card>
</template>

<script>
  import { mapGetters, mapActions } from 'vuex';
  import { getOptionsType } from '@/utilities/helpers';
  export default {
    name: "GalleryPostContent",
    props: {
      index: {
        type: Number,
        required: true
      },
      content: {
        type: String,
        required: true
      },
      contentError: {
        type: String,
        required: true
      }
    },
    methods: {
      ...mapActions({
        SET_POST_CONTENT: 'postData/SET_POST_CONTENT',
        SET_POST_ERROR: 'postData/SET_POST_ERROR'
      }),
      ...mapGetters({
        getMainOptions: 'mainData/getMainOptions'
      }),
      setElementId(idName) {
        return `${idName}-${this.index}`;
      },
    },
    computed: {
      postContent: {
        get() {
          return this.content;
        },
        set(value) {
          if (this.contentError !== '') {
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
      maxContentLength: {
        get() {
          return getOptionsType(this.getMainOptions, 'maxContentLength');
        }
      }
    }
  }
</script>