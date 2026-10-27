/* Collectibles all figures: editor UI. */
(function (wp) {
  var L = window.LOEd, el = L.el, c = wp.components;
  var useBlockProps = wp.blockEditor.useBlockProps;

  function Edit(props) {
    var a = props.attributes;
    function setA(k, v) { var o = {}; o[k] = v; props.setAttributes(o); }
    return el('div', useBlockProps({ style: L.BOX }),
      L.Header('grid-view', 'Collectibles: all figures', 'Series filter chips and a grid of every figure product, in series order.'),
      el(c.PanelBody, { title: 'Heading', initialOpen: true },
        L.Row([L.Text('Heading', a.title, function (v) { setA('title', v); }, { help: '{figures} is filled in.' }), L.Text('Heading, italic part', a.accent, function (v) { setA('accent', v); })]),
        L.Text('Text under the heading', a.text, function (v) { setA('text', v); })
      )
    );
  }

  wp.blocks.registerBlockType('layerone/flexi-all', { edit: Edit, save: function () { return null; } });
})(window.wp);
