<template>
    <div class="gm-frontend-gallery-post-image">
        <div class="gm-frontend-gallery-post-image-upload" @click="openFileInput" :ref="'dropFile'">
            <div v-if="uploadImageUrl === null" class="gm-frontend-gallery-post-image-upload-main">
                This is the stone on which I will build my empire.
                <div>
                    Allowed file types: {{ displayAllowedMimes }}
                </div>
            </div>
            <template v-else>
                <div class="gm-frontend-gallery-post-trash">
                    <button @click.stop="trashImage">x</button>
                </div>
                <img class="gm-frontend-gallery-post-image-upload-main" :src="uploadImageUrl" alt="">
            </template>
        </div>
        <div class="gm-frontend-submit-error">
            <transition name="fade">
                <div v-if="imageError !== ''">
                    {{ imageError }}
                </div>
            </transition>
        </div>
        <form class="gm-frontend-gallery-post-image-upload-file" :ref="'fileInputForm'">
            <input type="file" name="image" @change="imageUpdate" :ref="'fileInput'">
        </form>
    </div>
</template>

<script>
  import dragDrop from "drag-drop";
  import { mapGetters, mapActions } from 'vuex';

  export default {
    name: "GalleryPostImage",
    props: {
      index: {
        type: Number,
        required: true
      },
      getGalleryDataByIndex: {
        type: Function,
        required: true
      }
    },
    methods: {
      ...mapActions({
        SET_POST_CONTENT: 'postData/SET_POST_CONTENT',
        SET_POST_IMAGE_DATA: 'postData/SET_POST_IMAGE_DATA',
        SET_POST_ERROR: 'postData/SET_POST_ERROR',
        REMOVE_POST: 'postData/REMOVE_POST',
      }),
      ...mapGetters({
        getGalleryPostData: 'postData/getGalleryPostData',
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
      uploadImageUrl: {
        get() {
          return this.getGalleryDataByIndex({
            type: 'imageUrl'
          });
        }
      },
      imageError: {
        get() {
          return this.getGalleryDataByIndex({
            type: 'errors',
            deepKey: 'imageUrl'
          });
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
          const options = this.getMainOptions();
          return options.allowedMimes;
        }
      },
      displayAllowedMimes: {
        get() {
          const options = this.getMainOptions();
          const allowedMimes = options.allowedMimes;

          let display = [];

          allowedMimes.map((mime) => {
            const fileType = mime.substr(mime.indexOf('/') + 1);
            display.push('.' + fileType);
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