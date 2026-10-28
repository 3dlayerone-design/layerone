/* FAQs: editor UI. */
(function (wp) {
  var L = window.LOEd, el = L.el, c = wp.components;
  var useBlockProps = wp.blockEditor.useBlockProps;

  function Edit(props) {
    var a = props.attributes;
    function setA(k, v) { var o = {}; o[k] = v; props.setAttributes(o); }
    return el('div', useBlockProps({ style: L.BOX }),
      L.Header('editor-help', 'FAQs', 'Groups of questions. Visitors can search them and filter by group.'),
      el(c.PanelBody, { title: 'Page heading', initialOpen: false },
        L.Row([L.Text('Heading', a.heading, function (v) { setA('heading', v); }), L.Text('Heading, italic part', a.headingAccent, function (v) { setA('headingAccent', v); })]),
        L.Text('Text under the heading', a.intro, function (v) { setA('intro', v); })
      ),
      el(L.Repeater, { items: a.groups, onChange: function (v) { setA('groups', v); }, itemLabel: 'Group', itemTitle: function (g) { return g.title; }, blank: { title: '', items: [] }, addLabel: '+ Add group',
        render: function (g, set) {
          return [
            L.Text('Group name', g.title, function (v) { set('title', v); }),
            el('strong', null, 'Questions'),
            el(L.Rows, { items: g.items, onChange: function (v) { set('items', v); }, cols: '1fr auto', blank: { q: '', a: '' }, addLabel: '+ Add question',
              render: function (it, s) {
                return el('div', { style: { display: 'grid', gap: 6, background: '#fff', border: '1px solid #dcdcde', borderRadius: 6, padding: 10 } },
                  L.Text('Question', it.q, function (v) { s('q', v); }),
                  L.Rich('Answer', it.a, function (v) { s('a', v); }, { minHeight: 80, help: 'Select text to make it bold, italic or a link.' }));
              } })
          ];
        } }),
      el(c.PanelBody, { title: 'Help box at the bottom', initialOpen: false },
        L.Row([L.Text('Title', a.helpTitle, function (v) { setA('helpTitle', v); }), L.Text('Text', a.helpText, function (v) { setA('helpText', v); })]),
        L.Row([L.Text('Button label', a.helpLabel, function (v) { setA('helpLabel', v); }), L.Text('Button link (empty = Contact page)', a.helpUrl, function (v) { setA('helpUrl', v); })])
      )
    );
  }

  wp.blocks.registerBlockType('layerone/faq', { edit: Edit, save: function () { return null; } });
})(window.wp);
