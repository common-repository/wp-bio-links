<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>

    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <title><?php echo esc_html( $post->post_title ); ?></title>

    <?php wp_head(); ?>

</head>
<body class="wp-bio-links">

    <div class="wp-bio-links-container">

        <?php if ( ( $wp_bio_links_thumbnail = get_option( 'wp_bio_links_thumbnail' ) ) !== false ) : ?>

            <img class="wp-bio-links-thumbnail" src="<?php echo esc_url( $wp_bio_links_thumbnail ); ?>" alt="<?php bloginfo('blogname'); ?>" />

        <?php endif; ?>

        <?php if ( have_posts() ): ?>

            <?php while ( have_posts() ): the_post(); ?>

                <?php $wp_bio_links = get_post_meta( get_the_ID(), 'wp_bio_links', true); ?>


                <?php if ( isset($wp_bio_links['nickname']) ) : ?>

                <div class="wp-bio-links-nickname"><?php echo esc_html( $wp_bio_links['nickname'] ); ?></div>

                <?php endif; ?>


                <div class="wp-bio-links-items">

                <?php if ( isset($wp_bio_links['items']) ) : ?>

                    <?php foreach ( $wp_bio_links['items'] as $item ) : ?>

                    <a class="wp-bio-links-item" href="<?php echo esc_url( $item['url'] ); ?>"><?php echo esc_html( $item['text'] ); ?></a>

                    <?php endforeach; ?>

                <?php endif; ?>

                </div>

            <?php endwhile; ?>

        <?php endif; ?>


        <?php if ( get_option( 'wp_bio_links_credits', true ) ) : ?>

        <a class="wp-bio-links-credits" href="https://www.guglielmopepe.com/#wp-bio-links/?utm_source=<?php echo site_url(); ?>&utm_medium=footer_credits&utm_campaign=wp_bio_links">WP Bio Links</a>

        <?php endif; ?>

    </div>

<?php wp_footer(); ?>
</body>
</html>
