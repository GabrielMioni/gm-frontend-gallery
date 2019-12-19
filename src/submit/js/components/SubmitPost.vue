<template>
    <div class="gm-frontend-submit-post">
        <div class="gm-frontend-submit-post-trash">
            <div @click="trashPost">x</div>
        </div>
        <div class="gm-frontend-submit-post-left">
            <div class="gm-frontend-submit-post-upload" :ref="'dropFile'">
                This is the stone on which I will build my empire.
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
      }
    },
    mounted() {
      const dropArea = this.$refs.dropFile;
      const self = this;
      dragDrop(dropArea, (files) => {
        console.log(files);
      })
    }
  }
</script>