<template>
    <div class="gm-frontend-submit-post">
        <div class="gm-frontend-submit-post-trash">
            <div @click="trashPost">x</div>
        </div>
        <div class="gm-frontend-submit-post-left">
            <div class="gm-frontend-submit-post-upload"
                 @click="openFileInput"
                 :ref="'dropFile'">
                <div v-if="post.imageUrl === null">This is the stone on which I will build my empire.</div>
                <img v-else :src="post.imageUrl" alt="">
                <!--<div v-else>
                    <img :src="post.imageUrl" alt="">
                </div>-->
            </div>
        </div>
        <div class="gm-frontend-submit-post-right">
            <form>
                <div class="gm-frontend-submit-form-group">
                    <label :for="setElementId('gm-frontend-submit-title')">Title</label>
                    <input type="text" name="title"
                           v-model="post.title"
                           :id="setElementId('gm-frontend-submit-title')">
                </div>
                <div class="gm-frontend-submit-form-group">
                    <label :for="setElementId('gm-frontend-submit-content')">Content</label>
                    <textarea
                            v-model="post.content"
                            :id="setElementId('gm-frontend-submit-content')">
                    </textarea>
                </div>
                <input class="gm-frontend-submit-post-file" type="file" name="image"
                       @change="imageUpdate"
                       :ref="'fileInput'">
            </form>
        </div>
    </div>
</template>

<script>
  import dragDrop from "drag-drop";
  export default {
    name: "SubmitPost",
    props: {
      post: Object,
      index: Number,
    },
    methods: {
      trashPost() {
        this.$emit('trashPost', this.index);
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
        this.$emit('imageUpdate', {
          'index' : this.index,
          'imageUrl' : fileUrl,
          'imageObj' : file,
        });
      },
      openFileInput() {
        const fileInput = this.$refs.fileInput;
        fileInput.click();
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