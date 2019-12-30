<template>
    <button @click.stop="submitPosts">
        <slot>Submit</slot>
        <portal to="modals" v-if="showModal">
            <confirmation-modal
                    :confirm-is-dangerous="false"
                    @confirmNo="confirmNoHandler"
                    @confirmYes="confirmYesHandler">
                Your gallery submission was successful!
            </confirmation-modal>
        </portal>
    </button>
</template>

<script>
  import axios from "axios";
  import { mapGetters, mapActions } from 'vuex';
  import ConfirmationModal from "./ConfirmationModal";
  export default {
    name: "SubmitPostButton",
    components: {ConfirmationModal},
    data() {
      return {
        showModal: false,
      }
    },
    methods: {
      ...mapActions({
        SET_MAIN_TITLE_ERROR: 'mainData/SET_MAIN_TITLE_ERROR',
        SET_POST_ERROR: 'postData/SET_POST_ERROR',
      }),
      ...mapGetters({
        getMainTitle: 'mainData/getMainTitle',
        getMainNonce: 'mainData/getMainNonce',
        getGalleryPosts: 'postData/getGalleryPosts'
      }),
      createValidateFormData() {
        let attachmentContents = [];
        let formData = new FormData();
        let hasErrors = false;

        const galleryPosts = this.getGalleryPosts();

        galleryPosts.map((galleryPost, index) => {
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

        const mainTitle = this.getMainTitle();

        if (mainTitle === '') {
          this.SET_MAIN_TITLE_ERROR('A title is required');
          hasErrors = true;
        }

        if (hasErrors) {
          return false;
        }

        formData.append('mainTitle', mainTitle);
        formData.append('attachmentContents', JSON.stringify(attachmentContents));

        return formData;
      },
      submitPosts() {
        const formData = this.createValidateFormData();
        if (formData === false) {
          return;
        }

        const self = this;
        const mainNonce = this.getMainNonce();

        axios.post('/wp-json/gm-frontend-gallery/v1/submit/', formData, {
          headers: {
            'Content-Type': 'multipart/form-data',
            'X-WP-Nonce': mainNonce,
          }
        })
          .then((response)=>{
            setTimeout(()=>{
              self.showModal = true;
            }, 1000);
          }).catch((error)=>{
          const responseData = error.response.data;
          this.error = responseData.message;
        });
      },
      confirmNoHandler() {
        this.showModal = false;
      },
      confirmYesHandler() {
        this.showModal = false;
      }
    },
  }
</script>