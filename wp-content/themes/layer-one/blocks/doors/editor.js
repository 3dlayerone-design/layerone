/* Three doors: editor UI (plain JS, no build step). */
(function (wp) {
  var el = wp.element.createElement;
  var registerBlockType = wp.blocks.registerBlockType;
  var useBlockProps = wp.blockEditor.useBlockProps;
  var MediaUpload = wp.blockEditor.MediaUpload;
  var MediaUploadCheck = wp.blockEditor.MediaUploadCheck;
  var c = wp.components;
  var useSelect = wp.data.useSelect;

  function patch(obj, key, val) { var o = Object.assign({}, obj); o[key] = val; return o; }

  function DoorCard(props) {
    var d = props.door, i = props.index;
    var media = useSelect(function (select) {
      return (d.images || []).map(function (id) { return select('core').getMedia(id); });
    }, [d.images]);
    function set(key, val) { props.onChange(i, patch(d, key, val)); }

    return el(c.PanelBody, { className: 'lo-door-card', title: d.title || ('Card ' + (i + 1)), initialOpen: false },
      el('div', { className: 'lo-door-head' },
        el('strong', null, 'Card ' + (i + 1)),
        el('span', null,
          el(c.Button, { variant: 'tertiary', size: 'small', label: 'Move up', disabled: i === 0, onClick: function () { props.onMove(i, -1); } }, '↑ Up'),
          el(c.Button, { variant: 'tertiary', size: 'small', label: 'Move down', disabled: i === props.total - 1, onClick: function () { props.onMove(i, 1); } }, '↓ Down'),
          el(c.Button, { variant: 'tertiary', size: 'small', label: 'Remove card', isDestructive: true, onClick: function () { props.onRemove(i); } }, 'Remove')
        )
      ),
      el('div', { className: 'lo-door-row' },
        el(c.TextControl, { label: 'Small heading', placeholder: '01 · Collect', value: d.kicker || '', onChange: function (v) { set('kicker', v); } }),
        el(c.TextControl, { label: 'Title', value: d.title || '', onChange: function (v) { set('title', v); } })
      ),
      el(c.TextareaControl, { label: 'Text', rows: 2, value: d.text || '', onChange: function (v) { set('text', v); } }),
      el('div', { className: 'lo-door-row' },
        el(c.TextControl, { label: 'Button text', placeholder: 'Shop now', value: d.label || '', onChange: function (v) { set('label', v); } }),
        el(c.TextControl, { label: 'Link', type: 'url', placeholder: 'https://', value: d.url || '', onChange: function (v) { set('url', v); } })
      ),
      el('div', { className: 'lo-door-photos' },
        el('span', { className: 'lo-door-label' }, 'Photos and videos (they play one after another)'),
        el('div', { className: 'lo-door-thumbs' },
          media.map(function (m, k) {
            if (!m) return el(c.Spinner, { key: k });
            if (m.mime_type && m.mime_type.indexOf('video') === 0) {
              return el('span', { key: k, className: 'lo-door-video', title: m.title && m.title.rendered ? m.title.rendered : 'Video' },
                el('span', { className: 'dashicons dashicons-video-alt3' }), el('small', null, 'Video'));
            }
            var src = m.media_details && m.media_details.sizes && m.media_details.sizes.thumbnail ? m.media_details.sizes.thumbnail.source_url : m.source_url;
            return el('img', { key: k, src: src, alt: '' });
          })
        ),
        el(MediaUploadCheck, null,
          el(MediaUpload, {
            allowedTypes: ['image', 'video'], multiple: true, value: d.images || [],
            onSelect: function (sel) { set('images', sel.map(function (m) { return m.id; })); },
            render: function (o) { return el(c.Button, { variant: 'secondary', onClick: o.open }, (d.images && d.images.length) ? 'Change photos / videos' : 'Choose photos / videos'); }
          })
        )
      ),
      el('div', { className: 'lo-door-remove' },
        el(c.Button, { variant: 'secondary', isDestructive: true, icon: 'trash', onClick: function () { props.onRemove(i); } }, 'Remove this card')
      )
    );
  }

  function Edit(props) {
    var a = props.attributes, doors = a.doors || [], trust = a.trust || [];
    var blockProps = useBlockProps({ className: 'lo-doors-editor' });
    function setA(k, v) { var o = {}; o[k] = v; props.setAttributes(o); }
    function changeDoor(i, d) { var n = doors.slice(); n[i] = d; setA('doors', n); }
    function removeDoor(i) { var n = doors.slice(); n.splice(i, 1); setA('doors', n); }
    function moveDoor(i, dir) {
      var j = i + dir; if (j < 0 || j >= doors.length) return;
      var n = doors.slice(), t = n[i]; n[i] = n[j]; n[j] = t; setA('doors', n);
    }
    function changeTrust(i, key, v) { var n = trust.slice(); n[i] = patch(n[i], key, v); setA('trust', n); }

    return el('div', blockProps,
      el('div', { className: 'lo-door-title' },
        el('span', { className: 'dashicons dashicons-columns' }),
        el('div', null, el('strong', null, 'Three doors'), el('small', null, 'Heading, expanding photo cards and trust badges. The design looks best with 3 cards.'))
      ),
      el(c.PanelBody, { className: 'lo-door-box', title: 'Heading and intro', initialOpen: true },
        el('div', { className: 'lo-door-row' },
          el(c.TextControl, { label: 'Heading', value: a.heading, onChange: function (v) { setA('heading', v); } }),
          el(c.TextControl, { label: 'Heading, italic part', value: a.headingAccent, onChange: function (v) { setA('headingAccent', v); } })
        ),
        el(c.TextControl, { label: 'Short text next to the heading', value: a.intro, onChange: function (v) { setA('intro', v); } })
      ),
      doors.map(function (d, i) {
        return el(DoorCard, { key: i, index: i, total: doors.length, door: d, onChange: changeDoor, onRemove: removeDoor, onMove: moveDoor });
      }),
      el(c.Button, { variant: 'secondary', onClick: function () { setA('doors', doors.concat([{ kicker: '', title: '', text: '', label: '', url: '', images: [] }])); } }, '+ Add card'),
      el(c.PanelBody, { className: 'lo-door-box', title: 'Trust badges', initialOpen: false },
        el('strong', null, 'Trust badges'),
        trust.map(function (t, i) {
          return el('div', { className: 'lo-door-row lo-trust-row', key: i },
            el(c.TextControl, { label: i === 0 ? 'Bold text' : null, placeholder: 'Free shipping', value: t.bold || '', onChange: function (v) { changeTrust(i, 'bold', v); } }),
            el(c.TextControl, { label: i === 0 ? 'Text after it' : null, placeholder: 'over ₹999', value: t.text || '', onChange: function (v) { changeTrust(i, 'text', v); } }),
            el(c.Button, { variant: 'tertiary', size: 'small', label: 'Remove badge', isDestructive: true, onClick: function () { var n = trust.slice(); n.splice(i, 1); setA('trust', n); } }, 'Remove')
          );
        }),
        el(c.Button, { variant: 'tertiary', onClick: function () { setA('trust', trust.concat([{ bold: '', text: '' }])); } }, '+ Add badge')
      )
    );
  }

  registerBlockType('layerone/doors', { edit: Edit, save: function () { return null; } });
})(window.wp);
