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
                <div class="gm-frontend-gallery-post-trash">
                    <button @click.stop="trashImage">x</button>
                </div>
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
        this.SET_POST_IMAGE_DATA({
          index: this.index,
          imageUrl: null,
          file: null,
        });
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