/* Collectibles most loved: editor UI. */
(function (wp) {
  var L = window.LOEd, el = L.el, c = wp.components;
  var useBlockProps = wp.blockEditor.useBlockProps;

  function Edit(props) {
    var a = props.attributes;
    function setA(k, v) { var o = {}; o[k] = v; props.setAttributes(o); }
    return el('div', useBlockProps({ style: L.BOX }),
      L.Header('heart', 'Collectibles: most loved', 'A sliding shelf of favourite figures.'),
      el(c.PanelBody, { title: 'Heading', initialOpen: true },
        L.Row([L.Text('Heading', a.title, function (v) { setA('title', v); }), L.Text('Heading, italic part', a.accent, function (v) { setA('accent', v); })]),
        L.Text('Text', a.text, function (v) { setA('text', v); })
      ),
      el(c.PanelBody, { title: 'Figures', initialOpen: false },
        el('p', { style: { color: '#50575e' } }, 'Choose a product tag, or pick figures by hand. Picked figures win. With neither, figures that have a video are shown.'),
        el(L.TagAndProducts, { tag: a.tag, ids: a.products, onTag: function (v) { setA('tag', v); }, onIds: function (v) { setA('products', v); } })
      )
    );
  }

  wp.blocks.registerBlockType('layerone/flexi-loved', { edit: Edit, save: function () { return null; } });
})(window.wp);
