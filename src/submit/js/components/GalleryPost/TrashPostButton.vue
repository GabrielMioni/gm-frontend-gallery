<template>
    <v-btn class="gm-frontend-gallery-post-trash-button"
           fab dark small color="grey darken-4"
           @click.stop="checkShowModal">
        <v-icon>close</v-icon>
        <portal to="modals" v-if="showModal">
            <confirmation-modal
                    :confirm-is-dangerous="true"
                    @confirmNo="cancelDelete"
                    @confirmYes="deletePost">
                Are you sure you want to delete this post?
            </confirmation-modal>
        </portal>
    </v-btn>
</template>

<script>
  import ConfirmationModal from "@/utilities/vue/components/ConfirmationModal";
  import { imageUrlValidator} from "@/utilities/helpers";
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
      cancelDelete() {
        this.showModal = false;
      },
      deletePost() {
        new Promise((resolve) => {
          resolve(this.REMOVE_POST(this.index));
        }).then(()=>{
          this.showModal = false;
        });
      }
    }
  }
</script>