<template>
    <button @click.stop="checkShowModal">
        x
        <portal to="modals" v-if="showModal">
            <confirmation-modal
                    :confirm-is-dangerous="true"
                    @confirmNo="confirmNoHandler"
                    @confirmYes="confirmYesHandler">
                Are you sure you want to delete this post?
            </confirmation-modal>
        </portal>
    </button>
</template>

<script>
  import ConfirmationModal from "./ConfirmationModal";
  import { mapActions } from 'vuex';
  export default {
    name: "TrashPostButton",
    components: {ConfirmationModal},
    props: {
      index: {
        type: Number,
        required: true
      },
      getGalleryDataByIndex: {
        type: Function,
        required: true
      }
    },
    data() {
      return {
        showModal: false,
      }
    },
    methods: {
      ...mapActions({
        REMOVE_POST: 'postData/REMOVE_POST',
      }),
      checkShowModal() {
        const galleryData = this.getGalleryDataByIndex();

        if (
          galleryData.content.trim() !== '' ||
          galleryData.file !== null ||
          galleryData.imageUrl !== null
        ) {
          this.showModal = true;
        } else {
          this.confirmYesHandler();
        }
      },
      confirmNoHandler() {
        this.showModal = false;
      },
      confirmYesHandler() {
        this.REMOVE_POST();
        this.showModal = false;
      }
    }
  }
</script>