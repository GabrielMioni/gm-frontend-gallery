<template>
    <button class="gm-frontend-loading-button" @click.stop="submitAction()">
        <span class="gm-frontend-loading-button-main">
            <span v-bind:class="{ 'gm-frontend-submit-post-button-main--show' : !loading }">
                <slot name="defaultText">
                    Submit
                </slot>
            </span>
            <span v-bind:class="{ 'gm-frontend-submit-post-button-main--show' : loading }">
                <slot name="loadingText">
                    Loading
                </slot>
            </span>
        </span>
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
      submitAction: {
        type: Function,
        required: true,
      }
    }
  }
</script>