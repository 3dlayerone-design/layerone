<?php
/**
 * Contact block, front end.
 *
 * @var array $attributes Block attributes.
 */

if ( ! defined( 'ABSPATH' ) ) {
	return;
}

$icons = array(
	'mail'  => '<rect x="3" y="5" width="18" height="14" rx="3"/><path d="m4 7 8 6 8-6"/>',
	'chat'  => '<path d="M4 20l1.3-4A8 8 0 1 1 8 18.7z"/><path d="M9 9c0 3 3 6 6 6l1-2-2-1-1 .8c-.8-.4-1.6-1.2-2-2l.8-1-1-2z"/>',
	'clock' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
	'pin'   => '<path d="M12 21s7-6.2 7-11.5A7 7 0 0 0 5 9.5C5 14.8 12 21 12 21z"/><circle cx="12" cy="9.5" r="2.5"/>',
	'help'  => '<circle cx="12" cy="12" r="9"/><path d="M12 11v5M12 8h.01"/>',
);

$topics = array_filter( array_map( 'trim', explode( "\n", (string) $attributes['topics'] ) ) );
$email  = sanitize_email( $attributes['email'] );
?>
<main class="wrap pg">
  <nav class="crumbs" aria-label="Breadcrumb"><a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a><span>/</span><b><?php echo esc_html( get_the_title() ); ?></b></nav>
  <div class="pg-head"><h1><?php echo esc_html( $attributes['heading'] ); ?></h1><?php if ( $attributes['intro'] ) : ?><p><?php echo esc_html( $attributes['intro'] ); ?></p><?php endif; ?></div>

  <div class="ct">
    <div class="ct-cards">
      <?php
      foreach ( (array) $attributes['cards'] as $card ) :
      	if ( empty( $card['title'] ) ) {
      		continue;
      	}
      	$icon = isset( $icons[ $card['icon'] ?? '' ] ) ? $icons[ $card['icon'] ] : $icons['help'];
      	$url  = ! empty( $card['url'] ) ? ( 0 === strpos( $card['url'], '/' ) ? home_url( $card['url'] ) : $card['url'] ) : '';
      	$ext  = 0 === strpos( $url, 'http' ) && 0 !== strpos( $url, home_url() );
      	$val  = isset( $card['value'] ) ? $card['value'] : '';
      	$body = '<div class="ic"><svg class="i" viewBox="0 0 24 24">' . $icon . '</svg></div><div><h3>' . esc_html( $card['title'] ) . '</h3>' . ( ! empty( $card['text'] ) ? '<p>' . esc_html( $card['text'] ) . '</p>' : '' ) . ( '' !== $val ? '<b>' . esc_html( $val ) . '</b>' : '' ) . '</div>';
      	if ( $url ) {
      		echo '<a class="cc" href="' . esc_url( $url ) . '"' . ( $ext ? ' target="_blank" rel="noopener"' : '' ) . '>' . $body . '</a>'; // phpcs:ignore WordPress.Security.EscapeOutput
      	} else {
      		echo '<div class="cc">' . $body . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput
      	}
      endforeach;
      ?>
    </div>

    <section class="card ct-form" aria-labelledby="ctH">
      <h2 id="ctH"><?php echo esc_html( $attributes['formTitle'] ); ?></h2>
      <?php if ( $attributes['formText'] ) : ?><p><?php echo esc_html( $attributes['formText'] ); ?></p><?php endif; ?>
      <form id="ctForm" data-to="<?php echo esc_attr( $email ); ?>" novalidate>
        <div class="row2">
          <div class="fld"><label for="cName">Your name</label><input id="cName" name="name" autocomplete="name" maxlength="80" required></div>
          <div class="fld"><label for="cEmail">Email</label><input id="cEmail" name="email" type="email" autocomplete="email" maxlength="100" required></div>
        </div>
        <div class="row2">
          <div class="fld"><label for="cPhone">Mobile <i>(optional)</i></label><input id="cPhone" name="phone" type="tel" inputmode="numeric" autocomplete="tel-national" pattern="[6-9][0-9]{9}" maxlength="10" title="Enter a 10-digit Indian mobile number"></div>
          <div class="fld"><label for="cOrder">Order number <i>(optional)</i></label><input id="cOrder" name="order" maxlength="30" placeholder="e.g. 1042"></div>
        </div>
        <?php if ( $topics ) : ?>
        <div class="fld"><label for="cTopic">What is it about?</label>
          <select id="cTopic" name="topic" required><?php foreach ( $topics as $t ) : ?><option><?php echo esc_html( $t ); ?></option><?php endforeach; ?></select></div>
        <?php else : ?>
        <input type="hidden" id="cTopic" name="topic" value="Message">
        <?php endif; ?>
        <div class="fld"><label for="cMsg">Message</label><textarea id="cMsg" name="message" rows="6" placeholder="How can we help?" required minlength="10" maxlength="1500"></textarea></div>
        <button class="btn btn-dark" type="submit">Send message</button>
        <?php if ( $email ) : ?><p class="ct-hint">Prefer to write yourself? Email <button type="button" class="lk" id="copyMail">copy our address</button> and we will reply there.</p><?php endif; ?>
      </form>
    </section>
  </div>
</main>
