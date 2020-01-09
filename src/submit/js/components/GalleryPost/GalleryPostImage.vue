<template>
    <v-card class="gm-frontend-gallery-post-image">
        <div class="gm-frontend-gallery-post-image-upload"
             @keyup.enter="openFileInput"
             @click="openFileInput"
             :ref="'dropFile'">
            <div v-if="imageUrl === null" class="gm-frontend-gallery-post-image-upload-main">
                <v-btn class="gm-frontend-gallery-post-image-upload-main__button-big"
                        absolute
                        color="transparent"
                        height="100%"
                        width="100%"
                ></v-btn>
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
                <v-img
                        :src="imageUrl"
                        contain
                        class="grey darken-4 gm-frontend-gallery-post-image-upload-main">
                </v-img>
                <div class="gm-frontend-gallery-post-image-upload-controls">
                    <template v-if="canBeRotated">
                        <span class="gm-frontend-gallery-post-image-upload-controls__button-container">
                            <v-btn
                                    class="gm-frontend-gallery-post-image-upload-controls__button"
                                    fab dark small color="blue darken-4"
                                    @keyup.enter.stop
                                    @click.stop="orientImage('left')">
                                <v-icon>rotate_left</v-icon>
                            </v-btn>
                        </span>
                        <span class="gm-frontend-gallery-post-image-upload-controls__button-container">
                            <v-btn
                                    class="gm-frontend-gallery-post-image-upload-controls__button"
                                    fab dark small color="blue darken-4"
                                    @keyup.enter.stop
                                    @click.stop="orientImage('right')">
                                <v-icon>rotate_right</v-icon>
                            </v-btn>
                        </span>
                        <span class="gm-frontend-gallery-post-image-upload-controls__button-container">
                            <v-btn
                                    class="gm-frontend-gallery-post-image-upload-controls__button"
                                    fab dark small color="blue darken-4"
                                    @keyup.enter.stop
                                    @click.stop="orientImage('vertical')">
                                <v-icon>flip</v-icon>
                            </v-btn>
                        </span>
                        <span class="gm-frontend-gallery-post-image-upload-controls__button-container">
                            <v-btn
                                    class="gm-frontend-gallery-post-image-upload-controls__button"
                                    style="transform: rotate(90deg)"
                                    fab dark small color="blue darken-4"
                                    @keyup.enter.stop
                                    @click.stop="orientImage('horizontal')">
                                <v-icon>flip</v-icon>
                            </v-btn>
                        </span>
                    </template>
                    <span class="gm-frontend-gallery-post-image-upload-controls__trash-container">
                        <v-btn
                                class="gm-frontend-gallery-post-image-upload-controls__button"
                                @keyup.enter.stop
                                @click.stop="trashImage"
                                fab dark small color="red darken-4">
                            <v-icon>delete</v-icon>
                        </v-btn>
                    </span>
                </div>
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
    data() {
      return {
        canBeRotated: false
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
          this.canBeRotated = false;
          this.SET_POST_IMAGE_DATA({
            index: this.index,
            imageUrl: URL.createObjectURL(file),
            file: file,
          });
        }
        if (mimeIsAllowed && mimeType !== 'image/gif') {
          this.canBeRotated = true;
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
        const imageData = {
          lastModified: Math.round((new Date()).getTime() / 1000),
          type: imageFile.type
        };

        loadImage(imageFile,
          (canvas) => {
            canvas.toBlob((blob) => {
              const imageUrl = URL.createObjectURL(blob);
              const file = new File([blob], imageFile.name, imageData);
              self.SET_POST_IMAGE_DATA({
                index: self.index,
                imageUrl: imageUrl,
                file: file,
              });
            }, imageFile.type);
          },
          {
            orientation: orientationValue,
            canvas: true
          });
      },
      orientImage(direction) {
        const directions = {
          left: 8,
          right: 6,
          vertical: 2,
          horizontal: 4
        };

        const directionValue = typeof directions[direction] !== 'undefined' ? directions[direction] : true;
        this.processImage(this.imageFile, directionValue);
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
        this.SET_POST_IMAGE_DATA({
          index: this.index,
          imageUrl: '',
          file: '',
        }).then(()=>{
          setTimeout(()=>{
            this.SET_POST_IMAGE_DATA({
              index: this.index,
              imageUrl: null,
              file: null,
            });
          }, 500);
          this.clearFileInput();
        });
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