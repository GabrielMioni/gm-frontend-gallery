<template>
    <div id="gm-frontend-submit">
        <form>
            <div class="gm-frontend-submit-form-group">
                <label for="post_title">
                    <span>Title</span>
                    <span class="gm-frontend-submit-error">
                        <transition name="fade">
                            <div v-if="mainTitleError !== ''">
                                {{ mainTitleError }}
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
        <portal-target name="modals" slim />
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
      ...mapActions({
        SET_MAIN_TITLE: 'mainData/SET_MAIN_TITLE',
        SET_MAIN_TITLE_ERROR: 'mainData/SET_MAIN_TITLE_ERROR',
        SET_MAIN_NONCE: 'mainData/SET_MAIN_NONCE',
        ADD_POST: 'postData/ADD_POST',
        SET_POST_ERROR: 'postData/SET_POST_ERROR',
      }),
      ...mapGetters({
        getMainTitle: 'mainData/getMainTitle',
        getMainTitleError: 'mainData/getMainTitleError',
        getMainNonce: 'mainData/getMainNonce',
        getGalleryPosts: 'postData/getGalleryPosts'
      }),
      addPost() {
        return this.ADD_POST();
      },
      createValidateFormData() {
        let attachmentContents = [];
        let formData = new FormData();
        let hasErrors = false;

        this.galleryPosts.map((galleryPost, index) => {
          const postContent = galleryPost.content;
          const imageFile = galleryPost.file;

          if (postContent.trim() === '') {
            this.SET_POST_ERROR({
              'index': index,
              'type': 'content',
              'error': 'Content is required',
            });
          } else {
            attachmentContents.push(postContent);
          }

          if (imageFile === null) {
            this.SET_POST_ERROR({
              'index': index,
              'type': 'imageUrl',
              'error': 'An image is required',
            });
            hasErrors = true;
          } else {
            formData.append('image_files[]', imageFile);
          }
        });

        if (this.mainTitle.trim() === '') {
          this.SET_MAIN_TITLE_ERROR('A title is required');
          hasErrors = true;
        }

        if (hasErrors) {
          return false;
        }

        formData.append('mainTitle', this.mainTitle);
        formData.append('attachmentContents', JSON.stringify(attachmentContents));

        return formData;
      },
      submitPosts() {
        const formData = this.createValidateFormData();
        if (formData === false) {
          return;
        }

        axios.post('/wp-json/gm-frontend-gallery/v1/submit/', formData, {
          headers: {
            'Content-Type': 'multipart/form-data',
            'X-WP-Nonce': this.mainNonce,
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
      mainNonce: {
        get() {
          return this.getMainNonce();
        },
        set(newPostNonce) {
          return this.SET_MAIN_NONCE(newPostNonce);
        }
      },
      galleryPosts: {
        get() {
          return this.getGalleryPosts();
        }
      },
      mainTitleError: {
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
      this.mainNonce = mount.dataset.nonce;
    },
  }
</script>