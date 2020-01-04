<template>
    <button @click.stop="checkShowModal">
        x
        <portal to="modals" v-if="showModal">
            <confirmation-modal
                    :confirm-is-dangerous="true"
                    @confirmNo="confirmNoHandler"
                    @confirmYes="deletePost">
                Are you sure you want to delete this post?
            </confirmation-modal>
        </portal>
    </button>
</template>

<script>
  import ConfirmationModal from "../../../../utilities/vue/components/ConfirmationModal";
  import { imageUrlValidator} from "../../../../utilities/helpers";
  import { mapActions } from 'vuex';
  export default {
    name: "TrashPostButton",
    components: {ConfirmationModal},
    props: {
      index: {
        type: Number,
        required: true
      },
      imageUrl: {
        validator: imageUrlValidator,
        required: true,
      },
      content: {
        type: String,
        required: true,
      },
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
        if (this.content.trim() !== '' || this.imageUrl !== null) {
          this.showModal = true;
          return;
        }
        this.deletePost();
      },
      confirmNoHandler() {
        this.showModal = false;
      },
      deletePost() {
        this.REMOVE_POST(this.index);
        this.showModal = false;
      }
    }
  }
</script>