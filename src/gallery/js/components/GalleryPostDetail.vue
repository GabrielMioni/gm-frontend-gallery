<template>
    <v-card class="gm-frontend-gallery__detail" ref="galleryDetail">
        <div class="gm-frontend-gallery__detail__col-1">
            <div class="gm-frontend-gallery__detail__image-area">
                <v-card class="gm-frontend-gallery__detail__image-area__selected-image">
                    <v-img
                            :src="selectedImage"
                            contain
                            height="100%"
                            width="100%"
                            class="grey darken-4"
                    />
                </v-card>
                <div
                        v-if="galleryPost.images.length > 1"
                        class="gm-frontend-gallery__detail__image-area__attached-images"
                >
                    <gallery-post-detail-attached-image
                            v-for="(image, index) in galleryPost.images"
                            :image="image"
                            :index="index"
                            :key="index"
                            @updateSelectedImageIndex="chooseAttachedImage(index)"
                            :ref="`attachedImage${index}`"
                            v-bind:class="{'gm-frontend-gallery__detail__image-area__attached-images__image--active': selectedImageIndex === index}"
                    />
                </div>
            </div>
        </div>
        <div class="gm-frontend-gallery__detail__col-2">
            <h3>{{galleryPost.post_title}}</h3>
            <div class="gm-frontend-gallery__detail__col-2__content">
                <transition name="fade" mode="out-in">
                    <p :key="selectedImageIndex">
                        {{ galleryPost.images[selectedImageIndex].content }}
                    </p>
                </transition>
            </div>
        </div>
    </v-card>
</template>

<script>
  import GalleryPostDetailAttachedImage from "@/gallery/js/components/GalleryPostDetailAttachedImage";
  import { mapGetters } from 'vuex';
  export default {
    name: "GalleryPostDetail",
    components: {GalleryPostDetailAttachedImage},
    data() {
      return {
        selectedImageIndex: 0,
        focusableElms: [],
        focusedElmIndex: null
      }
    },
    props: {
      galleryPost: {
        type: Object,
        required: true
      },
      index: {
        type: Number,
        required: true
      }
    },
    methods: {
      ...mapGetters({
        getOpenedPostIndex: 'galleryData/getOpenedPostIndex'
      }),
      chooseAttachedImage(index) {
        this.selectedImageIndex = index;
      },
      updateCarouselButtons() {
        let buttons = [];

        const closeButton = document.getElementsByClassName('gm-frontend-gallery__carousel__close-button');
        const prevButton = document.querySelectorAll('.v-window__prev button');
        const nextButton = document.querySelectorAll('.v-window__next button');

        if (closeButton.length > 0) {
          buttons.push(closeButton[0]);
        }
        if (prevButton.length > 0) {
          buttons.push(prevButton[0]);
        }
        if (nextButton.length > 0) {
          buttons.push(nextButton[0]);
        }
        return buttons
      },
      setFocusableElms() {
        const buttons = this.updateCarouselButtons();
        const attachedImages = this.galleryPost.images.map((item, index)=>{
          const attachedImageRef = this.$refs[`attachedImage${index}`];
          if (typeof attachedImageRef === 'undefined') {
            return ;
          }
          return this.$refs[`attachedImage${index}`][0].$el;
        });

        return buttons.concat(attachedImages);
      },
      focusDetail() {
        if (this.currentIndex === this.index) {
          this.$refs['galleryDetail'].$el.focus();
        }
      },
      tabHandler(e) {
        if (e.key !== 'Tab') {
          return;
        }
        e.preventDefault();
        const reverse = e.shiftKey;
        if (this.focusedElmIndex === null) {
          this.focusedElmIndex = 0;
          return;
        }
        const maxFocusableIndex = this.focusableElms.length -1;
        if (!reverse && this.focusedElmIndex +1 <= maxFocusableIndex) {
          this.focusedElmIndex = this.focusedElmIndex +1;
          return
        }
        if (!reverse && this.focusedElmIndex +1 > maxFocusableIndex) {
          this.focusedElmIndex = 0;
          return
        }
        if (reverse && this.focusedElmIndex -1 >= 0) {
          this.focusedElmIndex = this.focusedElmIndex -1;
          return;
        }
        if (reverse && this.focusedElmIndex -1 < 0) {
          this.focusedElmIndex = maxFocusableIndex;
        }
      }
    },
    computed: {
      selectedImage() {
        return this.galleryPost.images[this.selectedImageIndex]['sized_images'].full;
      },
      currentIndex() {
        return this.getOpenedPostIndex();
      }
    },
    mounted() {
      this.$nextTick(()=>{
        this.focusableElms = this.setFocusableElms();
      });
      if (this.currentIndex === this.index) {
        document.addEventListener('keydown', this.tabHandler, true);
      }
    },
    beforeDestroy() {
      document.removeEventListener('keydown', this.tabHandler, true);
    },
    watch: {
      currentIndex() {
        if (this.currentIndex === this.index) {
          document.addEventListener('keydown', this.tabHandler, true);
        }
        if (this.currentIndex !== this.index) {
          document.removeEventListener('keydown', this.tabHandler, true);
          this.focusedElmIndex = null;
        }
      },
      focusedElmIndex() {
        if (this.focusedElmIndex !== null) {
          this.focusableElms[this.focusedElmIndex].focus();
        }
      }
    }
  }
</script>