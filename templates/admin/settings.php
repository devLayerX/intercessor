<?php
/**
 * Admin template: plugin settings page.
 *
 * Intentionally empty — rendering is handled entirely by
 * Admin_Loader::render_settings_page(), which calls render() on the shared
 * DisplayPage instance that was initialised (with a live Renderer) during
 * Admin_Loader::register(). Do not instantiate a fresh DisplayPage here.
 *
 * @package Intercessor
 * @since   1.0.0
 */

// This file is intentionally left without executable code.
// Admin_Loader::render_settings_page() calls $this->displayPage->render() directly,
// bypassing this template. The file exists only so any legacy require() of
// templates/admin/settings.php does not produce a fatal "file not found" error.

defined( 'ABSPATH' ) || exit;
