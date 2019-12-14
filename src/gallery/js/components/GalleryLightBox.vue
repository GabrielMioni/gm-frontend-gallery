<template>
    <div class="gm-gallery-light-box" v-bind:class="{ active: post !== null }">
        <div class="gm-gallery-light-box-content">
            <div class="gm-gallery-light-box-content-col-1">
                <img :src="post.images[0]['sized_images'].full" alt="">
            </div>
            <div class="gm-gallery-light-box-content-col-2">

            </div>
        </div>
    </div>
</template>

<script>
  export default {
    name: 'GalleryLightBox',
    props: {
      post: Object
    }
  }
</script>