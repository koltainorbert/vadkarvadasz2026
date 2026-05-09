<?php
/**
 * page.php – Normál WordPress oldal template
 * (regisztráció, bejelentkezés, dashboard, hirdetés feladás stb.)
 */
get_header(); ?>

<?php if (have_posts()): while (have_posts()): the_post(); ?>
    <?php
    $has_pb_blocks = ! empty( get_post_meta( get_the_ID(), 'va_page_blocks', true ) );
    $wrap_class    = 'va-wrap' . ( $has_pb_blocks ? ' va-wrap--pb' : '' );
    ?>
<div class="<?php echo esc_attr( $wrap_class ); ?>">
        <?php
        $content = trim( (string) get_the_content() );
        $has_h1  = stripos( $content, '<h1' ) !== false;

        if ( ! $has_h1 ) {
            echo '<h1 class="screen-reader-text">' . esc_html( get_the_title() ) . '</h1>';
        }

        // Fail-safe: ha a kereső oldal tartalma üres, akkor is jelenjen meg a kereső modul.
        if ( is_page( 'va-hirdetes-kereses' ) && $content === '' ) {
            echo do_shortcode( '[va_listing_search]' );
        } else {
            the_content();
        }
        ?>
<?php endwhile; endif; ?>
</div>

<?php get_footer(); ?>
