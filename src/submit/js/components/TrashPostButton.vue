<template>
    <button @click="showModal = true">
        x
        <portal to="modals" v-if="showModal">
            <confirmation-modal></confirmation-modal>
        </portal>
    </button>
</template>

<script>
  import ConfirmationModal from "./ConfirmationModal";
  export default {
    name: "TrashPostButton",
    components: {ConfirmationModal},
    data() {
      return {
        showModal: false,
      }
    },
  }
</script>