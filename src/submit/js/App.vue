<template>
    <div id="gm-frontend-submit">
        <form>
            <div class="gm-frontend-submit-form-group">
                <label for="post_title">Title</label>
                <input v-model="mainTitle" type="text" name="post_title" id="post_title">
            </div>
        </form>
        <transition-group name="fade">
            <submit-post
                    v-for="(post, index) in galleryPosts"
                    v-bind:key="`submitPost-${index}`"
                    :index="index">
            </submit-post>
        </transition-group>
        <button @click.stop="addPost">Add A Post!</button>
        <button @click.stop="submitPost">Submit</button>
    </div>
</template>

<script>
  import SubmitPost from "./components/SubmitPost";
  import { mapGetters, mapActions } from 'vuex';
  import axios from "axios";
  export default {
    name: "gmGallerySubmit",
    components: {SubmitPost},
    methods: {
      ...mapActions([
        'SET_MAIN_TITLE',
        'SET_POST_NONCE',
        'ADD_POST',
      ]),
      ...mapGetters([
        'getMainTitle',
        'getPostNonce',
        'getGalleryPosts'
      ]),
      postObjectDefault() {
        return {
          content: '',
          imageUrl: null,
          file: null,
        }
      },
      addPost() {
        return this.ADD_POST();
        // this.$store.commit('addGalleryPost');
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
          return this.getMainTitle();
        },
        set(newTitle) {
          return this.SET_MAIN_TITLE(newTitle);
        }
      },
      postNonce: {
        get() {
          return this.getPostNonce();
        },
        set(newPostNonce) {
          return this.SET_POST_NONCE(newPostNonce);
        }
      },
      galleryPosts: {
        get() {
          return this.getGalleryPosts();
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
      this.postNonce = mount.dataset.nonce;
    },
  }
</script>