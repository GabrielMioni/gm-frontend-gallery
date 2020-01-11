<template>
    <div class="gm-frontend-gallery-add-post-button">
        <v-btn
                @click="addPost"
                fab dark small color="primary">
            <v-icon>add</v-icon>
        </v-btn>
    </div>
</template>

<script>
  import { mapActions } from 'vuex';
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
      addPost() {
        console.log('galleryPostAddIndex', this.index);
        this.ADD_POST_AT_INDEX(this.index);
      },
    }
  }
</script>