<template>
    <button @click.stop="checkShowModal">
        x
        <portal to="modals" v-if="showModal">
            <confirmation-modal
                    @confirmNo="confirmNoHandler"
                    @confirmYes="confirmYesHandler">
                Are you sure you want to delete this post?
            </confirmation-modal>
        </portal>
    </button>
</template>

<script>
  import ConfirmationModal from "./ConfirmationModal";
  export default {
    name: "TrashPostButton",
    components: {ConfirmationModal},
    props: {
      galleryDataAccessor: Function,
      galleryDataDelete: Function,
    },
    data() {
      return {
        showModal: false,
      }
    },
    methods: {
      checkShowModal() {
        const galleryData = this.galleryDataAccessor();

        if (
          galleryData.content.trim() !== '' ||
          galleryData.file !== null ||
          galleryData.imageUrl !== null
        ) {
          this.showModal = true;
        } else {
          this.confirmYes();
        }
      },
      confirmNoHandler() {
        this.showModal = false;
      },
      confirmYesHandler() {
        this.galleryDataDelete();
        this.showModal = false;
      }
    }
  }
</script>