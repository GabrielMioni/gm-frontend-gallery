<template>
    <div id="gm-frontend-submit">
        <template v-for="(post, index) in galleryPosts">
            <submit-post
                    @trashPost="trashPostHandler"
                    :post="post"
                    :index="index">
            </submit-post>
        </template>
        <button @click.stop="addPost">Add A Post!</button>
    </div>
</template>

<script>
  import SubmitPost from "./components/SubmitPost";
  export default {
    name: "gmGallerySubmit",
    components: {SubmitPost},
    data() {
      return {
        galleryPosts: [this.postObjectDefault()],
      }
    },
    methods: {
      postObjectDefault() {
        return {
          title: '',
          content: '',
          image: null
        }
      },
      addPost() {
        this.galleryPosts.push(this.postObjectDefault());
      },
      trashPostHandler(index) {
        this.galleryPosts.splice(index, 1);

        if (this.galleryPosts.length <= 0) {
          this.galleryPosts.push(this.postObjectDefault());
        }
      }
    }
  }
</script>