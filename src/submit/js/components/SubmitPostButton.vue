<template>
    <loading-button :loading="submitting" :click-action="submitPosts">
        <template slot="defaultText">
            Submit
        </template>
        <template slot="loadingText">
            Loading
        </template>
        <confirmation-modal
                slot="confirmationModal"
                v-if="showModal"
                :single-button="true"
                :confirm-is-dangerous="false"
                @confirmNo="confirmNoHandler">
            Your gallery submission was successful!
            <div slot="confirmNo">
                Return to Gallery Submit Form
            </div>
        </confirmation-modal>
    </loading-button>
</template>

<script>
  import axios from "axios";
  import { mapGetters, mapActions } from 'vuex';
  import ConfirmationModal from "@/utilities/vue/components/ConfirmationModal";
  import LoadingButton from "@/utilities/vue/components/LoadingButton";
  export default {
    name: "SubmitPostButton",
    components: {LoadingButton, ConfirmationModal},
    data() {
      return {
        showModal: false,
      }
    },
    methods: {
      ...mapActions({
        SET_MAIN_TITLE_ERROR: 'mainData/SET_MAIN_TITLE_ERROR',
        SET_MAIN_SUBMITTING: 'mainData/SET_MAIN_SUBMITTING',
        SET_POST_ERROR: 'postData/SET_POST_ERROR',
        RESET_MAIN_DATA: 'mainData/RESET_MAIN_DATA',
        RESET_GALLERY_POST_DATA: 'postData/RESET_GALLERY_POST_DATA'
      }),
      ...mapGetters({
        getMainTitle: 'mainData/getMainTitle',
        getMainNonce: 'mainData/getMainNonce',
        getMainSubmitting: 'mainData/getMainSubmitting',
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
        if (this.submitting) {
          return;
        }

        const formData = this.createValidateFormData();
        if (formData === false) {
          return;
        }

        const self = this;
        self.submitting = true;
        const mainNonce = self.getMainNonce();

        axios.post('/wp-json/gm-frontend-gallery/v1/submit/', formData, {
          headers: {
            'Content-Type': 'multipart/form-data',
            'X-WP-Nonce': mainNonce,
          }
        })
        .then((response)=>{
          setTimeout(()=>{
            self.showModal = true;
            self.submitting = false;
          }, 1000);
        }).catch((error)=>{
          const responseData = error.response.data;
          self.error = responseData.message;
        });
      },
      confirmNoHandler() {
        this.RESET_MAIN_DATA();
        this.RESET_GALLERY_POST_DATA();
        this.showModal = false;
      }
    },
    computed: {
      submitting: {
        get() {
          return this.getMainSubmitting();
        },
        set(value) {
          return this.SET_MAIN_SUBMITTING(value);
        }
      }
    }
  }
</script>