<template>
    <div class="gm-gallery-light-box-container"
         v-bind:class="{ active: post !== null }"
         @click="closePost">
        <div class="gm-gallery-light-box">
            <div class="gm-gallery-light-box-main-image">
                <img :src="currentImage" alt="">
            </div>
            <div class="gm-gallery-light-box-content-col-2">
                <div class="gm-gallery-light-box-content-col-2-text">
                    <div class="gm-gallery-light-box-content-col-2-title">
                        {{ post.post_title }}
                    </div>
                    <div class="gm-gallery-light-box-content-col-2-content">
                        {{ post.post_content }}
                    </div>
                </div>
            </div>
            <div class="gm-gallery-light-box-images">
                <template v-for="(image, index) in post.images">
                    <img
                            v-bind:class="{ active: index === activeImage }"
                            @click.stop="selectImage(index)"
                            :src="image['sized_images'].thumbnail" alt="">
                </template>
            </div>
        </div>
        <div class="gm-gallery-light-box-navigation">
            <div class="gm-gallery-light-box-content-navigation-left" @click.stop="galleryNavigate('left')"> < </div>
            <div class="gm-gallery-light-box-content-navigation-right" @click.stop="galleryNavigate('right')"> > </div>
        </div>
    </div>
</template>

<script>
  export default {
    name: 'GalleryLightBox',
    props: {
      post: Object
    },
    data() {
      return {
        currentImage: this.retrieveImage(0, 'full'),
        activeImage: 0,
      }
    },
    methods: {
      closePost() {
        this.$emit('close-post');
      },
      retrieveImage(index, size) {
        return this.post.images[index]['sized_images'][size];
      },
      selectImage(index) {
        this.activeImage = index;
        this.currentImage = this.retrieveImage(index, 'full');
      },
      galleryNavigate(direction) {
        this.$emit('galleryNavigate', direction);
      }
    }
  }
</script>