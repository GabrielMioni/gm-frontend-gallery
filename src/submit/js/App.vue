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
        galleryPosts: [],
      }
    },
    methods: {
      addPost() {
        this.galleryPosts.push({
          title: '',
          content: '',
          image: null,
        });
      },
      trashPostHandler(index) {
        this.galleryPosts.splice(index, 1);
      }
    }
  }
</script>