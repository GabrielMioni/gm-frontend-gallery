<template>
    <div class="gm-frontend-submit-post">
        <div class="gm-frontend-submit-post-trash">
            <div @click="trashPost">x</div>
        </div>
        <div class="gm-frontend-submit-post-left">
            <div v-if="post.imageUrl === null" class="gm-frontend-submit-post-upload" :ref="'dropFile'">
                This is the stone on which I will build my empire.
            </div>
            <div v-else>
                <img :src="post.imageUrl" alt="">
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
                <input type="file" name="image" :id="setElementId('gm-frontend-submit-image')">
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
        const file = fileData[0];
        const fileUrl = URL.createObjectURL(file);
        this.$emit('imageUpdate', {
          'index' : this.index,
          'imageUrl' : fileUrl,
          'imageObj' : file,
        });
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