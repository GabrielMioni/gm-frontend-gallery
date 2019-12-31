<template>
    <button class="gm-frontend-loading-button"
            :ref="'loadingButton'"
            @click.stop="doClick()">
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
        <portal to="modals">
            <slot name="confirmationModal"></slot>
        </portal>
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
    },
    methods: {
      doClick() {
        this.$refs.loadingButton.blur();
        this.clickAction();
      }
    }
  }
</script>