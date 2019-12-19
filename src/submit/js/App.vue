<template>
    <div id="gm-frontend-submit">
        <template v-for="(post, index) in galleryPosts">
            <submit-post
                    :post="post"
                    :index="index">
            </submit-post>
        </template>
    </div>
</template>

<script>
  import SubmitPost from "./components/SubmitPost";
  export default {
    name: "gmGallerySubmit",
    components: {SubmitPost},
    data() {
      return {
        galleryPosts: [{},{},{}],
      }
    }
  }
</script>