<template>
    <button class="gm-frontend-loading-button" @click.stop="clickAction()">
        <span class="gm-frontend-loading-button-main">
            <span v-bind:class="{ 'gm-frontend-loading-button-main--show' : !loading }">
                <slot name="defaultText">
                    Submit
                </slot>
            </span>
            <span v-bind:class="{ 'gm-frontend-loading-button-main--show' : loading }">
                <slot name="loadingText">
                    Loading
                </slot>
            </span>
        </span>
        <slot name="confirmationModal"></slot>
    </button>
</template>

<script>
  export default {
    name: "LoadingButton",
    props: {
      loading: {
        type: Boolean,
        default: false,
      },
      clickAction: {
        type: Function,
        required: true,
      }
    }
  }
</script>