/* How it's made: editor UI. */
(function (wp) {
  var L = window.LOEd, el = L.el, c = wp.components;
  var useBlockProps = wp.blockEditor.useBlockProps;

  function Edit(props) {
    var a = props.attributes;
    function setA(k, v) { var o = {}; o[k] = v; props.setAttributes(o); }
    return el('div', useBlockProps({ style: L.BOX }),
      L.Header('list-view', "How it's made", 'Numbered steps with a photo each.'),
      el(c.PanelBody, { title: 'Heading', initialOpen: true },
        L.Row([L.Text('Small heading', a.kicker, function (v) { setA('kicker', v); }), L.Text('Heading', a.heading, function (v) { setA('heading', v); })]),
        L.Text('Heading, italic part', a.headingAccent, function (v) { setA('headingAccent', v); })
      ),
      el(L.Repeater, { items: a.steps, onChange: function (v) { setA('steps', v); }, itemLabel: 'Step', itemTitle: function (s) { return s.title; }, blank: { imageId: 0, title: '', text: '', alt: '' }, addLabel: '+ Add step',
        render: function (s, set) {
          return [
            el(L.Media, { label: 'Photo', value: s.imageId, onChange: function (v) { set('imageId', v); }, size: 72 }),
            L.Text('Title', s.title, function (v) { set('title', v); }),
            L.Text('Text', s.text, function (v) { set('text', v); }),
            L.Text('Photo description (for screen readers)', s.alt, function (v) { set('alt', v); })
          ];
        } })
    );
  }

  wp.blocks.registerBlockType('layerone/steps', { edit: Edit, save: function () { return null; } });
})(window.wp);
