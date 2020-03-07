<template>
  <div class="gm-frontend-gallery__detail__single">
    This is my Vue elm.
    <gallery-post-detail
      v-if="galleryPosts.length > 0"
      :gallery-post="galleryPosts[0]"
      :index="0">
    </gallery-post-detail>
  </div>
</template>

<script>
import GalleryPostDetail from '@/gallery/js/components/GalleryPostDetail'
import { mapActions, mapGetters } from 'vuex'
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
    galleryPosts () {
      // const blah = this.getGalleryPosts()
      // return blah
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
