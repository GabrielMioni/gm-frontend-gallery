<template>
    <div id="gm-frontend-submit">
        <form>
            <div class="gm-frontend-submit-form-group">
                <label for="post_title">
                    <span>Title</span>
                    <span class="gm-frontend-submit-error">
                        <transition name="fade">
                            <div v-if="titleError !== ''">
                                {{ titleError }}
                            </div>
                        </transition>
                    </span>
                </label>
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
        <button @click.stop="submitPosts">Submit</button>
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
        'SET_POST_ERROR',
        'SET_MAIN_TITLE_ERROR',
        'ADD_POST',
      ]),
      ...mapGetters([
        'getMainTitle',
        'getPostNonce',
        'getMainTitleError',
        'getGalleryPosts'
      ]),
      addPost() {
        return this.ADD_POST();
      },
      submitPosts() {
        let attachmentContents = [];
        let formData = new FormData();
        let hasErrors = false;

        this.galleryPosts.map((galleryPost, index) => {
          attachmentContents.push(galleryPost.content);
          const imageFile = galleryPost.file;

          if (imageFile === null) {
            this.SET_POST_ERROR({
              'index': index,
              'type': 'imageUrl',
              'error': 'An image is required',
            });
            hasErrors = true;
          }

          formData.append('image_files[]', galleryPost.file);
        });

        if (this.mainTitle.trim() === '') {
          this.SET_MAIN_TITLE_ERROR('A title is required');
          hasErrors = true;
        }

        if (hasErrors) {
          return;
        }

        formData.append('mainTitle', this.mainTitle);
        formData.append('attachmentContents', JSON.stringify(attachmentContents));

        axios.post('/wp-json/gm-frontend-gallery/v1/submit/', formData, {
          headers: {
            'Content-Type': 'multipart/form-data',
            'X-WP-Nonce': this.postNonce,
          }
        })
        .then((response)=>{

        }).catch((error)=>{
          const responseData = error.response.data;
          this.error = responseData.message;
        });
      }
    },
    computed: {
      mainTitle: {
        get() {
          return this.getMainTitle();
        },
        set(newTitle) {
          if (this.getMainTitleError() !== '') {
            this.SET_MAIN_TITLE_ERROR('');
          }
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
      titleError: {
        get() {
          return this.getMainTitleError();
        },
        set(error) {
          return this.SET_MAIN_TITLE_ERROR(error);
        }
      }
    },
    created() {
      const mount = document.getElementById('gm-frontend-submit');
      this.postNonce = mount.dataset.nonce;
    },
  }
</script>