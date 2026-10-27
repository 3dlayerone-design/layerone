/* Customizer repeater for the announcement bar messages. The setting holds a JSON array of {bold, text, link, url}. */
(function (api, $) {
  var FIELDS = [
    ['bold', 'Bold text', 'Free shipping'],
    ['text', 'Text', 'on orders over ₹999'],
    ['link', 'Link text (optional)', 'Shop now'],
    ['url', 'Link URL (optional)', 'https://']
  ];

  api.controlConstructor.layerone_repeater = api.Control.extend({
    ready: function () {
      var control = this, $list = control.container.find('.lo-rep'), rows = [];

      function parse(v) {
        try { var a = JSON.parse(v); return Array.isArray(a) ? a : []; } catch (e) { return []; }
      }
      function save() { control.setting.set(JSON.stringify(rows)); }

      function render() {
        $list.empty();
        rows.forEach(function (row, i) {
          var $card = $('<div class="lo-rep-item"></div>');
          var $head = $('<div class="lo-rep-head"></div>');
          $head.append('<b>Message ' + (i + 1) + '</b>');
          var $tools = $('<span class="lo-rep-tools"></span>');
          $('<button type="button" title="Move up" aria-label="Move up">↑</button>').prop('disabled', i === 0).on('click', function () { move(i, -1); }).appendTo($tools);
          $('<button type="button" title="Move down" aria-label="Move down">↓</button>').prop('disabled', i === rows.length - 1).on('click', function () { move(i, 1); }).appendTo($tools);
          $('<button type="button" class="lo-rep-del" title="Delete" aria-label="Delete">✕</button>').on('click', function () { rows.splice(i, 1); render(); save(); }).appendTo($tools);
          $head.append($tools);
          $card.append($head);

          FIELDS.forEach(function (f) {
            var $in = $('<input type="' + (f[0] === 'url' ? 'url' : 'text') + '">').attr('placeholder', f[2]).val(row[f[0]] || '');
            $in.on('input', function () { rows[i][f[0]] = this.value; save(); });
            $card.append($('<label></label>').append($('<span></span>').text(f[1])).append($in));
          });
          $list.append($card);
        });
      }
      function move(i, d) {
        var j = i + d;
        if (j < 0 || j >= rows.length) return;
        var t = rows[i]; rows[i] = rows[j]; rows[j] = t;
        render(); save();
      }

      rows = parse(control.setting.get());
      render();
      control.container.find('.lo-rep-add').on('click', function () {
        rows.push({ bold: '', text: '', link: '', url: '' });
        render(); save();
        $list.find('.lo-rep-item:last input:first').trigger('focus');
      });
    }
  });
})(wp.customize, jQuery);
