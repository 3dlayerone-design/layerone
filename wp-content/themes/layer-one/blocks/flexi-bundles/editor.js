/* Collectibles bundles: editor UI. */
(function (wp) {
  var L = window.LOEd, el = L.el, c = wp.components;
  var useBlockProps = wp.blockEditor.useBlockProps;

  function Edit(props) {
    var a = props.attributes;
    function setA(k, v) { var o = {}; o[k] = v; props.setAttributes(o); }
    return el('div', useBlockProps({ style: L.BOX }),
      L.Header('screenoptions', 'Collectibles: bundles', 'Bundle cards. Banner, name, tagline, badge and price come from each bundle product.'),
      el(c.PanelBody, { title: 'Heading', initialOpen: true },
        L.Row([L.Text('Heading', a.title, function (v) { setA('title', v); }), L.Text('Heading, italic part', a.accent, function (v) { setA('accent', v); })]),
        L.Text('Text', a.text, function (v) { setA('text', v); })
      ),
      el(c.PanelBody, { title: 'Which bundles', initialOpen: false },
        el('p', { style: { color: '#50575e' } }, 'Leave empty to show every bundle product. The first one is shown large; a bundle with a badge comes first.'),
        el(L.TagAndProducts, { showTag: false, kind: 'bundles', pickLabel: 'Choose bundles', ids: a.bundleProducts, onIds: function (v) { setA('bundleProducts', v); } })
      )
    );
  }

  wp.blocks.registerBlockType('layerone/flexi-bundles', { edit: Edit, save: function () { return null; } });
})(window.wp);
