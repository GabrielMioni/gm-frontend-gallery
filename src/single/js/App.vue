<template>
  <div class="gm-frontend-gallery__detail__single">
    This is my Vue elm.
    <gallery-post-detail v-if="false">
    </gallery-post-detail>
  </div>
</template>

<script>
import GalleryPostDetail from '@/gallery/js/components/GalleryPostDetail'
export default {
  name: 'GalleryPostSingle',
  components: { GalleryPostDetail },
  data () {
    return {
      galleryPostId: false,
      galleryPost: {}
    }
  },
  methods: {
    getSingleGalleryPost () {
      // Go get that Gallery Post.
    }
  },
  watch: {
    galleryPostId (value) {
      if (value === false) {
        return
      }
      this.getSingleGalleryPost()
    }
  },
  created () {
    const mount = document.getElementById('gm-frontend-gallery-post-single')
    this.galleryPostId = mount.dataset.id
  }
}
</script>
