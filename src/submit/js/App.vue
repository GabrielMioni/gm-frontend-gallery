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
                        v-bind:key="`submitPost-${index}`"
                        :index="index"
                        :post-state="post">
                </gallery-post>
            </transition-group>
            <button
                    :ref="'addPostButton'"
                    @click.stop="addPost">
                Add A Post!
            </button>
            <submit-post-button>Submit</submit-post-button>
            <portal-target name="modals" slim></portal-target>
        </v-container>
    </v-app>
    <!--<div id="gm-frontend-submit" v-bind:class="{ 'gm-frontend-submit&#45;&#45;active' : submitting }">
        <v-text-field
                class="blah"
                v-model="mainTitle"
                :error-messages="mainTitleError"
                :label="'Title'">
        </v-text-field>
        <transition-group name="fade">
            <gallery-post
                    v-for="(post, index) in galleryPosts"
                    v-bind:key="`submitPost-${index}`"
                    :index="index"
                    :post-state="post">
            </gallery-post>
        </transition-group>
        <button
                :ref="'addPostButton'"
                @click.stop="addPost">
            Add A Post!
        </button>
        <submit-post-button>Submit</submit-post-button>
        <portal-target name="modals" slim></portal-target>
    </div>-->
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