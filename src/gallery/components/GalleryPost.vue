<template>
    <div class="gallery-post">
        <img :src="post.images[0]['sized_images'].medium" alt="">
    </div>
</template>

<script>
  export default {
    name: 'GalleryPost',
    props: {
      post: Object
    }
  }
</script>