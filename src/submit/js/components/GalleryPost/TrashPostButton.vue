<template>
    <v-btn class="gm-frontend-gallery-post-trash-button"
           fab dark small color="grey darken-4"
           :ref="'deleteButton'"
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
  import { mapGetters, mapActions } from 'vuex';
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
      ...mapGetters({
        getGalleryPostsLength: 'postData/getGalleryPostsLength'
      }),
      ...mapActions({
        REMOVE_POST: 'postData/REMOVE_POST',
      }),
      checkShowModal() {
        const deleteButton = this.$refs.deleteButton.$el;
        deleteButton.blur();

        if (this.content.trim() !== '' || this.imageUrl !== null) {
          this.showModal = true;
          return;
        }
        this.deletePost(deleteButton);
      },
      cancelDelete() {
        this.showModal = false;
      },
      deletePost(buttonElm) {
        if (this.getGalleryPostsLength() <= 1) {
          const shakeClass = 'gm-frontend-shake';

          if (!buttonElm.classList.contains(shakeClass)) {
            buttonElm.classList.add('gm-frontend-shake');

            setTimeout(()=>{
              buttonElm.classList.remove('gm-frontend-shake');
            }, 1000);
          }

          return;
        }

        new Promise((resolve) => {
          resolve(this.REMOVE_POST(this.index));
        }).then(()=>{
          this.showModal = false;
        });
      }
    }
  }
</script>