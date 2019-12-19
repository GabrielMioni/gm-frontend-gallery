<template>
    <div class="gm-frontend-submit-post">
        <div class="gm-frontend-submit-post-trash">
            <div @click="trashPost">x</div>
        </div>
        <div class="gm-frontend-submit-post-left">
            <div class="gm-frontend-submit-post-upload">
                This is the stone on which I will build my empire.
            </div>
        </div>
        <div class="gm-frontend-submit-post-right">
            <form>
                <div class="gm-frontend-submit-form-group">
                    <label :for="`gm-frontend-submit-title-${index}`">Title</label>
                    <input type="text" name="title"
                           v-model="post.title"
                           :id="`gm-frontend-submit-title-${index}`">
                </div>
                <div class="gm-frontend-submit-form-group">
                    <label :for="`gm-frontend-submit-content-${index}`">Content</label>
                    <textarea
                            v-model="post.content"
                            :id="`gm-frontend-submit-content-${index}`">
                    </textarea>
                </div>
            </form>
        </div>
    </div>
</template>

<script>
  export default {
    name: "SubmitPost",
    props: {
      post: Object,
      index: Number,
    },
    methods: {
      trashPost() {
        this.$emit('trashPost', this.index);
      }
    }
  }
</script>