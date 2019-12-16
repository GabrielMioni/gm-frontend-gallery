<template>
    <div class="gm-gallery-light-box-container"
         v-bind:class="{ active: post !== null }"
         @click="closePost">
        <div class="gm-gallery-light-box"
             v-bind:class="{ 'show-details': mobileShowDetails === true }"
             @click.stop>
            <div class="gm-gallery-light-box-main-image">
                <img :src="currentImage" alt="">
                <div class="gm-gallery-light-box-navigation">
                    <div class="gm-gallery-light-box-navigation-left" @click.stop="galleryNavigate('left')"> < </div>
                    <div class="gm-gallery-light-box-navigation-right" @click.stop="galleryNavigate('right')"> > </div>
                </div>
            </div>
            <div class="gm-gallery-light-box-text">
                <div class="gm-gallery-light-box-content-text-title">
                    {{ post.post_title }}
                </div>
                <div class="gm-gallery-light-box-content-text-content">
                    {{ post.post_content }}
                </div>
            </div>
            <div class="gm-gallery-light-box-display-details"
                @click="toggleDetails">
                Details
            </div>
            <div class="gm-gallery-light-box-images">
                <template v-for="(image, index) in post.images">
                    <img v-bind:class="{ active: index === activeImage }"
                         @click.stop="selectImage(index)"
                         :src="image['sized_images'].thumbnail" alt="">
                </template>
            </div>
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
        mobileShowDetails: false,
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
      },
      toggleDetails() {
        this.mobileShowDetails = !this.mobileShowDetails;
      }
    }
  }
</script>