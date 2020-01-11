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
    },
    mounted() {
      this.$refs['cancelButton'].$el.focus();
    }
  }
</script>