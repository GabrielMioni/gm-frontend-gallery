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
        @confirmYes="deletePost()">
        Are you sure you want to delete this post?
      </confirmation-modal>
    </portal>
  </v-btn>
</template>

<script>
import ConfirmationModal from '@/utilities/vue/components/ConfirmationModal'
import { imageUrlValidator, applyShake } from '@/utilities/helpers'
import { mapGetters, mapActions } from 'vuex'

export default {
  name: 'TrashPostButton',
  components: { ConfirmationModal },
  props: {
    index: {
      type: Number,
      required: true
    },
    imageUrl: {
      validator: imageUrlValidator,
      required: true
    },
    content: {
      type: String,
      required: true
    }
  },
  data () {
    return {
      showModal: false
    }
  },
  methods: {
    ...mapGetters({
      getGalleryPostsLength: 'postData/getGalleryPostsLength'
    }),
    ...mapActions({
      REMOVE_POST: 'postData/REMOVE_POST',
      CLEAR_POST: 'postData/CLEAR_POST'
    }),
    checkShowModal () {
      const galleryPostLength = this.getGalleryPostsLength()

      if (galleryPostLength <= 1) {
        applyShake(this.$refs.deleteButton.$el, 1000)
        return
      }

      if (this.content.trim() !== '' || this.imageUrl !== null) {
        this.showModal = true
        return
      }

      this.deletePost(galleryPostLength)
    },
    cancelDelete () {
      this.showModal = false
    },
    deletePost (galleryPostLength) {
      new Promise((resolve) => {
        resolve(this.REMOVE_POST(this.index))
        /* if (galleryPostLength > 1) {
            resolve(this.REMOVE_POST(this.index));
          }
          resolve(this.CLEAR_POST(this.index)); */
      }).then(() => {
        this.showModal = false
      })
    }
  }
}
</script>
