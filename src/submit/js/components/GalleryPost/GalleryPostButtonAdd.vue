<template>
  <div class="gm-frontend-gallery-add-post-button">
    <v-btn
      @click="addPost"
      ref="addPostButton"
      fab dark small color="primary">
      <v-icon>add</v-icon>
    </v-btn>
  </div>
</template>

<script>
  import {mapActions, mapGetters} from 'vuex';
  import {applyShake} from "@/utilities/helpers";

  export default {
    name: "GalleryPostButtonAdd",
    props: {
      index: {
        type: Number,
        required: true
      }
    },
    methods: {
      ...mapActions({
        ADD_POST_AT_INDEX: 'postData/ADD_POST_AT_INDEX',
      }),
      ...mapGetters({
        getMainOptions: 'mainData/getMainOptions',
        getGalleryPostsLength: 'postData/getGalleryPostsLength'
      }),
      addPost () {
        if (this.getGalleryPostsLength() >= this.maxAttachments) {
          applyShake(this.$refs['addPostButton'].$el, 1000);
          return;
        }

        this.ADD_POST_AT_INDEX(this.index);
      },
    },
    computed: {
      maxAttachments: {
        get () {
          const options = this.getMainOptions();
          return options['maxAttachments'];
        }
      }
    }
  }
</script>