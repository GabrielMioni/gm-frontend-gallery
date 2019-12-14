<template>
    <div
        class="gm-gallery-post"
        @click="openGalleryPost">
        <img :src="post.images[0]['sized_images'].medium" alt="">
        <div class="gm-gallery-post-content">
            <div class="gm-gallery-post-content-grid">
                <div class="gm-post-title">
                    {{ post.post_title }}
                </div>
                <div class="gm-post-content">
                    {{ post.post_content }}
                </div>
            </div>
        </div>
    </div>
</template>

<script>
  export default {
    name: 'GalleryPost',
    props: {
      post: Object,
      index: Number
    },
    methods: {
      openGalleryPost() {
        this.$emit('open-post', this.index);
      }
    }
  }
</script>