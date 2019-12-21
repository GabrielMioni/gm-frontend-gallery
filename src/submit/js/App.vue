<template>
    <div id="gm-frontend-submit">
        <template v-for="(post, index) in galleryPosts">
            <submit-post
                    @trashPost="trashPostHandler"
                    @imageUpdate="imageUpdateHandler"
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
        wpNonce: null,
      }
    },
    methods: {
      postObjectDefault() {
        return {
          title: '',
          content: '',
          imageUrl: null,
          imageObj: null,
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
      },
      imageUpdateHandler(data) {
        const currentGalleryPost = this.galleryPosts[data.index];
        currentGalleryPost.imageUrl = data.imageUrl;
      }
    },
    created() {
      const mount = document.getElementById('gm-frontend-submit');
      this.wpNonce = mount.dataset.nonce;
    },
  }
</script>