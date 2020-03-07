<template>
  <div class="gm-frontend-gallery__detail__single">
    This is my Vue elm.
    <gallery-post-detail v-if="false">
    </gallery-post-detail>
  </div>
</template>

<script>
import GalleryPostDetail from '@/gallery/js/components/GalleryPostDetail'
import {mapActions, mapGetters} from 'vuex'
export default {
  name: 'GalleryPostSingle',
  components: { GalleryPostDetail },
  data () {
    return {
      galleryPostId: false
    }
  },
  methods: {
    ...mapActions({
      SET_GALLERY_POSTS: 'galleryData/SET_GALLERY_POSTS'
    }),
    ...mapGetters({
      getGalleryPosts: 'galleryData/getGalleryPosts',
      getGalleryLoading: 'galleryData/getGalleryLoading'
    })
  },
  computed: {
    galleryPost () {
      return this.getGalleryPosts()
    }
  },
  created () {
    const mount = document.getElementById('gm-frontend-gallery-post-single')
    this.galleryPostId = mount.dataset.id
  },
  mounted () {
    this.SET_GALLERY_POSTS({ time: 1000, postId: this.galleryPostId })
  }
}
</script>
