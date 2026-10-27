/* Custom & corporate: editor UI. */
(function (wp) {
  var L = window.LOEd, el = L.el, c = wp.components;
  var useBlockProps = wp.blockEditor.useBlockProps;

  function textRows(items, onChange, label) {
    return el(L.Rows, { items: items, onChange: onChange, cols: '1fr auto', blank: { text: '' }, addLabel: '+ Add',
      render: function (t, set) { return L.Text(label, t.text, function (v) { set('text', v); }); } });
  }

  function Edit(props) {
    var a = props.attributes;
    function setA(k, v) { var o = {}; o[k] = v; props.setAttributes(o); }
    function setObj(key) { return function (k, v) { setA(key, L.patch(a[key], k, v)); }; }
    var deal = a.deal || {}, figc = a.figc || {}, forme = a.forme || {};
    var setDeal = setObj('deal'), setFigc = setObj('figc'), setForme = setObj('forme');

    return el('div', useBlockProps({ style: L.BOX }),
      L.Header('id-alt', 'Custom & corporate', 'Photo showcase, corporate card, custom figurine card and personal orders row.'),

      el(c.PanelBody, { title: 'Heading and link', initialOpen: true },
        L.Row([L.Text('Small heading', a.kicker, function (v) { setA('kicker', v); }), L.Text('Heading', a.heading, function (v) { setA('heading', v); })]),
        L.Row([L.Text('Heading, italic part', a.headingAccent, function (v) { setA('headingAccent', v); }), L.Text('Link text (right side)', a.linkLabel, function (v) { setA('linkLabel', v); })]),
        L.Text('Link URL', a.linkUrl, function (v) { setA('linkUrl', v); }, { type: 'url', help: 'Empty = Custom & Corporate category.' })
      ),

      el(c.PanelBody, { title: 'Photo showcase (' + (a.showcase || []).length + ')', initialOpen: false },
        el(L.Repeater, { items: a.showcase, onChange: function (v) { setA('showcase', v); }, itemLabel: 'Photo', itemTitle: function (s) { return s.name; }, blank: { imageId: 0, kind: 'Personal', name: '', url: '' }, addLabel: '+ Add photo',
          render: function (s, set) {
            return [
              el(L.Media, { value: s.imageId, onChange: function (v) { set('imageId', v); }, size: 72 }),
              L.Row([
                L.Text('Name', s.name, function (v) { set('name', v); }),
                el(c.SelectControl, { label: 'Type', value: s.kind || 'Personal', options: [{ label: 'Personal', value: 'Personal' }, { label: 'Corporate', value: 'Corporate' }], onChange: function (v) { set('kind', v); } })
              ]),
              L.Text('Link', s.url, function (v) { set('url', v); }, { type: 'url', help: 'Empty = Custom & Corporate category.' })
            ];
          } })
      ),

      el(c.PanelBody, { title: 'Corporate gifting card', initialOpen: false },
        L.Row([L.Text('Small heading', deal.kicker, function (v) { setDeal('kicker', v); }), el(c.TextareaControl, { label: 'Title (a new line breaks the title)', rows: 2, value: deal.title || '', onChange: function (v) { setDeal('title', v); } })]),
        L.Area('Text', deal.text, function (v) { setDeal('text', v); }),
        el('strong', null, 'Numbers'),
        el(L.Rows, { items: deal.stats, onChange: function (v) { setDeal('stats', v); }, cols: '1fr 2fr auto', blank: { value: '', label: '' }, addLabel: '+ Add number',
          render: function (s, set) { return [L.Text('Number', s.value, function (v) { set('value', v); }), L.Text('Label', s.label, function (v) { set('label', v); })]; } }),
        el('strong', null, 'Steps'),
        textRows(deal.steps, function (v) { setDeal('steps', v); }, 'Step'),
        L.Row([L.Text('WhatsApp button text', deal.ctaLabel, function (v) { setDeal('ctaLabel', v); }), L.Text('WhatsApp message', deal.waMessage, function (v) { setDeal('waMessage', v); }, { help: 'Uses the WhatsApp number from Customize > Header.' })]),
        L.Row([L.Text('Link text', deal.moreLabel, function (v) { setDeal('moreLabel', v); }), L.Text('Link URL', deal.moreUrl, function (v) { setDeal('moreUrl', v); }, { type: 'url' })]),
        el('strong', null, 'Three photos on the right (the middle one is larger)'),
        el('div', { style: { display: 'flex', gap: 16, flexWrap: 'wrap' } },
          [0, 1, 2].map(function (i) {
            return el(L.Media, { key: i, label: 'Photo ' + (i + 1), value: (deal.images || [])[i] || 0, size: 56, onChange: function (v) {
              var n = (deal.images || []).slice(); while (n.length < 3) n.push(0); n[i] = v; setDeal('images', n);
            } });
          })
        )
      ),

      el(c.PanelBody, { title: 'Custom figurine card', initialOpen: false },
        el(L.Media, { label: 'Photo', value: figc.imageId, onChange: function (v) { setFigc('imageId', v); }, size: 72 }),
        L.Row([L.Text('Badge', figc.badge, function (v) { setFigc('badge', v); }), L.Text('Small heading', figc.kicker, function (v) { setFigc('kicker', v); })]),
        L.Text('Title', figc.title, function (v) { setFigc('title', v); }),
        L.Area('Text', figc.text, function (v) { setFigc('text', v); }),
        el('strong', null, 'Tags'),
        textRows(figc.tags, function (v) { setFigc('tags', v); }, 'Tag'),
        el('strong', null, 'Steps'),
        textRows(figc.steps, function (v) { setFigc('steps', v); }, 'Step'),
        L.Row([L.Text('Button text', figc.btnLabel, function (v) { setFigc('btnLabel', v); }), L.Text('Button link', figc.btnUrl, function (v) { setFigc('btnUrl', v); }, { type: 'url' })])
      ),

      el(c.PanelBody, { title: 'Personal custom orders row', initialOpen: false },
        L.Text('Title', forme.title, function (v) { setForme('title', v); }),
        L.Text('Text', forme.text, function (v) { setForme('text', v); }),
        el(L.Rows, { items: forme.items, onChange: function (v) { setForme('items', v); }, cols: '2fr 1fr auto', blank: { label: '', price: '' }, addLabel: '+ Add item',
          render: function (s, set) { return [L.Text('Item', s.label, function (v) { set('label', v); }), L.Text('Price text', s.price, function (v) { set('price', v); })]; } }),
        L.Row([L.Text('Button text', forme.btnLabel, function (v) { setForme('btnLabel', v); }), L.Text('Button link', forme.btnUrl, function (v) { setForme('btnUrl', v); }, { type: 'url' })])
      )
    );
  }

  wp.blocks.registerBlockType('layerone/custom', { edit: Edit, save: function () { return null; } });
})(window.wp);
