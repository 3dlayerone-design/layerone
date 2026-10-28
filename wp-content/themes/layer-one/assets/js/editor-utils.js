/* Shared helpers for the Layer One block editors (plain JS, no build step). Exposes window.LOEd. */
(function (wp) {
  var el = wp.element.createElement;
  var c = wp.components;
  var useSelect = wp.data.useSelect;
  var MediaUpload = wp.blockEditor.MediaUpload;
  var MediaUploadCheck = wp.blockEditor.MediaUploadCheck;

  function patch(obj, key, val) { var o = Object.assign({}, obj); o[key] = val; return o; }

  function Text(label, value, onChange, extra) {
    return el(c.TextControl, Object.assign({ label: label, value: value || '', onChange: onChange }, extra || {}));
  }
  function Area(label, value, onChange, extra) {
    return el(c.TextareaControl, Object.assign({ label: label, value: value || '', rows: 3, onChange: onChange }, extra || {}));
  }
  /* Rich text field: bold, italic and links, saved as simple HTML. */
  function Rich(label, value, onChange, opts) {
    opts = opts || {};
    return el('div', { className: 'components-base-control' },
      el('label', { className: 'components-base-control__label', style: { display: 'block', marginBottom: 6, fontSize: 11, fontWeight: 500, textTransform: 'uppercase' } }, label),
      el(wp.blockEditor.RichText, {
        tagName: 'div', value: value || '', onChange: onChange, placeholder: opts.placeholder || '',
        allowedFormats: opts.formats || ['core/bold', 'core/italic', 'core/link'],
        style: { border: '1px solid #8c8f94', borderRadius: 2, padding: '8px 10px', background: '#fff', minHeight: opts.minHeight || 60, fontSize: 13 }
      }),
      opts.help ? el('p', { className: 'components-base-control__help', style: { margin: '4px 0 0', color: '#50575e', fontSize: 12 } }, opts.help) : null
    );
  }
  function Row(children, cols) {
    return el('div', { style: { display: 'grid', gridTemplateColumns: cols || '1fr 1fr', gap: '12px' } }, children);
  }

  /* One image (or video) from the media library, with a thumbnail. */
  function Media(props) {
    var type = props.type || 'image';
    var media = useSelect(function (select) { return props.value ? select('core').getMedia(props.value) : null; }, [props.value]);
    var thumb = '';
    if (media) {
      thumb = media.media_details && media.media_details.sizes && media.media_details.sizes.thumbnail
        ? media.media_details.sizes.thumbnail.source_url
        : (type === 'image' ? media.source_url : '');
    }
    var size = props.size || 56;
    return el('div', { style: { display: 'flex', gap: '10px', alignItems: 'center' } },
      props.value
        ? (thumb
          ? el('img', { src: thumb, alt: '', style: { width: size, height: size, objectFit: 'cover', borderRadius: 6 } })
          : el('span', { className: 'dashicons dashicons-format-video', style: { fontSize: 28, width: 28, height: 28 } }))
        : null,
      el('div', null,
        props.label ? el('div', { style: { fontSize: 11, textTransform: 'uppercase', marginBottom: 4 } }, props.label) : null,
        el(MediaUploadCheck, null,
          el(MediaUpload, {
            allowedTypes: [type], value: props.value || 0,
            onSelect: function (m) { props.onChange(m.id); },
            render: function (o) { return el(c.Button, { variant: 'secondary', size: 'small', onClick: o.open }, props.value ? 'Change' : 'Choose ' + type); }
          })
        ),
        props.value ? el(c.Button, { variant: 'link', isDestructive: true, style: { marginLeft: 8 }, onClick: function () { props.onChange(0); } }, 'Remove') : null
      )
    );
  }

  /* List of cards with move up / down / delete and an add button. */
  function Repeater(props) {
    var items = props.items || [];
    function set(i, v) { var n = items.slice(); n[i] = v; props.onChange(n); }
    function remove(i) { var n = items.slice(); n.splice(i, 1); props.onChange(n); }
    function move(i, d) {
      var j = i + d; if (j < 0 || j >= items.length) return;
      var n = items.slice(), t = n[i]; n[i] = n[j]; n[j] = t; props.onChange(n);
    }
    /* Each item is an accordion panel: closed by default, titled with props.itemTitle(item). */
    return el('div', { style: { display: 'grid', gap: 10 } },
      el('div', { style: { background: '#fff', border: '1px solid #dcdcde', borderRadius: 6, overflow: 'hidden' } },
        items.map(function (it, i) {
          var fallback = (props.itemLabel || 'Item') + ' ' + (i + 1);
          var title = (props.itemTitle && props.itemTitle(it, i)) || fallback;
          return el(c.PanelBody, { key: i, title: title, initialOpen: false },
            el('div', { style: { display: 'flex', justifyContent: 'flex-end', marginBottom: 8 } },
              el(c.Button, { variant: 'tertiary', size: 'small', label: 'Move up', disabled: i === 0, onClick: function () { move(i, -1); } }, '↑ Up'),
              el(c.Button, { variant: 'tertiary', size: 'small', label: 'Move down', disabled: i === items.length - 1, onClick: function () { move(i, 1); } }, '↓ Down'),
              el(c.Button, { variant: 'tertiary', size: 'small', label: 'Remove', isDestructive: true, onClick: function () { remove(i); } }, 'Remove')
            ),
            el('div', { style: { display: 'grid', gap: 8 } }, props.render(it, function (k, v) { set(i, patch(it, k, v)); }, i))
          );
        })
      ),
      el('div', null, el(c.Button, { variant: 'secondary', onClick: function () { props.onChange(items.concat([Object.assign({}, props.blank)])); } }, props.addLabel || '+ Add'))
    );
  }

  /* Compact one-line rows for short lists (stats, steps, badges). */
  function Rows(props) {
    var items = props.items || [];
    function set(i, v) { var n = items.slice(); n[i] = v; props.onChange(n); }
    return el('div', { style: { display: 'grid', gap: 6 } },
      items.map(function (it, i) {
        return el('div', { key: i, style: { display: 'grid', gridTemplateColumns: props.cols, gap: 8, alignItems: 'end' } },
          props.render(it, function (k, v) { set(i, patch(it, k, v)); }, i),
          el(c.Button, { variant: 'tertiary', size: 'small', label: 'Remove', isDestructive: true, onClick: function () { var n = items.slice(); n.splice(i, 1); props.onChange(n); } }, 'Remove')
        );
      }),
      el('div', null, el(c.Button, { variant: 'secondary', size: 'small', onClick: function () { props.onChange(items.concat([Object.assign({}, props.blank)])); } }, props.addLabel || '+ Add'))
    );
  }

  function Header(icon, title, note) {
    return el('div', { style: { display: 'flex', gap: 10, alignItems: 'center' } },
      el('span', { className: 'dashicons dashicons-' + icon }),
      el('div', null, el('strong', null, title), el('small', { style: { display: 'block', color: '#50575e' } }, note))
    );
  }


  /* Figures, bundles and product tags from the catalogue (loaded once per editor session). */
  var optionsPromise = null;
  function useFlexiOptions() {
    var st = wp.element.useState(null), data = st[0], setData = st[1];
    wp.element.useEffect(function () {
      if (!optionsPromise) optionsPromise = wp.apiFetch({ path: '/layerone/v1/flexi/options' }).catch(function () { return { figures: [], bundles: [], tags: [] }; });
      var live = true;
      optionsPromise.then(function (d) { if (live) setData(d); });
      return function () { live = false; };
    }, []);
    return data;
  }

  /* Pick products by name. ids = array of product ids, options = [{id, name}]. */
  function ProductPicker(props) {
    var opts = props.options || [];
    var byId = {}, byName = {};
    opts.forEach(function (o) { byId[o.id] = o.name; byName[o.name.toLowerCase()] = o.id; });
    var tokens = (props.ids || []).map(function (id) { return byId[id]; }).filter(Boolean);
    return el(c.FormTokenField, {
      label: props.label, value: tokens, suggestions: opts.map(function (o) { return o.name; }), maxSuggestions: 12,
      __experimentalExpandOnFocus: true,
      onChange: function (list) {
        var ids = [];
        list.forEach(function (t) { var name = typeof t === 'string' ? t : t.value; var id = byName[String(name).toLowerCase()]; if (id && ids.indexOf(id) < 0) ids.push(id); });
        props.onChange(ids);
      }
    });
  }


  /* "Show products with this tag" + "or pick by hand". Used by the figure and bundle sources of the Flexi blocks. */
  function TagAndProducts(props) {
    var opts = useFlexiOptions();
    var tags = opts ? opts.tags : [];
    var tagOptions = [{ label: props.tagEmptyLabel || 'Choose a tag…', value: '' }].concat(tags.map(function (t) { return { label: t.name + ' (' + t.count + ')', value: t.slug }; }));
    if (props.tag && !tags.some(function (t) { return t.slug === props.tag; })) tagOptions.push({ label: props.tag, value: props.tag });
    return el('div', { style: { display: 'grid', gap: 10 } },
      props.showTag === false ? null : el(c.SelectControl, { label: props.tagLabel || 'Show figures with this product tag', value: props.tag || '', options: tagOptions, onChange: props.onTag }),
      opts ? el(ProductPicker, { label: props.pickLabel || 'Or pick figures by hand', ids: props.ids, options: props[props.kind || 'figures'] ? props[props.kind || 'figures'] : opts[props.kind || 'figures'], onChange: props.onIds }) : el(c.Spinner)
    );
  }

  var BOX = { border: '1px solid #dcdcde', borderRadius: 8, padding: 16, background: '#f6f7f7', display: 'grid', gap: 14 };
  var CARD = { background: '#fff', border: '1px solid #dcdcde', borderRadius: 6, padding: 14, display: 'grid', gap: 10 };

  window.LOEd = { el: el, patch: patch, Text: Text, Area: Area, Rich: Rich, Row: Row, Media: Media, Repeater: Repeater, Rows: Rows, useFlexiOptions: useFlexiOptions, ProductPicker: ProductPicker, TagAndProducts: TagAndProducts, Header: Header, BOX: BOX, CARD: CARD };
})(window.wp);
