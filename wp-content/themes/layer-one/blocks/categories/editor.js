/* Category tiles: editor UI (plain JS, no build step). */
(function (wp) {
  var el = wp.element.createElement;
  var registerBlockType = wp.blocks.registerBlockType;
  var useBlockProps = wp.blockEditor.useBlockProps;
  var MediaUpload = wp.blockEditor.MediaUpload;
  var MediaUploadCheck = wp.blockEditor.MediaUploadCheck;
  var c = wp.components;
  var useSelect = wp.data.useSelect;

  function patch(obj, key, val) { var o = Object.assign({}, obj); o[key] = val; return o; }

  function TileCard(props) {
    var t = props.tile, i = props.index;
    var media = useSelect(function (select) { return t.imageId ? select('core').getMedia(t.imageId) : null; }, [t.imageId]);
    var thumb = media && media.media_details && media.media_details.sizes && media.media_details.sizes.thumbnail ? media.media_details.sizes.thumbnail.source_url : (media ? media.source_url : '');
    function set(k, v) { props.onChange(i, patch(t, k, v)); }

    var options = [{ label: 'Choose a category…', value: 0 }].concat((props.cats || []).map(function (cat) {
      return { label: wp.htmlEntities.decodeEntities(cat.name) + ' (' + cat.count + ')', value: cat.id };
    }));

    var pickedCat = (props.cats || []).filter(function (x) { return x.id === t.categoryId; })[0];
    var tileCat = pickedCat ? wp.htmlEntities.decodeEntities(pickedCat.name) : '';

    return el(c.PanelBody, { className: 'lo-tile-card', title: t.title || tileCat || ('Tile ' + (i + 1)), initialOpen: false },
      el('div', { className: 'lo-tile-head' },
        el('strong', null, 'Tile ' + (i + 1)),
        el('span', null,
          el(c.Button, { variant: 'tertiary', size: 'small', label: 'Move up', disabled: i === 0, onClick: function () { props.onMove(i, -1); } }, '↑ Up'),
          el(c.Button, { variant: 'tertiary', size: 'small', label: 'Move down', disabled: i === props.total - 1, onClick: function () { props.onMove(i, 1); } }, '↓ Down'),
          el(c.Button, { variant: 'tertiary', size: 'small', label: 'Remove tile', isDestructive: true, onClick: function () { props.onRemove(i); } }, 'Remove')
        )
      ),
      el('div', { className: 'lo-tile-body' },
        el('div', { className: 'lo-tile-img' },
          thumb ? el('img', { src: thumb, alt: '' }) : el('div', { className: 'lo-tile-noimg' }, 'No photo'),
          el(MediaUploadCheck, null,
            el(MediaUpload, {
              allowedTypes: ['image'], value: t.imageId || 0,
              onSelect: function (m) { set('imageId', m.id); },
              render: function (o) { return el(c.Button, { variant: 'secondary', onClick: o.open }, t.imageId ? 'Change photo' : 'Choose photo'); }
            })
          )
        ),
        el('div', { className: 'lo-tile-fields' },
          el(c.SelectControl, { label: 'Product category', value: t.categoryId || 0, options: options, onChange: function (v) { set('categoryId', parseInt(v, 10) || 0); } }),
          el(c.TextControl, { label: 'Title', help: 'Empty = category name.', value: t.title || '', onChange: function (v) { set('title', v); } }),
          el(c.TextControl, { label: 'Price text', help: 'Empty = "from" + the cheapest product in the category.', placeholder: 'from ₹449', value: t.priceText || '', onChange: function (v) { set('priceText', v); } }),
          el(c.TextControl, { label: 'Link', help: 'Empty = the category page.', type: 'url', value: t.url || '', onChange: function (v) { set('url', v); } }),
          el(c.ToggleControl, { label: 'Wide tile (spans two columns)', checked: !!t.wide, onChange: function (v) { set('wide', v); } })
        )
      ),
      el('div', { className: 'lo-tile-remove' },
        el(c.Button, { variant: 'secondary', isDestructive: true, icon: 'trash', onClick: function () { props.onRemove(i); } }, 'Remove this tile')
      )
    );
  }

  function Edit(props) {
    var a = props.attributes, tiles = a.tiles || [];
    var blockProps = useBlockProps({ className: 'lo-tiles-editor' });
    var cats = useSelect(function (select) {
      return select('core').getEntityRecords('taxonomy', 'product_cat', { per_page: 100, orderby: 'name', order: 'asc', hide_empty: false });
    }, []);
    function setA(k, v) { var o = {}; o[k] = v; props.setAttributes(o); }
    function change(i, t) { var n = tiles.slice(); n[i] = t; setA('tiles', n); }
    function remove(i) { var n = tiles.slice(); n.splice(i, 1); setA('tiles', n); }
    function move(i, d) { var j = i + d; if (j < 0 || j >= tiles.length) return; var n = tiles.slice(), x = n[i]; n[i] = n[j]; n[j] = x; setA('tiles', n); }

    return el('div', blockProps,
      el('div', { className: 'lo-tile-title' },
        el('span', { className: 'dashicons dashicons-grid-view' }),
        el('div', null, el('strong', null, 'Category tiles'), el('small', null, 'Photo tiles linking to product categories. The first tile is tall; a "wide" tile spans two columns.'))
      ),
      el(c.PanelBody, { className: 'lo-tile-box', title: 'Heading and link', initialOpen: true },
        el('div', { className: 'lo-tile-row' },
          el(c.TextControl, { label: 'Small heading', value: a.kicker, onChange: function (v) { setA('kicker', v); } }),
          el(c.TextControl, { label: 'Heading', value: a.heading, onChange: function (v) { setA('heading', v); } }),
          el(c.TextControl, { label: 'Heading, italic part', value: a.headingAccent, onChange: function (v) { setA('headingAccent', v); } })
        ),
        el('div', { className: 'lo-tile-row two' },
          el(c.TextControl, { label: 'Link text (right side)', value: a.linkLabel, onChange: function (v) { setA('linkLabel', v); } }),
          el(c.TextControl, { label: 'Link URL', type: 'url', help: 'Empty = Home & Desk category.', value: a.linkUrl, onChange: function (v) { setA('linkUrl', v); } })
        )
      ),
      cats === null ? el(c.Spinner) : null,
      tiles.map(function (t, i) {
        return el(TileCard, { key: i, index: i, total: tiles.length, tile: t, cats: cats, onChange: change, onRemove: remove, onMove: move });
      }),
      el(c.Button, { variant: 'secondary', onClick: function () { setA('tiles', tiles.concat([{ categoryId: 0, title: '', imageId: 0, priceText: '', url: '', wide: false }])); } }, '+ Add tile')
    );
  }

  registerBlockType('layerone/categories', { edit: Edit, save: function () { return null; } });
})(window.wp);
