<template>
    <div id="gm-frontend-submit" v-bind:class="{ 'gm-frontend-submit--active' : submitting }">
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
            <gallery-post
                    v-for="(post, index) in galleryPosts"
                    v-bind:key="`submitPost-${index}`"
                    :index="index">
            </gallery-post>
        </transition-group>
        <button
                :ref="'addPostButton'"
                @click.stop="addPost">Add A Post!</button>
        <submit-post-button>Submit</submit-post-button>
        <portal-target name="modals" slim></portal-target>
    </div>
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
        getGalleryPosts: 'postData/getGalleryPosts',
        getMainSubmitting: 'mainData/getMainSubmitting',
        getMainOptions: 'mainData/getMainOptions'
      }),
      addPost() {
        this.$refs.addPostButton.blur();
        if (this.galleryPosts.length >= this.options['maxAttachments']) {
          return;
        }
        this.ADD_POST();
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
      }
    },
    created() {
      const mount = document.getElementById('gm-frontend-submit');

      this.SET_MAIN_NONCE(mount.dataset.nonce);
      this.SET_MAIN_OPTIONS(mount.dataset.options);
    },
  }
</script>