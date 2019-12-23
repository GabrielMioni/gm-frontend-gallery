<template>
    <div id="gm-frontend-submit">
        <form>
            <div class="gm-frontend-submit-form-group">
                <label for="post_title">Title</label>
                <input v-model="mainTitle" type="text" name="post_title" id="post_title">
            </div>
        </form>
        <template v-for="(post, index) in this.$store.getters.galleryPosts">
            <submit-post :post="post" :index="index">
            </submit-post>
        </template>
        <button @click.stop="addPost">Add A Post!</button>
        <button @click.stop="submitPost">Submit</button>
    </div>
</template>

<script>
  import SubmitPost from "./components/SubmitPost";
  import axios from "axios";
  export default {
    name: "gmGallerySubmit",
    components: {SubmitPost},
    methods: {
      postObjectDefault() {
        return {
          content: '',
          imageUrl: null,
          file: null,
        }
      },
      addPost() {
        this.$store.commit('addGalleryPost');
      },
      imageUpdateHandler(data) {
        const currentGalleryPost = this.galleryPosts[data.index];
        currentGalleryPost.file = data.file;
        currentGalleryPost.imageUrl = data.imageUrl;
      },
      submitPost() {
        let attachmentContents = [];
        let formData = new FormData();

        this.$store.getters.galleryPosts.map((galleryPost) => {
          attachmentContents.push(galleryPost.content);
          formData.append('image_files[]', galleryPost.file);
        });

        formData.append('postNonce', this.postNonce);
        formData.append('mainTitle', this.mainTitle);
        formData.append('attachmentContents', JSON.stringify(attachmentContents));

        axios.post('/wp-json/gm-frontend-gallery/v1/submit/', formData, { headers: { 'Content-Type': 'multipart/form-data' } })
          .then((response)=>{

          }).catch((error)=>{

        });
      }
    },
    computed: {
      mainTitle: {
        get() {
          return this.$store.state.mainTitle;
        },
        set(value) {
          this.$store.commit('updateTitle', value);
        }
      }
    },
    created() {
      const mount = document.getElementById('gm-frontend-submit');
      //this.postNonce = mount.dataset.nonce;
      this.$store.commit('updatePostNonce', mount.dataset.nonce);
    },
  }
</script>