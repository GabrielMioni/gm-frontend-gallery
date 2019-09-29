<?php
/**
 * Class PluginTest
 *
 * @package Gm_Frontend_Gallery
 */

/**
 * Sample test case.
 */
class PluginTest extends WP_UnitTestCase {

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
