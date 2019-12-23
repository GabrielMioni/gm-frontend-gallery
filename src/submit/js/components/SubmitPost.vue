<template>
    <div class="gm-frontend-submit-post">
        <div class="gm-frontend-submit-post-trash">
            <div @click="trashPost">x</div>
        </div>
        <div class="gm-frontend-submit-post-left">
            <div class="gm-frontend-submit-post-upload" @click="openFileInput" :ref="'dropFile'">
                <div v-if="uploadImageUrl === null">This is the stone on which I will build my empire.</div>
                <img v-else :src="uploadImageUrl" alt="">
            </div>
        </div>
        <div class="gm-frontend-submit-post-right">
            <form>
                <div class="gm-frontend-submit-form-group">
                    <label :for="setElementId('gm-frontend-submit-content')">Content</label>
                    <textarea v-model="postContent" :id="setElementId('gm-frontend-submit-content')">
                    </textarea>
                </div>
                <input class="gm-frontend-submit-post-file" type="file" name="image" @change="imageUpdate" :ref="'fileInput'">
            </form>
        </div>
    </div>
</template>

<script>
  import dragDrop from "drag-drop";
  export default {
    name: "SubmitPost",
    props: {
      index: Number,
    },
    methods: {
      trashPost() {
        // this.$emit('trashPost', this.index);
        this.$store.commit('removeGalleryPost', this.index);
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

        this.$store.commit('updateImageUpload', {
          index: this.index,
          imageUrl: fileUrl,
          file: file,
        });
      },
      openFileInput() {
        const fileInput = this.$refs.fileInput;
        fileInput.click();
      }
    },
    computed: {
      postContent: {
        get() {
          return this.$store.state.galleryPosts[this.index].content;
        },
        set(value) {
          this.$store.commit('updateGalleryPostContent', {
            index: this.index,
            data: value,
          });
        }
      },
      uploadImageUrl: {
        get() {
          return this.$store.state.galleryPosts[this.index].imageUrl;
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