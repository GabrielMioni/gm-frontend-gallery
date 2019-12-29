<template>
    <div class="gm-frontend-confirmation">
        <div class="gm-frontend-confirmation-modal">
            <div>
                <slot>Please confirm</slot>
            </div>
            <div>
                <button>Cancel</button>
                <button>Ok</button>
            </div>
        </div>
    </div>
</template>

<script>
  export default {
    name: "ConfirmationModal"
  }
</script>

<style scoped>

</style>