/* Hero spotlight: editor UI (plain JS, no build step). Each slide picks a WooCommerce product and adds the hero text. */
(function (wp) {
  var el = wp.element.createElement;
  var registerBlockType = wp.blocks.registerBlockType;
  var useBlockProps = wp.blockEditor.useBlockProps;
  var InspectorControls = wp.blockEditor.InspectorControls;
  var c = wp.components;
  var useSelect = wp.data.useSelect;

  var EMPTY = { bgColor: '', productId: 0, kicker: '', description: '', moreLabel: '', moreUrl: '', unit: '', quote: '' };

  function SlideCard(props) {
    var s = props.slide, i = props.index, products = props.products;
    function set(key, val) { props.onChange(i, Object.assign({}, s, (function () { var o = {}; o[key] = val; return o; })())); }

    var options = [{ label: 'Choose a product…', value: 0 }].concat((products || []).map(function (p) {
      return { label: p.title && p.title.rendered ? wp.htmlEntities.decodeEntities(p.title.rendered) : '#' + p.id, value: p.id };
    }));

    var picked = (products || []).filter(function (p) { return p.id === s.productId; })[0];
    var slideTitle = picked && picked.title ? wp.htmlEntities.decodeEntities(picked.title.rendered) : 'Slide ' + (i + 1);

    return el(c.PanelBody, { className: 'lo-hero-slide', title: slideTitle, initialOpen: false },
      el('div', { className: 'lo-hero-slide-head' },
        el('strong', null, 'Slide ' + (i + 1)),
        el('span', { className: 'lo-hero-slide-tools' },
          el(c.Button, { variant: 'tertiary', size: 'small', label: 'Move up', disabled: i === 0, onClick: function () { props.onMove(i, -1); } }, '↑ Up'),
          el(c.Button, { variant: 'tertiary', size: 'small', label: 'Move down', disabled: i === props.total - 1, onClick: function () { props.onMove(i, 1); } }, '↓ Down'),
          el(c.Button, { variant: 'tertiary', size: 'small', label: 'Remove slide', isDestructive: true, onClick: function () { props.onRemove(i); } }, 'Remove')
        )
      ),
      el(c.SelectControl, { label: 'Product', value: s.productId || 0, options: options, onChange: function (v) { set('productId', parseInt(v, 10) || 0); } }),
      el(c.TextControl, { label: 'Small heading above the title', help: 'Empty = the product category.', value: s.kicker || '', onChange: function (v) { set('kicker', v); } }),
      el(c.TextareaControl, { label: 'Description', help: 'Empty = the product short description.', rows: 3, value: s.description || '', onChange: function (v) { set('description', v); } }),
      el('div', { className: 'lo-hero-row' },
        el(c.TextControl, { label: 'Second button text', placeholder: 'Shop now', value: s.moreLabel || '', onChange: function (v) { set('moreLabel', v); } }),
        el(c.TextControl, { label: 'Second button link', type: 'url', placeholder: 'Empty = product category', value: s.moreUrl || '', onChange: function (v) { set('moreUrl', v); } })
      ),
      el('div', { className: 'lo-hero-row' },
        el(c.TextControl, { label: 'Text after the price', placeholder: '/ piece', value: s.unit || '', onChange: function (v) { set('unit', v); } }),
        el(c.TextControl, { label: 'WhatsApp quote message', help: 'If filled, the Add to cart button becomes "Get a quote on WhatsApp".', value: s.quote || '', onChange: function (v) { set('quote', v); } })
      ),
      el('div', { className: 'lo-hero-color' },
        el('span', { className: 'lo-hero-color-label' }, 'Background colour of this slide'),
        el('div', { className: 'lo-hero-color-row' },
          el('input', { type: 'color', value: s.bgColor || '#ffffff', 'aria-label': 'Background colour', onChange: function (e) { set('bgColor', e.target.value); } }),
          s.bgColor
            ? el(c.Button, { variant: 'link', onClick: function () { set('bgColor', ''); } }, 'Use the theme default')
            : el('span', { className: 'lo-hero-color-note' }, 'Not set: the theme default is used')
        )
      ),
      el('div', { className: 'lo-hero-remove' },
        el(c.Button, { variant: 'secondary', isDestructive: true, icon: 'trash', onClick: function () { props.onRemove(i); } }, 'Remove this slide')
      )
    );
  }

  function Edit(props) {
    var attrs = props.attributes, slides = attrs.slides || [];
    var products = useSelect(function (select) {
      return select('core').getEntityRecords('postType', 'product', { per_page: 100, status: 'publish', orderby: 'title', order: 'asc' });
    }, []);
    var blockProps = useBlockProps({ className: 'lo-hero-editor' });

    function update(next) { props.setAttributes({ slides: next }); }
    function change(i, slide) { var n = slides.slice(); n[i] = slide; update(n); }
    function remove(i) { var n = slides.slice(); n.splice(i, 1); update(n); }
    function move(i, d) {
      var j = i + d; if (j < 0 || j >= slides.length) return;
      var n = slides.slice(), t = n[i]; n[i] = n[j]; n[j] = t; update(n);
    }
    function add() { update(slides.concat([Object.assign({}, EMPTY)])); }

    return el('div', blockProps,
      el(InspectorControls, null,
        el(c.PanelBody, { title: 'Slideshow', initialOpen: true },
          el(c.ToggleControl, { label: 'Change slides automatically', checked: !!attrs.autoplay, onChange: function (v) { props.setAttributes({ autoplay: v }); } }),
          el(c.RangeControl, { label: 'Seconds per slide', min: 2, max: 15, value: attrs.duration, onChange: function (v) { props.setAttributes({ duration: v || 6 }); } })
        )
      ),
      el('div', { className: 'lo-hero-title' },
        el('span', { className: 'dashicons dashicons-star-filled' }),
        el('div', null, el('strong', null, 'Hero spotlight'), el('small', null, 'The top section of the home page. Price, image and Add to cart come from the WooCommerce product.'))
      ),
      products === null ? el(c.Spinner) : null,
      slides.map(function (s, i) {
        return el(SlideCard, { key: i, index: i, total: slides.length, slide: s, products: products, onChange: change, onRemove: remove, onMove: move });
      }),
      el(c.Button, { variant: 'secondary', onClick: add }, '+ Add slide')
    );
  }

  registerBlockType('layerone/hero', {
    edit: Edit,
    save: function () { return null; }
  });
})(window.wp);
