<template>
    <div id="gm-frontend-submit">
        <form>
            <div class="gm-frontend-submit-form-group">
                <label for="post_title">Title</label>
                <input v-model="mainTitle" type="text" name="post_title" id="post_title">
            </div>
        </form>
        <submit-post
                v-for="(post, index) in this.$store.getters.galleryPosts"
                v-bind:key="index"
                :index="index">
        </submit-post>
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

        formData.append('mainTitle', this.mainTitle);
        formData.append('attachmentContents', JSON.stringify(attachmentContents));

        axios.post('/wp-json/gm-frontend-gallery/v1/submit/', formData, {
          headers: {
            'Content-Type': 'multipart/form-data',
            'X-WP-Nonce': this.$store.getters.postNonce,
          }
        })
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
      },
      galleryCount: {
        get() {
          return this.$store.state.galleryPosts.length;
        }
      }
    },
    created() {
      const mount = document.getElementById('gm-frontend-submit');
      this.$store.commit('updatePostNonce', mount.dataset.nonce);
    },
  }
</script>