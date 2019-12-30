<template>
    <div class="gm-frontend-confirmation">
        <div class="gm-frontend-confirmation-modal">
            <h5>
                <slot>Please confirm</slot>
            </h5>
            <div>
                <button @click.stop="confirmNo">Cancel</button>
                <button v-if="!singleButton"
                        @click.stop="confirmYes"
                        v-bind:class="{ 'gm-frontend-confirmation-modal-button--danger': confirmIsDangerous } ">
                    Ok
                </button>
            </div>
        </div>
    </div>
</template>

<script>
  export default {
    name: "ConfirmationModal",
    props: {
      'singleButton' : {
        default: false,
        type: Boolean
      },
      'confirmIsDangerous' : {
        default: false,
        type: Boolean
      }
    },
    methods: {
      confirmNo() {
        this.$emit('confirmNo');
      },
      confirmYes() {
        this.$emit('confirmYes');
      }
    }
  }
</script>