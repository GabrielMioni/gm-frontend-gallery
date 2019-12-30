<template>
    <div class="gm-frontend-confirmation">
        <div class="gm-frontend-confirmation-modal">
            <h5>
                <slot>Please confirm</slot>
            </h5>
            <div>
                <button @click.stop="confirmNo">Cancel</button>
                <button @click.stop="confirmYes">Ok</button>
            </div>
        </div>
    </div>
</template>

<script>
  export default {
    name: "ConfirmationModal",
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