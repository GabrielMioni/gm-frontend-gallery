<template>
    <div class="gm-frontend-confirmation">
        <div class="gm-frontend-confirmation-modal">
            <div>
                <slot>Please confirm</slot>
            </div>
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