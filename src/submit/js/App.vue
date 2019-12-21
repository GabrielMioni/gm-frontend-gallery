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
        <button @click.stop="submitPost">Submit</button>
    </div>
</template>

<script>
  import SubmitPost from "./components/SubmitPost";
  import axios from "axios";
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
          file: null,
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
        currentGalleryPost.file = data.file;
        currentGalleryPost.imageUrl = data.imageUrl;
      },
      submitPost() {
        let attachmentContents = [];
        let formData = new FormData();

        this.galleryPosts.map((galleryPost)=>{
          attachmentContents.push(galleryPost.content);
          formData.append('image_files[]', galleryPost.file);
        });

        formData.append('post_nonce', this.wpNonce);
        formData.append('contents', JSON.stringify(attachmentContents));

        axios.post('/wp-json/gm-frontend-gallery/v1/submit/', formData, { headers: { 'Content-Type': 'multipart/form-data' } })
          .then((response)=>{

          }).catch((error)=>{

        });
      }
    },
    created() {
      const mount = document.getElementById('gm-frontend-submit');
      this.wpNonce = mount.dataset.nonce;
    },
  }
</script>