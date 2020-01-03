<template>
    <div class="gm-frontend-submit-post">
        <div class="gm-frontend-submit-post-trash gm-frontend-submit-post-trash--full">
            <trash-post-button
                    :galleryDataAccessor="getGalleryDataByIndex"
                    :galleryDataDelete="removePost"
            ></trash-post-button>
        </div>
        <div class="gm-frontend-submit-post-left">
            <div class="gm-frontend-submit-post-upload" @click="openFileInput" :ref="'dropFile'">
                <div v-if="uploadImageUrl === null" class="gm-frontend-submit-post-upload-main">
                    This is the stone on which I will build my empire.
                    <div>
                        Allowed file types: {{ displayAllowedMimes }}
                    </div>
                </div>
                <template v-else>
                    <div class="gm-frontend-submit-post-trash">
                        <button @click.stop="trashImage">x</button>
                    </div>
                    <img class="gm-frontend-submit-post-upload-main" :src="uploadImageUrl" alt="">
                </template>
            </div>
            <div class="gm-frontend-submit-error">
                <transition name="fade">
                    <div v-if="imageError !== ''">
                        {{ imageError }}
                    </div>
                </transition>
            </div>
        </div>
        <div class="gm-frontend-submit-post-right">
            <form>
                <div class="gm-frontend-submit-form-group">
                    <label :for="setElementId('gm-frontend-submit-content')">
                        Content
                        <div class="gm-frontend-submit-error">
                            <transition name="fade">
                                <div v-if="contentError !== ''">
                                    {{ contentError }}
                                </div>
                            </transition>
                        </div>
                    </label>
                    <textarea v-model="postContent" :id="setElementId('gm-frontend-submit-content')">
                    </textarea>
                </div>
            </form>
            <form :ref="'fileInputForm'">
                <input class="gm-frontend-submit-post-file" type="file" name="image" @change="imageUpdate" :ref="'fileInput'">
            </form>
        </div>
    </div>
</template>

<script>
  import TrashPostButton from "./TrashPostButton";
  import dragDrop from "drag-drop";
  import { mapGetters, mapActions } from 'vuex';
  export default {
    name: "GalleryPost",
    components: {TrashPostButton},
    props: {
      index: Number,
    },
    data() {
      return {
        galleryDataAccessor: this.getGalleryPostData(),
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
      getGalleryDataByIndex(data) {
        data = data == null ? {} : data;
        let payload = {
          index: this.index,
        };
        if (typeof data.type !== 'undefined') {
          payload.type = data.type;
        }
        if (typeof data.deepKey !== 'undefined') {
          payload.deepKey = data.deepKey;
        }

        return this.galleryDataAccessor(payload);
      },
      setElementId(idName) {
        return `${idName}-${this.index}`;
      },
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
      removePost() {
        this.REMOVE_POST(this.index);
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
      postContent: {
        get() {
          return this.getGalleryDataByIndex({
            type: 'content'
          });
        },
        set(value) {
          const galleryPostContentError = this.getGalleryDataByIndex({
            type: 'errors',
            deepKey: 'content',
          });
          if (galleryPostContentError !== '') {
            this.SET_POST_ERROR({
              index: this.index,
              type: 'content',
              error: '',
            });
          }
          return this.SET_POST_CONTENT({
            index: this.index,
            data: value,
          });
        }
      },
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
      contentError: {
        get() {
          return this.getGalleryDataByIndex({
            type: 'errors',
            deepKey: 'content'
          });
        },
        set(error) {
          return this.SET_POST_ERROR({
            index: this.index,
            type: 'content',
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