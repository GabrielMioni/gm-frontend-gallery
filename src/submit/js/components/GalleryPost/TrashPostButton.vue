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
  import ConfirmationModal from "../../../../utilities/vue/components/ConfirmationModal";
  import { mapActions } from 'vuex';
  export default {
    name: "TrashPostButton",
    components: {ConfirmationModal},
    props: {
      postState: {
        type: Object,
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
        if (
          this.postState.content.trim() !== '' ||
          this.postState.file !== null ||
          this.postState.imageUrl !== null
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
        this.REMOVE_POST(this.index);
        this.showModal = false;
      }
    }
  }
</script>