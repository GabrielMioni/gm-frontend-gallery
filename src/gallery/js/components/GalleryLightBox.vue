<template>
    <div class="gm-gallery-light-box-container" @click="closePost">
        <div class="gm-gallery-light-box"
             v-bind:class="{ 'show-details': mobileShowDetails === true }"
             @click.stop>
            <div class="gm-gallery-light-box-close">
                <div class="gm-gallery-light-box-close-button" @click.stop="closePost">x</div>
            </div>
            <div class="gm-gallery-light-box-main-image">
                <img v-if="loading === false" :src="currentImage" alt="">
                <div v-else>Loading!</div>
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
                    {{ currentContent }}
                </div>
            </div>
            <div class="gm-gallery-light-box-details-toggle" @click="toggleDetails">
                <div>
                    <span v-if="mobileShowDetails">hide</span>
                    <span v-else>show</span> details
                </div>
            </div>
            <div v-if="loading === false" class="gm-gallery-light-box-images">
                <template v-for="(image, index) in post.images">
                    <img v-bind:class="{ active: index === activeImage }"
                         @click.stop="selectImage(index)"
                         :src="image['sized_images'].full" alt="">
                </template>
            </div>
        </div>
    </div>
</template>

<script>
  export default {
    name: 'GalleryLightBox',
    props: {
      post: Object,
      loading: Boolean,
    },
    data() {
      return {
        activeImage: 0,
        currentImage: this.retrieveImage(0, 'full'),
        currentContent: this.retrieveContent(0),
        mobileShowDetails: false,
      }
    },
    methods: {
      closePost() {
        this.$emit('closePost');
      },
      retrieveImage(index, size) {
        return this.post.images[index]['sized_images'][size];
      },
      retrieveContent(index) {
        return this.post.images[index]['content'];
      },
      selectImage(index) {
        this.activeImage = index;
        this.currentImage = this.retrieveImage(index, 'full');
        this.currentContent = this.retrieveContent(index);
      },
      galleryNavigate(direction) {
        this.activeImage = 0;
        this.$emit('galleryNavigate', direction);
      },
      toggleDetails() {
        this.mobileShowDetails = !this.mobileShowDetails;
      }
    }
  }
</script>