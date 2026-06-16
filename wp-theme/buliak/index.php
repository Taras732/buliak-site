<?php if (!defined('ABSPATH')) exit; get_header(); ?>
<section class="sec wrap" style="padding-top:130px">
  <div class="sec-head"><h2><?php echo is_archive() ? get_the_archive_title() : 'Блог'; ?></h2></div>
  <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
    <article style="margin-bottom:40px;padding-bottom:24px;border-bottom:1px solid var(--line)">
      <h3 style="font-size:1.6rem;margin-bottom:10px"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
      <div style="color:var(--ash)"><?php the_excerpt(); ?></div>
    </article>
  <?php endwhile; else : ?>
    <p style="color:var(--ash)">Поки що порожньо.</p>
  <?php endif; ?>
</section>
<?php get_footer(); ?>
