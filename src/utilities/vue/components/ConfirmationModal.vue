<template>
    <div class="gm-frontend-confirmation">
        <v-card class="gm-frontend-confirmation-modal">
            <h5>
                <slot>Please confirm</slot>
            </h5>
            <div>
                <v-btn
                        ref="cancelButton"
                        color="primary"
                        @click.stop="confirmNo">
                    <slot name="confirmNo">
                        Cancel
                    </slot>
                </v-btn>
                <v-btn
                        ref="confirmButton"
                        color="red darken-4"
                        v-if="!singleButton"
                        @click.stop="confirmYes">
                    <slot name="confirmYes">
                        Ok
                    </slot>
                </v-btn>
            </div>
        </v-card>
    </div>
</template>

<script>
  export default {
    name: "ConfirmationModal",
    data() {
      return {
        cancelButtonIsFocused: true
      }
    },
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
      },
      handleTab(e) {
        const keyCode = e.keyCode;
        if (keyCode === 27) {
          this.confirmNo();
          return;
        }
        if (keyCode !== 9) {
          return;
        }
        e.preventDefault();

        const targetButtonRef = this.cancelButtonIsFocused ? 'cancelButton' : 'confirmButton';
        this.$refs[targetButtonRef].$el.focus();

        this.cancelButtonIsFocused = !this.cancelButtonIsFocused;
      }
    },
    mounted() {
      document.addEventListener('keydown', this.handleTab, true);
    },
    beforeDestroy() {
      document.removeEventListener('keydown', this.handleTab, true);
    }
  }
</script>