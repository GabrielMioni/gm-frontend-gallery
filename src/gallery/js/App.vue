<template>
  <v-app
    :dark="false"
    id="gm-frontend-gallery">
    <v-container fluid>
      <div class="gm-frontend-gallery">
        <v-fade-transition
          class="gm-frontend-gallery__main"
          group>
          <gallery-post-image
            v-for="(galleryPost, index) in galleryPosts"
            :gallery-post="galleryPost"
            :index="index"
            :key="index">
          </gallery-post-image>
        </v-fade-transition>
      </div>
      <div class="gm-frontend-gallery__footer">
        <v-btn
          v-if="galleryPosts.length < getGalleryCount()"
          color="primary"
          :loading="getGalleryLoading()"
          @click="SET_GALLERY_POSTS(1000)"
        >
          Load More
        </v-btn>
      </div>
      <gallery-carousel
        v-if="carouselIsOpen"
      >
      </gallery-carousel>
    </v-container>
  </v-app>
</template>

<script>
import GalleryPostImage from './components/galleryPostImage'
import GalleryCarousel from './components/GalleryCarousel'
import { mapGetters, mapActions } from 'vuex'

export default {
  name: 'gmGallery',
  components: { GalleryCarousel, GalleryPostImage },
  data () {
    return {
      showCarousel: true
    }
  },
  methods: {
    ...mapGetters({
      getGalleryCount: 'galleryData/getGalleryCount',
      getGalleryPosts: 'galleryData/getGalleryPosts',
      getOpenedPostIndex: 'galleryData/getOpenedPostIndex',
      getGalleryLoading: 'galleryData/getGalleryLoading'
    }),
    ...mapActions({
      SET_GALLERY_POSTS: 'galleryData/SET_GALLERY_POSTS',
      SET_OPENED_POST_INDEX: 'galleryData/SET_OPENED_POST_INDEX'
    }),
    close () {
      this.showCarousel = false
    }
  },
  computed: {
    galleryPosts () {
      const galleryPosts = this.getGalleryPosts()
      return galleryPosts.filter((post) => {
        return post.images.length > 0
      })
    },
    openedPostIndex: {
      get () {
        return this.getOpenedPostIndex()
      },
      set (index) {
        return this.SET_OPENED_POST_INDEX(index)
      }
    },
    carouselIsOpen () {
      return this.getOpenedPostIndex() !== null
    }
  },
  mounted () {
    this.SET_GALLERY_POSTS(1000)
  }
}
</script>
