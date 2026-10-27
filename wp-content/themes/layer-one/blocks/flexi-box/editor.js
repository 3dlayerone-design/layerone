/* Collectibles box builder: editor UI. */
(function (wp) {
  var L = window.LOEd, el = L.el, c = wp.components;
  var useBlockProps = wp.blockEditor.useBlockProps;

  function Edit(props) {
    var a = props.attributes;
    function setA(k, v) { var o = {}; o[k] = v; props.setAttributes(o); }
    return el('div', useBlockProps({ style: L.BOX }),
      L.Header('archive', 'Collectibles: box builder', 'Box sizes, prices and the free bonus figure are set in WooCommerce > Flexi Crew. Only the texts are edited here.'),
      el(c.PanelBody, { title: 'Heading', initialOpen: true },
        L.Row([L.Text('Heading', a.title, function (v) { setA('title', v); }), L.Text('Heading, italic part', a.accent, function (v) { setA('accent', v); })]),
        L.Area('Text', a.text, function (v) { setA('text', v); })
      )
    );
  }

  wp.blocks.registerBlockType('layerone/flexi-box', { edit: Edit, save: function () { return null; } });
})(window.wp);
