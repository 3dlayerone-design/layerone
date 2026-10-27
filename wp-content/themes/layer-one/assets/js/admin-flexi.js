/* Product screen: show only the Flexi Crew fields that match the chosen kind, and pick videos from the media library. */
(function ($) {
  var $kind = $('#lo_kind');
  function toggle() {
    var k = $kind.val();
    $('#layerone-flexi .lo-block').each(function () { $(this).toggle($(this).data('kind') === k); });
  }
  $kind.on('change', toggle);
  toggle();

  $('#layerone-flexi').on('click', '.lo-media-pick', function () {
    var $row = $(this).closest('.lo-media');
    var frame = wp.media({ title: 'Choose a video', library: { type: $row.data('type') || 'video' }, multiple: false });
    frame.on('select', function () {
      var a = frame.state().get('selection').first().toJSON();
      $row.find('input[type=hidden]').val(a.id);
      $row.find('.lo-media-name').text(a.filename || a.title);
      $row.find('.lo-media-clear').prop('hidden', false);
    });
    frame.open();
  });
  $('#layerone-flexi').on('click', '.lo-media-clear', function () {
    var $row = $(this).closest('.lo-media');
    $row.find('input[type=hidden]').val('');
    $row.find('.lo-media-name').text('None');
    $(this).prop('hidden', true);
  });
})(jQuery);
