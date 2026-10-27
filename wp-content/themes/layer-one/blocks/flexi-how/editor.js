/* Collectibles how it works: editor UI. */
(function (wp) {
  var L = window.LOEd, el = L.el, c = wp.components;
  var useBlockProps = wp.blockEditor.useBlockProps;

  function Edit(props) {
    var a = props.attributes;
    function setA(k, v) { var o = {}; o[k] = v; props.setAttributes(o); }
    return el('div', useBlockProps({ style: L.BOX }),
      L.Header('editor-ol', 'Collectibles: how it works', 'A short numbered row of how ordering works.'),
      el(c.PanelBody, { title: 'Steps', initialOpen: true },
        el(L.Repeater, { items: a.steps, onChange: function (v) { setA('steps', v); }, itemLabel: 'Step', itemTitle: function (s) { return s.title; }, blank: { title: '', text: '' }, addLabel: '+ Add step',
          render: function (s, set) { return [L.Text('Bold text', s.title, function (v) { set('title', v); }), L.Text('Text', s.text, function (v) { set('text', v); })]; } })
      )
    );
  }

  wp.blocks.registerBlockType('layerone/flexi-how', { edit: Edit, save: function () { return null; } });
})(window.wp);
