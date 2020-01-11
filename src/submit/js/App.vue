<template>
    <v-app id="gm-frontend-submit">
        <v-container fluid>
            <v-text-field
                    v-model="mainTitle"
                    :error-messages="mainTitleError"
                    :label="'Title'">
            </v-text-field>
            <transition-group name="fade">
                <gallery-post
                        v-for="(post, index) in galleryPosts"
                        v-bind:key="`submitPost-${post.uniqueId}`"
                        :index="index"
                        :post-state="post">
                </gallery-post>
            </transition-group>
            <div class="gm-frontend-submit__footer">
                <v-input v-if="allowedAttachmentsMessage.length > 0" class="gm-frontend-submit__max-attachment-message"
                         :messages="allowedAttachmentsMessage">
                </v-input>
                <v-btn class="gm-frontend-submit__footer__add-one-button" large color="teal" :ref="'addPostButton'" @click.stop="addPost">
                    Add A Post!
                </v-btn>
                <submit-post-button :disabled="errorsPresentMessage.length > 0">
                    Submit
                </submit-post-button>
            </div>
            <portal-target name="modals" slim></portal-target>
        </v-container>
    </v-app>
</template>

<script>
  import GalleryPost from "./components/GalleryPost/GalleryPost";
  import SubmitPostButton from "./components/SubmitPostButton";
  import { mapGetters, mapActions } from 'vuex';
  export default {
    name: "gmGallerySubmit",
    components: {SubmitPostButton, GalleryPost},
    methods: {
      ...mapActions({
        SET_MAIN_TITLE: 'mainData/SET_MAIN_TITLE',
        SET_MAIN_TITLE_ERROR: 'mainData/SET_MAIN_TITLE_ERROR',
        SET_MAIN_NONCE: 'mainData/SET_MAIN_NONCE',
        SET_MAIN_OPTIONS: 'mainData/SET_MAIN_OPTIONS',
        ADD_POST: 'postData/ADD_POST'
      }),
      ...mapGetters({
        getMainTitle: 'mainData/getMainTitle',
        getMainTitleError: 'mainData/getMainTitleError',
        getMainSubmitting: 'mainData/getMainSubmitting',
        getMainOptions: 'mainData/getMainOptions',
        getGalleryPosts: 'postData/getGalleryPosts',
        getGalleryPostsLength: 'postData/getGalleryPostsLength',
      }),
      addPost() {
        if (this.galleryPosts.length >= this.options['maxAttachments']) {
          return;
        }
        this.ADD_POST();
      },
      generateKey() {
        const rand = Math.floor(Math.random() * 100);
        return `submitPost-${rand}`
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
      mainTitleError: {
        get() {
          return this.getMainTitleError();
        }
      },
      galleryPosts: {
        get() {
          return this.getGalleryPosts();
        }
      },
      submitting: {
        get() {
          return this.getMainSubmitting();
        }
      },
      options: {
        get() {
          return this.getMainOptions();
        }
      },
      errorsPresentMessage: {
        get() {
          let errorsPresent = false;
          this.galleryPosts.map((galleryPost) => {
            const errors = Object.values(galleryPost.errors);
            errors.map((error)=>{
              if (!errorsPresent && error.trim() !== '') {
                errorsPresent = true;
              }
            })
          });

          if (this.getMainTitleError() !== '' && !errorsPresent) {
            errorsPresent = true;
          }

          return errorsPresent === true ? 'Please check the errors above before submitting' : '';
        }
      },
      allowedAttachmentsMessage: {
        get() {
          const maxAttachments = this.options.maxAttachments;
          if (maxAttachments <= 1) {
            return '';
          }
          return `Gallery attachments: ${this.getGalleryPostsLength()}/${maxAttachments}`;
        }
      }
    },
    created() {
      const mount = document.getElementById('gm-frontend-submit');

      this.SET_MAIN_NONCE(mount.dataset.nonce);
      this.SET_MAIN_OPTIONS(mount.dataset.options);
    },
  }
</script>