<?php
/**
 * Flexi Crew band block, front end. Figures and bundles are read from the products (see inc/flexi/catalog.php).
 *
 * @var array $attributes Block attributes.
 */

if ( ! defined( 'ABSPATH' ) || ! function_exists( 'layerone_flexi_catalog' ) ) {
	return;
}

$catalog = layerone_flexi_catalog();
$page    = $catalog['pageUrl'];
$sizes   = $catalog['box']['sizes'];
$last    = end( $sizes );

$ph = function ( $text ) use ( $catalog, $last ) {
	return strtr( (string) $text, array(
		'{figures}' => count( $catalog['figures'] ),
		'{series}'  => count( $catalog['series'] ),
		'{bundles}' => count( $catalog['groups'] ),
		'{price}'   => $last ? layerone_plain_price( (float) $last['price'] / max( 1, (int) $last['n'] ) ) : '',
		'{n}'       => $last ? (int) $last['n'] : '',
	) );
};

$u1 = $attributes['btn1Url'] ? $attributes['btn1Url'] : $page;
$u2 = $attributes['btn2Url'] ? $attributes['btn2Url'] : $page . '#box';

/* Figure wall: the products picked by hand, else everything with the chosen product tag, else every figure that has a video. */
$by_id  = array();
foreach ( $catalog['figures'] as $f ) {
	$by_id[ $f['id'] ] = $f;
}
$wall = array();
$hand = array_filter( array_map( 'intval', (array) $attributes['wallProducts'] ) );
if ( $hand ) {
	foreach ( $hand as $id ) {
		if ( isset( $by_id[ $id ] ) ) {
			$wall[] = $by_id[ $id ];
		}
	}
} else {
	$tag = sanitize_title( (string) $attributes['wallTag'] );
	foreach ( $catalog['figures'] as $f ) {
		if ( $tag && in_array( $tag, (array) $f['tags'], true ) ) {
			$wall[] = $f;
		}
	}
	if ( ! $wall ) {
		$wall = array_values( array_filter( $catalog['figures'], function ( $f ) {
			return ! empty( $f['mp4'] );
		} ) );
	}
}
$wall = array_values( array_filter( $wall, function ( $f ) {
	return ! empty( $f['thumb'] ) || ! empty( $f['image'] );
} ) );
if ( (int) $attributes['wallLimit'] > 0 ) {
	$wall = array_slice( $wall, 0, (int) $attributes['wallLimit'] );
}
$half  = (int) ceil( count( $wall ) / 2 );
$belts = $wall ? array( array_slice( $wall, 0, $half ), array_slice( $wall, $half ) ) : array();

/* Bundles: the products picked by hand, else every bundle product. */
$bundles = array();
$pick    = array_filter( array_map( 'intval', (array) $attributes['bundleProducts'] ) );
$b_by_id = array();
foreach ( $catalog['bundles'] as $b ) {
	$b_by_id[ $b['id'] ] = $b;
}
if ( $pick ) {
	foreach ( $pick as $id ) {
		if ( isset( $b_by_id[ $id ] ) ) {
			$bundles[] = $b_by_id[ $id ];
		}
	}
} else {
	$bundles = $catalog['bundles'];
}

if ( ! function_exists( 'layerone_flexi_tile' ) ) {
	function layerone_flexi_tile( $f, $href, $hide ) {
		$src = ! empty( $f['thumb'] ) ? $f['thumb'] : $f['image'];
		?>
	<a class="l2-tile" data-ready href="<?php echo esc_url( $href ); ?>" style="--tint:<?php echo esc_attr( $f['tint'] ); ?>"<?php echo $hide ? ' aria-hidden="true" tabindex="-1"' : ''; ?>>
		<img src="<?php echo esc_url( $src ); ?>" alt="" loading="lazy">
		<?php if ( ! empty( $f['mp4'] ) ) : ?>
		<video muted playsinline loop preload="none" poster="<?php echo esc_url( $src ); ?>">
			<?php if ( ! empty( $f['webm'] ) ) : ?><source src="<?php echo esc_url( $f['webm'] ); ?>" type="video/webm"><?php endif; ?>
			<source src="<?php echo esc_url( $f['mp4'] ); ?>" type="video/mp4">
		</video>
		<?php endif; ?>
		<span><?php echo esc_html( $f['name'] ); ?></span>
	</a>
		<?php
	}
}
?>
<section class="l2-fx" id="flexi">
  <div class="wrap l2-fx-in">
    <div class="l2-fx-copy">
      <?php if ( $attributes['kicker'] ) : ?><span class="l2-k"><?php echo esc_html( $attributes['kicker'] ); ?></span><?php endif; ?>
      <h2><?php echo esc_html( $ph( $attributes['heading'] ) ); ?><?php if ( $attributes['headingAccent'] ) : ?> <span class="serif"><?php echo esc_html( $ph( $attributes['headingAccent'] ) ); ?></span><?php endif; ?></h2>
      <?php if ( $attributes['text'] ) : ?><p><?php echo esc_html( $ph( $attributes['text'] ) ); ?></p><?php endif; ?>
      <?php if ( ! empty( $attributes['stats'] ) ) : ?>
      <dl class="l2-stats"><?php foreach ( $attributes['stats'] as $s ) : ?><div><dt><?php echo esc_html( $ph( $s['value'] ) ); ?></dt><dd><?php echo esc_html( $ph( $s['label'] ) ); ?></dd></div><?php endforeach; ?></dl>
      <?php endif; ?>
      <div class="l2-cta">
        <?php if ( $attributes['btn1Label'] ) : ?><a class="btn l2-btn-w" href="<?php echo esc_url( $u1 ); ?>"><?php echo esc_html( $attributes['btn1Label'] ); ?></a><?php endif; ?>
        <?php if ( $attributes['btn2Label'] ) : ?><a class="btn l2-btn-o" href="<?php echo esc_url( $u2 ); ?>"><?php echo esc_html( $attributes['btn2Label'] ); ?></a><?php endif; ?>
      </div>
    </div>
    <?php if ( $belts ) : ?>
    <div class="l2-wall" id="fxWall" aria-label="Flexi figures moving">
      <?php foreach ( $belts as $b => $list ) : ?>
      <div class="l2-row<?php echo $b ? ' r' : ''; ?>"><div class="l2-belt" style="--n:<?php echo (int) count( $list ); ?>">
        <?php
        foreach ( array( false, true ) as $hide ) {
        	foreach ( $list as $f ) {
        		layerone_flexi_tile( $f, $page . '#fig-' . $f['code'], $hide );
        	}
        }
        ?>
      </div></div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>

  <?php if ( $bundles ) : ?>
  <div class="wrap">
    <div class="l2-bh">
      <h3><?php echo esc_html( $attributes['bundlesTitle'] ); ?></h3>
      <?php if ( $attributes['bundlesSub'] ) : ?><span><?php echo esc_html( $ph( $attributes['bundlesSub'] ) ); ?></span><?php endif; ?>
      <div class="l2-arr"><button type="button" class="ar" id="bPrev" aria-label="Previous bundles">←</button><button type="button" class="ar" id="bNext" aria-label="More bundles">→</button></div>
    </div>
    <div class="l2-bundles" id="bundleRow">
      <?php foreach ( $bundles as $b ) : ?>
      <a class="l2-bundle" href="<?php echo esc_url( $b['url'] ); ?>"><?php if ( $b['banner'] ) : ?><img src="<?php echo esc_url( $b['banner'] ); ?>" alt="" loading="lazy"><?php endif; ?>
        <div><b><?php echo esc_html( $b['name'] ); ?></b><span><?php echo esc_html( $b['line'] ); ?></span></div><?php if ( $b['badge'] ) : ?><em><?php echo esc_html( $b['badge'] ); ?></em><?php endif; ?></a>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>
</section>
