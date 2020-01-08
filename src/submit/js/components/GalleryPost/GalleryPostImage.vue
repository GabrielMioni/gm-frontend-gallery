<template>
    <v-card class="gm-frontend-gallery-post-image">
        <div class="gm-frontend-gallery-post-image-upload"
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
                <v-btn class="gm-frontend-gallery-post-image-upload-rotate"
                       fab dark small color="blue darken-4"
                       @click.stop="rotateImageLeft">
                    <v-icon>rotate_left</v-icon>
                </v-btn>
                <v-btn class="gm-frontend-gallery-post-image-upload-rotate"
                       fab dark small color="blue darken-4"
                       @click.stop="rotateImageRight">
                    <v-icon>rotate_right</v-icon>
                </v-btn>
                <v-img
                        :src="imageUrl"
                        contain
                        class="grey darken-4 gm-frontend-gallery-post-image-upload-main">
                </v-img>
            </template>
        </div>
        <form class="gm-frontend-gallery-post-image-upload-file" :ref="'fileInputForm'">
            <input type="file" name="image" @change="imageUpdate" :ref="'fileInput'">
        </form>
    </v-card>
</template>

<script>
  import dragDrop from "drag-drop";
  import { mapGetters, mapActions } from 'vuex';
  import { getOptionsType } from '@/utilities/helpers';
  import { imageUrlValidator } from "@/utilities/helpers";
  import * as loadImage from 'blueimp-load-image';
  require('blueimp-canvas-to-blob');

  export default {
    name: "GalleryPostImage",
    props: {
      index: {
        type: Number,
        required: true
      },
      imageUrl: {
        validator: imageUrlValidator,
        required: true
      },
      imageFile: {
        require: true,
      },
      imageUrlError: {
        type: String,
        required: true,
      }
    },
    methods: {
      ...mapActions({
        SET_POST_IMAGE_DATA: 'postData/SET_POST_IMAGE_DATA',
        SET_POST_ERROR: 'postData/SET_POST_ERROR'
      }),
      ...mapGetters({
        getMainOptions: 'mainData/getMainOptions'
      }),
      imageUpdate(fileData) {
        if (fileData.type === 'change') {
          fileData[0] = fileData.target.files[0];
        }

        const file = fileData[0];
        const mimeType = file.type;
        const mimeIsAllowed = this.allowedMimes.indexOf(mimeType) > -1;

        if (mimeIsAllowed && mimeType === 'image/gif') {
          this.SET_POST_IMAGE_DATA({
            index: this.index,
            imageUrl: URL.createObjectURL(file),
            file: file,
          });
        }
        if (mimeIsAllowed && mimeType !== 'image/gif') {
          this.processImage(file, true);
        }
        if (mimeIsAllowed && this.imageError !== '') {
          this.imageError = '';
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
      processImage(imageFile, orientationValue = true) {
        const self = this;
        const mimeType = imageFile.type;
        const fileName = imageFile.name;

        loadImage(imageFile,
          (canvas) => {
            canvas.toBlob((blob) => {
              const fileBlob = URL.createObjectURL(blob);
              const file = new File([blob], fileName, {
                lastModified: Math.round((new Date()).getTime() / 1000),
                type: mimeType
              });
              self.SET_POST_IMAGE_DATA({
                index: self.index,
                imageUrl: fileBlob,
                file: file,
              });
            }, mimeType);
          },
          {
            orientation: orientationValue,
            canvas: true
          });
      },
      rotateImageLeft() {
        this.processImage(this.imageFile, 8);
      },
      rotateImageRight() {
        this.processImage(this.imageFile, 6);
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
      const dropArea = this.$refs.dropFile;
      const self = this;
      dragDrop(dropArea, (files) => {
        self.imageUpdate(files);
      })
    }
  }
</script>