<template>
    <div class="gm-frontend-submit-post">
        <div class="gm-frontend-submit-post-trash">
            <div @click="REMOVE_POST">x</div>
        </div>
        <div class="gm-frontend-submit-post-left">
            <div class="gm-frontend-submit-post-upload" @click="openFileInput" :ref="'dropFile'">
                <div v-if="uploadImageUrl === null">This is the stone on which I will build my empire.</div>
                <img v-else :src="uploadImageUrl" alt="">
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
  import dragDrop from "drag-drop";
  import { mapGetters, mapActions } from 'vuex';
  export default {
    name: "SubmitPost",
    props: {
      index: Number,
    },
    data() {
      return {
        galleryDataAccessor: this.getGalleryPostData(),
      }
    },
    methods: {
      ...mapActions([
        'REMOVE_POST',
        'SET_POST_CONTENT',
        'SET_POST_IMAGE_DATA',
        'SET_POST_ERROR'
      ]),
      ...mapGetters([
        'getGalleryPostData'
      ]),
      setElementId(idName) {
        return `${idName}-${this.index}`;
      },
      imageUpdate(fileData) {
        if (fileData.type === 'change') {
          fileData[0] = fileData.target.files[0];
        }

        const file = fileData[0];
        const fileUrl = URL.createObjectURL(file);

        this.SET_POST_IMAGE_DATA({
          index: this.index,
          imageUrl: fileUrl,
          file: file,
        });

        if (this.imageError !== '') {
          this.imageError = '';
        }

        const fileInputForm = this.$refs.fileInputForm;
        fileInputForm.reset();
      },
      openFileInput() {
        const fileInput = this.$refs.fileInput;
        fileInput.click();
      }
    },
    computed: {
      postContent: {
        get() {
          return this.galleryDataAccessor({
            index: this.index,
            type: 'content'
          });
        },
        set(value) {
          const galleryPostContentError = this.galleryDataAccessor({
            index: this.index,
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
          return this.galleryDataAccessor({
            index: this.index,
            type: 'imageUrl'
          });
        }
      },
      imageError: {
        get() {
          return this.galleryDataAccessor({
            index: this.index,
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
          return this.galleryDataAccessor({
            index: this.index,
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