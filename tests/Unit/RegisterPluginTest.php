<?php
require_once(__DIR__ . '/../../gm-frontend-gallery.php');
require_once(__DIR__ . '/../../definitionsTrait.php');

class RegisterPluginTest extends WP_UnitTestCase
{
    use definitionsTrait;

    public function setUp()
    {
        parent::setUp();
        $gmFrontendGallery = new gmFrontendGallery();
        $gmFrontendGallery->createPostType();
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    /** @test */
    public function plugin_can_create_gallery_post_type()
    {
        $postTypeExists = post_type_exists($this->postType);
        $this->assertTrue($postTypeExists);
    }

    /** @test */
    public function plugin_can_be_activated()
    {
        $activationRan = $this->activatePlugins('gm-frontend-gallery/gm-frontend-gallery.php');
        $this->assertTrue($activationRan);

        $activePlugins = get_option('active_plugins');
        $this->assertContains('gm-frontend-gallery/gm-frontend-gallery.php', $activePlugins);
    }
    
    protected function activatePlugins($plugin )
    {
        $activePlugins = get_option( 'active_plugins' );
        $plugin = plugin_basename( trim( $plugin ) );

        if ( !in_array( $plugin, $activePlugins ) ) {
            $activePlugins[] = $plugin;
            sort( $activePlugins );
            do_action( 'activate_plugin', trim( $plugin ) );
            update_option( 'active_plugins', $activePlugins );
            do_action( 'activate_' . trim( $plugin ) );
            do_action( 'activated_plugin', trim( $plugin) );

            return true;
        }

        return false;
    }
}
