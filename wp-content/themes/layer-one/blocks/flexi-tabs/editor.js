/* Collectibles section tabs: editor UI. */
(function (wp) {
  var L = window.LOEd, el = L.el, c = wp.components;
  var useBlockProps = wp.blockEditor.useBlockProps;

  function Edit(props) {
    var a = props.attributes;
    function setA(k, v) { var o = {}; o[k] = v; props.setAttributes(o); }
    return el('div', useBlockProps({ style: L.BOX }),
      L.Header('menu', 'Collectibles: section tabs', 'A sticky bar of links. The anchor is the id of a section: loved, bundles, all or box. A small box counter appears when figures are picked.'),
      el(c.PanelBody, { title: 'Links', initialOpen: true },
        el(L.Rows, { items: a.links, onChange: function (v) { setA('links', v); }, cols: '2fr 1fr auto', blank: { label: '', anchor: '' }, addLabel: '+ Add link',
          render: function (s, set) { return [L.Text('Text', s.label, function (v) { set('label', v); }, { help: '{figures} is filled in.' }), L.Text('Section id', s.anchor, function (v) { set('anchor', v); })]; } })
      )
    );
  }

  wp.blocks.registerBlockType('layerone/flexi-tabs', { edit: Edit, save: function () { return null; } });
})(window.wp);
