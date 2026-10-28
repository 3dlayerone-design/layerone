/* Policy page: editor UI. The text is written with the normal block editor (headings, paragraphs, lists, tables). */
(function (wp) {
  var L = window.LOEd, el = L.el, c = wp.components;
  var be = wp.blockEditor;

  function Edit(props) {
    var a = props.attributes;
    function setA(k, v) { var o = {}; o[k] = v; props.setAttributes(o); }
    var inner = be.useInnerBlocksProps(
      { style: { background: '#fff', border: '1px solid #dcdcde', borderRadius: 6, padding: '8px 18px', minHeight: 120 } },
      {
        template: [['core/heading', { level: 2, placeholder: 'Section title' }], ['core/paragraph', { placeholder: 'Write the text of this section…' }]],
        allowedBlocks: ['core/heading', 'core/paragraph', 'core/list', 'core/table', 'core/quote', 'core/separator', 'core/image']
      }
    );
    return el('div', be.useBlockProps({ style: L.BOX }),
      L.Header('media-text', 'Policy page', 'Every Heading 2 below becomes a link in the “On this page” list.'),
      el(c.PanelBody, { title: 'Heading', initialOpen: false },
        L.Text('Heading (empty = page title)', a.heading, function (v) { setA('heading', v); }),
        L.Text('Text under the heading', a.intro, function (v) { setA('intro', v); }),
        L.Text('“Last updated” date (empty = date this page was saved)', a.updated, function (v) { setA('updated', v); })
      ),
      el('div', inner)
    );
  }

  wp.blocks.registerBlockType('layerone/legal', {
    edit: Edit,
    save: function () { return el(wp.blockEditor.InnerBlocks.Content); }
  });
})(window.wp);
