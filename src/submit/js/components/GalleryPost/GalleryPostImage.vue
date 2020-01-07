<template>
<!--    <v-card class="gm-frontend-gallery-post-image">
        &lt;!&ndash;<div class="gm-frontend-gallery-post-image-upload"
             @keyup.enter="openFileInput"
             @click="openFileInput"
             :tabindex="0"
             :ref="'dropFile'">
            <div v-if="imageUrl === null" class="gm-frontend-gallery-post-image-upload-main">
                <div class="gm-frontend-gallery-post-image-upload-main-icon">
                    <v-icon>image</v-icon>
                </div>
                <div>Click or drag and drop files to upload</div>
                <div>
                    Allowed file types: {{ displayAllowedMimes }}
                </div>
                <v-input
                        :error-messages="imageError">
                </v-input>
            </div>
            <template v-else>
                <v-btn class="gm-frontend-gallery-post-image-upload-trash"
                       tabindex="-1"
                       @click.stop="trashImage"
                       fab dark small color="red darken-4">
                    <v-icon>delete</v-icon>
                </v-btn>
                <v-img
                        :src="imageUrl"
                        contain
                        class="grey darken-4 gm-frontend-gallery-post-image-upload-main">
                </v-img>
            </template>
        </div>&ndash;&gt;
        <v-image-input
                v-model="imageData"
                :image-quality="0.85"
                imageMinScaling="cover"
                clearable
                clearIcon="delete"
                image-format="jpeg"
                :fullHeight="true"
                :fullWidth="true"
        />
        <form class="gm-frontend-gallery-post-image-upload-file" :ref="'fileInputForm'">
            <input type="file" name="image" @change="imageUpdate" :ref="'fileInput'">
        </form>
    </v-card>-->
    <div class="gm-frontend-gallery-post-image">
        <v-image-input
                v-model="imageData"
                class="gm-frontend-gallery-post-image-edit"
                :image-quality="0.85"
                imageMinScaling="cover"
                clearable
                clearIcon="delete"
                image-format="jpeg"
                rotateClockwiseIcon="rotate_right"
                rotateCounterClockwiseIcon="rotate_left"
                uploadIcon="image"
                :fullHeight="false"
                :fullWidth="false"
        />
    </div>
</template>

<script>
  import dragDrop from "drag-drop";
  import { mapGetters, mapActions } from 'vuex';
  import { getOptionsType } from '@/utilities/helpers';
  import { imageUrlValidator } from "@/utilities/helpers";
  import VImageInput from 'vuetify-image-input';

  export default {
    name: "GalleryPostImage",
    components: { VImageInput },
    props: {
      index: {
        type: Number,
        required: true
      },
      imageFile: {
        required: true
      },
      imageUrl: {
        validator: imageUrlValidator,
        required: true
      },
      imageUrlError: {
        type: String,
        required: true,
      }
    },
    methods: {
      ...mapActions({
        SET_POST_IMAGE_DATA: 'postData/SET_POST_IMAGE_DATA',
        SET_POST_ERROR: 'postData/SET_POST_ERROR',
        SET_POST_IMAGE_FILE: 'postData/SET_POST_IMAGE_FILE'
      }),
      ...mapGetters({
        getMainOptions: 'mainData/getMainOptions'
      }),
      imageUpdate(fileData) {
        if (fileData.type === 'change') {
          fileData[0] = fileData.target.files[0];
        }

        const file = fileData[0];
        const fileUrl = URL.createObjectURL(file);
        const mimeIsAllowed = this.allowedMimes.indexOf(file.type) > -1;

        if (mimeIsAllowed) {
          this.SET_POST_IMAGE_DATA({
            index: this.index,
            imageUrl: fileUrl,
            file: file,
          });

          if (this.imageError !== '') {
            this.imageError = '';
          }
        }
        if (!mimeIsAllowed) {
          this.imageError = 'The selected file type is not allowed';
          this.SET_POST_IMAGE_DATA({
            index: this.index,
            imageUrl: null,
            file: null,
          });
        }

        this.clearFileInput();
      },
      clearFileInput() {
        const fileInputForm = this.$refs.fileInputForm;
        fileInputForm.reset();
      },
      openFileInput() {
        const fileInput = this.$refs.fileInput;
        fileInput.click();
      },
      trashImage() {
        setTimeout(()=>{
          this.SET_POST_IMAGE_DATA({
            index: this.index,
            imageUrl: null,
            file: null,
          });
        }, 500);
        this.clearFileInput();
      }
    },
    computed: {
      imageData: {
        get() {
          return this.imageFile;
        },
        set(file) {
          return this.SET_POST_IMAGE_FILE({
            index: this.index,
            file: file
          });
        }
      },
      imageError: {
        get() {
          return this.imageUrlError;
        },
        set(error) {
          return this.SET_POST_ERROR({
            index: this.index,
            type: 'imageUrl',
            error: error,
          });
        }
      },
      allowedMimes: {
        get() {
          return getOptionsType(this.getMainOptions, 'allowedMimes');
        }
      },
      displayAllowedMimes: {
        get() {
          const allowedMimes = getOptionsType(this.getMainOptions, 'allowedMimes');

          const display = allowedMimes.map((mime) => {
            return '.' + mime.substr(mime.indexOf('/') + 1);
          });

          return display.join(', ');
        }
      }
    },
    mounted() {
      /*const dropArea = this.$refs.dropFile;
      const self = this;
      dragDrop(dropArea, (files) => {
        self.imageUpdate(files);
      })*/
    }
  }
</script>