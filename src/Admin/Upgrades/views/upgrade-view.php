<?php

?>
<div class="updated">
    <p>
        <?php
        printf(
            __( 'Intercessor needs to upgrade the database. %sLearn more about this upgrade%s.', 'intercessor' ),
            '<a href="#" onClick="jQuery(this).parent().next(\'div\').slideToggle()">',
            '</a>'
        );
        ?>
    </p>
    <div style="display: none;">
        <h3>
            <?php esc_html_e( 'About this upgrade:', 'intercessor' ); ?>
        </h3>
        <p>
            <?php
            printf(
            /* translators: 1. Opening strong/italics tag; do not translate. 2. Closing strong/italics tag; do not translate. */
                esc_html__( 'This is a %1$smandatory%2$s update that will migrate all Intercessor prayer count from the prayer meta to a new database. This upgrade will provide better performance and scalability.', 'intercessor' ),
                '<strong><em>',
                '</em></strong>'
            );
            ?>
        </p>
        <p>
            <?php
            printf(
            /* translators: 1. Opening strong tag; do not translate. 2. Closing strong tag; do not translate. */
                esc_html__( '%1$sPlease back up your database before starting this upgrade.%2$s This upgrade routine will make irreversible changes to the database.', 'intercessor' ),
                '<strong>',
                '</strong>'
            );
            ?>
        </p>
        <p>
            <?php
            printf(
            /* translators: 1. Opening strong tag; do not translate. 2. Closing strong tag; do not translate. 3. Line break; do not translate. 4. CLI command example; do not translate. */
                esc_html__( '%1$sAdvanced User?%2$s This upgrade can also be run via WP-CLI with the following command:%3$s%3$s%4$s', 'intercessor' ),
                '<strong>',
                '</strong>',
                '<br />',
                '<code>wp intercessor v110_upgrade</code>'
            );
            ?>
        </p>
        <p>
            <?php
            esc_html_e( 'For large sites, this is the recommended method of upgrading.', 'intercessor' );
            ?>
        </p>
    </div>
    <?php
    $url = add_query_arg(
        [
            'page'                => 'intercessor-upgrades',
            'intercessor-upgrade' => 'v110_upgrade',
        ],
        admin_url()
    );
    ?>
    <p>
        <a class="button button-secondary" href="<?php echo esc_url( $url ); ?>"><?php esc_html_e( 'Begin the upgrade', 'intercessor' ); ?></a>
    </p>
</div>
